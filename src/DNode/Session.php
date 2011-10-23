<?php
namespace DNode;
use Evenement\EventEmitter;

class Session extends EventEmitter
{
    public $id = '';
    private $scrubber;
    private $wrapped = array();
    public $remote = array();
    public $requests = array();
    private $callbacks = array();
    private $cbId = 0;
    public $ready = false;

    public function __construct($id, $wrapper)
    {
        $this->id = $id;
    }

    public function start()
    {
        $this->request('methods', array($this));
    }

    public function request($method, $args)
    {
        $scrub = $this->scrub($args);
        $this->requests[] = array(
            'method' => $method,
            'arguments' => $scrub['arguments'],
            'callbacks' => $scrub['callbacks'],
            'links' => $scrub['links']
        );
    }

    public function parse($line) {
        var_dump($line);
        // TODO: Error handling for JSON parsing
        $msg = json_decode($line);
        // TODO: Try/catch handle
        $this->handle($msg);
    }

    public function handle($req) {
        $session = $this;
        $args = $this->unscrub($req);

        if ($req->method == 'methods') {
            return $this->handleMethods($args[0]);
        }
        if ($req->method == 'error') {
            return $this->emit('remoteError', array($args[0]));
        }
        if (is_string($req->method)) {
            if (is_callable(array($this, $req->method))) {
                return call_user_func_array(array($this, $req->method), $args);
            }
            return $this->emit('error', array("Request for non-enumerable method: {$req->method}"));
        }
        if (is_numeric($req->method)) {
            call_user_func_array(array($this, $this->scrubber->callbacks[$req->method]), $args);
        }
    }

    private function handleMethods($methods)
    {
        if (!is_array($methods)) {
            $methods = array();
        }

        $this->remote = array();
        foreach ($methods as $key => $value) {
            $this->remote[$key] = $value;
        }

        $this->emit('remote', array($this->remote));
        $this->ready = true;
        $this->emit('ready');
    }

    private function scrub($obj)
    {
        $paths = array();
        $links = array();

        // TODO: Deep traversal
        foreach ($obj as $id => $node) {
            if (is_object($node) && $node instanceof \Closure) {
                $this->callbacks[$this->cbId] = $node;
                $this->wrapped[] = $node;
                $paths[$id] = $this->cbId;
                $this->cbId++;
                $obj[$id] = '[Function]';
            }
        }

        return array(
            'arguments' => $obj,
            'callbacks' => $paths,
            'links' => $links
        );
    }

    /**
     * Replace callbacks. The supplied function should take a callback 
     * id and return a callback of its own. 
     */
    private function unscrub($msg) {
        $args = array();
        $session = $this;
        foreach ($msg->callbacks as $id => $value) {
            $method = $value[1];
            if (!isset($this->wrapped[$id])) {
                $this->wrapped[$id] = function() use ($session, $method) {
                    $session->request($method, func_get_args());
                };
            }
            $args[$id] = array($method => $this->wrapped[$id]);
        }
        return $args;
    }
}
