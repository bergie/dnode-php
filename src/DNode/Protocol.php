<?php
namespace DNode;

class Protocol
{
    private $wrapper;
    private $sessions = array();

    public function __construct($wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function create()
    {
        // FIXME: Random ID generation, should be unique
        $id = microtime();
        $this->sessions[$id] = new Session($id, $this->wrapper);
        return $this->sessions[$id];
    }

    public function destroy($id)
    {
        unset($this->sessions[$id]);
    }

    public function end()
    {
        foreach ($this->sessions as $id => $session) {
            $this->sessions[$id]->end();
        }
    }

    public function parseArgs($args) {
        $params = array();

        foreach ($args as $arg) {
            if (is_string($arg)) {
                if (preg_match('/^\d+$/', $arg)) {
                    $params['port'] = $arg;
                    continue;
                }
                if (preg_match('/^\\//', $arg)) {
                    $params['path'] = $arg;
                    continue;
                }
                $params['host'] = $arg;
                continue;
            }

            if (is_numeric($arg)) {
                $params['port'] = $arg;
                continue;
            }

            if (is_object($arg)) {
                if ($arg instanceof \Closure) {
                    $params['block'] = $arg;
                    continue;
                }

                if ($arg instanceof ServerInterface) {
                    $params['server'] = $arg;
                    continue;
                }

                foreach ($arg as $key => $value) {
                    $params[$key] = $value;
                }
                continue;
            }

            throw new \Exception("Not sure what to do about " . gettype($arg) . " arguments");
        }

        return $params;
    }
}
