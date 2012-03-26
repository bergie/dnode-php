<?php
namespace DNode;
use Evenement\EventEmitter;

class DNode extends EventEmitter
{
    private $protocol;
    private $stack = array();

    public function __construct($wrapper = null)
    {
        if (is_null($wrapper)) {
            $wrapper = new \StdClass();
        }
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
        $this->handleConnection($stream, $params);
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

        $server = stream_socket_server("tcp://{$params['host']}:{$params['port']}");

        while ($stream = stream_socket_accept($server)) {
            $this->handleConnection($stream, $params);
        }
    }

    protected function handleConnection($stream, $params)
    {
        $client = $this->protocol->create();
        foreach ($this->stack as $middleware) {
            call_user_func($middleware, array($client->instance, $client->remote, $client));
        }

        $buffer = '';
        $started = false;
        $readied = false;
        $connected = true;

        $client->on('end', function() use (&$connected, $stream) {
            $connected = false;
            fclose($stream);
        });

        while ($connected) {
            $readables = array($stream);
            $priority = null;

            if (sizeof($client->requests) > 0) {
                $writables = array($stream);
            } else {
                $writables = null;
            }

            if (0 < stream_select($readables, $writables, $priority, null)) {
                if ($writables) {
                    foreach ($writables as $writable) {
                        if (!count($client->requests)) {
                            continue;
                        }
                        fwrite($writable, json_encode(array_shift($client->requests)) . "\n");
                    }
                }

                foreach ($readables as $readable) {
                    $buffer .= fread($readable, 2046);
                    if (preg_match('/\n/', $buffer)) {
                        // We got a full command, run it
                        $commands = explode("\n", $buffer);
                        foreach ($commands as $command) {
                            if (empty($command)) {
                                continue;
                            }
                            $client->parse($command);
                        }
                        $buffer = '';
                    }
                }
            }

            if (!$started) {
                $client->start();
                $started = true;
            }

            if ($client->ready && !$readied) {
                if (isset($params['block'])) {
                    call_user_func($params['block'], $client->remote, $client);
                }
                $readied = true;
            }

            if ($connected && feof($stream)) {
                $client->end();
            }
        }
    }

    public function end()
    {
        $this->protocol->end();
        $this->emit('end');
    }

    public function close()
    {
        $this->server->close();
    }
}
