<?php
namespace DNode;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use React\Socket\Connection;
use React\Socket\ConnectionInterface;

class DNode extends EventEmitter
{
    private $loop;
    private $protocol;
    private $stack = array();

    public function __construct(LoopInterface $loop, $wrapper = null)
    {
        $this->loop = $loop;

        $wrapper = $wrapper ?: new \StdClass();
        $this->protocol = new Protocol($wrapper);
    }

    public function using($middleware)
    {
        $this->stack[] = $middleware;
        return $this;
    }

    public function connect()
    {
        $params = $this->protocol->parseArgs(func_get_args());
        if (!isset($params['host'])) {
            $params['host'] = '127.0.0.1';
        }

        if (!isset($params['port'])) {
            throw new \Exception("For now we only support TCP connections to a defined port");
        }

        $stream = @stream_socket_client("tcp://{$params['host']}:{$params['port']}");
        if (!$stream) {
            throw new \RuntimeException("No connection to DNode server in tcp://{$params['host']}:{$params['port']}");
        }

        $conn = new Connection($stream, $this->loop);
        $this->loop->addReadStream($stream, array($conn, 'handleData'));
        $this->handleConnection($conn, $params);
    }

    public function listen()
    {
        $params = $this->protocol->parseArgs(func_get_args());
        if (!isset($params['host'])) {
            $params['host'] = '127.0.0.1';
        }

        if (!isset($params['port'])) {
            throw new \Exception("For now we only support TCP connections to a defined port");
        }

        $that = $this;

        $server = new Server($this->loop);
        $server->on('connect', function ($conn) use ($that, $params) {
            $that->handleConnection($conn, $params);
        });
        $server->listen($params['port'], $params['host']);
    }

    public function handleConnection(ConnectionInterface $conn, $params)
    {
        $client = $this->protocol->create();
        foreach ($this->stack as $middleware) {
            call_user_func($middleware, array($client->instance, $client->remote, $client));
        }

        $client->on('request', function (array $request) use ($conn) {
            $conn->write(json_encode($request)."\n");
        });

        if (isset($params['block'])) {
            $client->on('ready', function () use ($client, $params) {
                call_user_func($params['block'], $client->remote, $client);
            });
        }

        $client->start();

        $conn->on('data', function ($data, $conn) use ($client) {
            if (!isset($conn->dNodeBuffer)) {
                $conn->dNodeBuffer = '';
            }

            $conn->dNodeBuffer .= $data;
            if (false !== strpos($conn->dNodeBuffer, "\n")) {
                // We got a full command, run it
                $commands = explode("\n", $conn->dNodeBuffer);
                foreach ($commands as $command) {
                    if (empty($command)) {
                        continue;
                    }
                    $client->parse($command);
                }
                $conn->dNodeBuffer = '';
            }
        });

        $ended = false;
        $conn->on('end', function () use ($client, &$ended) {
            if (!$ended) {
                $ended = true;
                $client->end();
            }
        });
        $client->on('end', function () use ($conn, &$ended) {
            if (!$ended) {
                $ended = true;
                $conn->end();
            }
        });
    }

    public function end()
    {
        $this->protocol->end();
        $this->emit('end');
    }

    public function close()
    {
        // FIXME: $this->server does not exist
        $this->server->close();
    }
}
