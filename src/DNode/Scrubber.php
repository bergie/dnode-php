<?php
namespace DNode;

class Scrubber
{
    private $callbacks = array();
    private $wrapped = array();

    /**
     * Take the functions out and note them for future use
     */ 
    public function scrub($obj)
    {
        return $obj;
    }

    /**
     * Replace callbacks. The supplied function should take a callback 
     * id and return a callback of its own. 
     */
    public function unscrub($msg, $f) {
        return $msg;
    }
}
