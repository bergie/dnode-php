<?php
namespace DNode;
use Evenement\EventEmitter;

class Session extends EventEmitter
{
    // Session ID
    public $id = '';

    // Wrapped local callbacks, by callback ID
    private $callbacks = array();

    // Latest callback ID used
    private $cbId = 0;

    // Remote methods that were wrapped, by callback ID
    private $wrapped = array();

    // Wrapped object
    private $wrapper;

    // Remote methods
    public $remote;

    // Whether the session is ready for operation
    public $ready = false;

    public function __construct($id, $wrapper)
    {
        $this->id = $id;
        $this->wrapper = $wrapper;
        $this->remote = new RemoteProxy();
        $this->wrapper->remote =& $this->remote;
    }

    public function start()
    {
        // Send our methods to the other party
        $this->request('methods', array($this->wrapper));
    }

    public function end()
    {
        $this->emit('end');
        $this->removeAllListeners();

        $this->callbacks = array();
        $this->wrapped = array();
        $this->remote = null;
        $this->wrapper = null;
    }

    public function request($method, $args)
    {
        // Wrap callbacks in arguments
        $scrub = $this->scrub($args);

        $request = array(
            'method' => $method,
            'arguments' => $scrub['arguments'],
            'callbacks' => $scrub['callbacks'],
            'links' => $scrub['links']
        );

        $this->emit('request', array($request));
    }

    public function parse($line)
    {
        // TODO: Error handling for JSON parsing
        $msg = json_decode($line);
        // TODO: Try/catch handle
        $this->handle($msg);
    }

    public function handle($req)
    {
        $session = $this;

        // Register callbacks from request
        $args = $this->unscrub($req);

        if ($req->method === 'methods') {
            // Got a methods list from the remote
            return $this->handleMethods($args[0]);
        }
        if ($req->method === 'error') {
            // Got an error from the remote
            return $this->emit('remoteError', array($args[0]));
        }
        if (is_string($req->method)) {
            if (is_callable(array($this, $req->method))) {
                return call_user_func_array(array($this, $req->method), $args);
            }
            return $this->emit('error', array("Request for non-enumerable method: {$req->method}"));
        }
        if (is_numeric($req->method)) {
            call_user_func_array($this->callbacks[$req->method], $args);
        }
    }

    private function handleMethods($methods)
    {
        if (!is_object($methods)) {
            $methods = new \StdClass();
        }

        foreach ($methods as $key => $value) {
            $this->remote->setMethod($key, $value);
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
            if (is_object($node)) {
                if ($node instanceof \Closure) {
                    $this->callbacks[$this->cbId] = $node;
                    $paths[$this->cbId] = array($id);
                    $this->cbId++;
                    $obj[$id] = '[Function]';
                    continue;
                }

                $reflector = new \ReflectionClass($node);
                $methods = $reflector->getMethods();
                foreach ($methods as $method) {
                    if (!$method->isPublic()) {
                        continue;
                    }

                    $methodName = $method->getName();

                    $this->callbacks[$this->cbId] = function() use ($methodName, $node) {
                        call_user_func_array(array($node, $methodName), func_get_args());
                    };
                    $paths[$this->cbId] = array($id, $methodName);
                    $this->cbId++;
                    $node->$methodName = '[Function]';
                }
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
        $args = $msg->arguments;
        $session = $this;
        foreach ($msg->callbacks as $id => $path) {
            if (!isset($this->wrapped[$id])) {
                $this->wrapped[$id] = function() use ($session, $id) {
                    $session->request((int) $id, func_get_args());
                };
            }
            $location =& $args;
            foreach ($path as $part) {
                if (is_array($location)) {
                    $location =& $location[$part];
                    continue;
                }
                $location =& $location->$part;
            }
            $location = $this->wrapped[$id];
        }
        return $args;
    }
}
