<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Raptor;

class Raptor {

    protected $path = 'admin';

    public function __construct()
    {
        $this->path = config('raptor.path', $this->path);
    }

    public function path($path = null)
    {
        if($path)
            return $this->path = $path;
        return $this->path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getNamespace($namespace)
    {
        return sprintf("Callcocam\Raptor\%s", $namespace);
    }


}
