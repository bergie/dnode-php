<?php
namespace DNode;

class Transformer
{
    public function transform($input, $callback)
    {
        $callback(strtoupper(preg_replace('/[aeiou]{2,}/', 'oo', $input)));
    }
}
