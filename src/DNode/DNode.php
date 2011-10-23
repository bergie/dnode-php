<?php
namespace DNode;
use Evenement\EventEmitter;

class DNode extends EventEmitter
{
    private $protocol;
    private $stack = array();

    public function __construct($wrapper = null)
    {
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

        $stream = stream_socket_client("tcp://{$params['host']}:{$params['port']}");
        $client = $this->protocol->create();
        foreach ($this->stack as $middleware) {
            call_user_func($middleware, array($client->instance, $client->remote, $client));
        }

        $buffer = '';
        $started = false;
        $readied = false;
        while (true) {
            $readables = array($stream);
            $writables = array($stream);
            $priority = null;
            if (0 < stream_select($readables, $writables, $priority, null)) {
                foreach ($readables as $readable) {
                    $buffer .= fread($readable, 2046); 
                    if (preg_match('/\n/', $buffer)) {
                        // We got a full command, run it
                        $client->parse($buffer);
                        $buffer = '';
                    }
                }

                foreach ($writables as $writable) {
                    if (!count($client->requests)) {
                        continue;
                    }
                    fwrite($writable, json_encode(array_pop($client->requests)) . "\n");
                }
            }

            if (!$started) {
                $client->start();
                $started = true;
            }

            if ($client->ready && !$readied) {
                if (isset($params['block'])) {
                    call_user_func($params['block'], $client->remote, $stream);
                } 
                $readied = true;
            }

            if (feof($stream)) {
                break;
            }
        }
        $client->emit('end');
    }

    public function listen()
    {
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
