<?php
namespace DNode;
use Evenement\EventEmitter;

class Session extends EventEmitter
{
    public $id = '';
    private $scrubber;
    private $wrapped = array();
    private $remote = array();

    public function __construct($id, $wrapper)
    {
        $this->id = $id;
        $this->scrubber = new Scrubber();
    }

    public function start()
    {
        $this->request('methods', array($this));
    }

    public function request($method, $args)
    {
        $scrub = $this->scrubber->scrub($args);
        $this->emit(array(
            'request',
            array(
                'method' => $method,
                'argments' => $scrub['arguments'],
                'callbacks' => $scrub['callbacks'],
                'links' => $scrub['links']
            )
        ));
    }

    public function parse($line) {
        // TODO: Error handling for JSON parsing
        $msg = json_decode($line);
        // TODO: Try/catch handle
        $this->handle($msg);
    }

    public function handle($req) {
        $session = $this;
        $wrapped =& $this->wrapped;
        $args = $this->scrubber->unscrub($req, function($id) use ($session, $wrapped) {
            if (!$wrapped[$id]) {
                $wrapped[$id] = function() use ($session) {
                    $session->request($id, func_get_args());
                };
            }
            return $wrapped[$id];
        });

        if ($req['method'] == 'methods') {
            return $this->handleMethods($args[0]);
        }
        if ($req['method'] == 'error') {
            return $this->emit('remoteError', array($args[0]));
        }
        if (is_string($req['method'])) {
            if (is_callable(array($this, $req['method']))) {
                return call_user_func_array(array($this, $req['method']), $args);
            }
            return $this->emit('error', array("Request for non-enumerable method: {$req['method']}"));
        }
        if (is_numeric($req['method'])) {
            call_user_func_array(array($this, $this->scrubber->callbacks[$req['method']]), $args);
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
        $this->emit('ready');
    }
}
