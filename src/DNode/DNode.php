<?php
namespace DNode;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

class DNode extends EventEmitter
{
    public $stack = array();

    private $loop;
    private $protocol;

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

        if (!isset($params['scheme'])) {
            $params['scheme'] = 'tcp:';
        }

        if (!isset($params['host'])) {
            $params['host'] = '127.0.0.1';
        }

        if (!isset($params['port'])) {
            throw new \Exception("For now we only support connections to a defined port");
        }

        $url = "{$params['scheme']}//{$params['host']}:{$params['port']}";
        $client = @stream_socket_client($url);

        if (!$client) {
            $e = new \RuntimeException("No connection to DNode server in $url");

            $this->emit('error', array($e));

            if (!count($this->listeners('error'))) {
                trigger_error((string) $e, E_USER_ERROR);
            }

            return;
        }

        $conn = new Connection($client, $this->loop, $params['scheme']);
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
        $server->on('connection', function ($conn) use ($that, $params) {
            $that->handleConnection($conn, $params);
        });
        $server->listen($params['port'], $params['host']);

        return $server;
    }

    public function handleConnection(ConnectionInterface $conn, $params)
    {
        $client = $this->protocol->create();

        $onReady = isset($params['block']) ? $params['block'] : null;
        $stream = new Stream($this, $client, $onReady);

        $conn->pipe($stream)->pipe($conn);

        $client->start();
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
