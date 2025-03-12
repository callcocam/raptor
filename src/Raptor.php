<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor;

class Raptor
{

    protected $path = 'admin';

    public function __construct()
    {
        $this->path = config('raptor.path', $this->path);
    }

    public static function make()
    {
        return new static();
    }

    public function path($path = null)
    {

        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getNamespace($namespace)
    {
        return sprintf("Callcocam\Raptor\%s", $namespace);
    }


    public function generate($namespace, $name, $type = 'controller')
    {
        $namespace = $this->getNamespace($namespace);
        $class = sprintf("%s\%s", $namespace, $name);
        if (class_exists($class))
            return new $class();
        return new $namespace();
    }
}
