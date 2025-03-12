<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Middleware;

use Callcocam\Raptor\Services\RaptorNavigationGenerator;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Middleware para compartilhar a navegação com o Inertia
 */
class ShareNavigationWithInertia
{
    /**
     * Gerador de navegação
     *
     * @var RaptorNavigationGenerator
     */
    protected RaptorNavigationGenerator $navigationGenerator;

    /**
     * Construtor
     *
     * @param RaptorNavigationGenerator $navigationGenerator
     */
    public function __construct(RaptorNavigationGenerator $navigationGenerator)
    {
        $this->navigationGenerator = $navigationGenerator;
    }

    /**
     * Manipula a requisição
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  bool  $useCache
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $useCache = false)
    {
        // Converte o parâmetro de string para booleano
        $useCache = filter_var($useCache, FILTER_VALIDATE_BOOLEAN);
 
        // Gera a navegação
        $navigation = $this->navigationGenerator->generate($useCache);

        // Compartilha a navegação com o Inertia
        Inertia::share('navigation', $navigation);

        return $next($request);
    }
}
