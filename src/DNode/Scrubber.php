<?php
namespace DNode;

class Scrubber
{
    private $callbacks = array();
    private $wrapped = array();
    private $cbId = 0;

    /**
     * Take the functions out and note them for future use
     */ 
    public function scrub($obj)
    {
        $paths = array();
        $links = array();

        // TODO: Deep traversal
        foreach ($obj as $node) {
            if (is_object($node) && $node instanceof \Closure) {
                $this->callbacks[$cbId] = $node;
                $this->wrapped[] = $node;
                $paths[$cbId] = $node;
                $cbId++;
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
    public function unscrub($msg, $f) {
        foreach ($msg->callbacks as $value) {
            $this->callbacks[$value[0]] = call_user_func($f, $value[0]);
        }
        var_dump($this->callbacks);
        return array();
    }
}
