<?php
namespace DNode;

class Protocol
{
    private $wrapper;
    private $sessions = array();

    public function construct($wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function create()
    {
        // FIXME: Random ID generation, should be unique
        $id = time();
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
            $this->session->end();
        }
    }
}
