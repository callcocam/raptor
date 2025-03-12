<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Raptor\Services\RaptorNavigationGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador para operações relacionadas à navegação no Raptor
 */
class NavigationController extends Controller
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
     * Retorna a estrutura de navegação como JSON
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $useCache = $request->boolean('cache', true);
        $navigation = $this->navigationGenerator->generate($useCache);

        return response()->json([
            'navigation' => $navigation,
        ]);
    }

    /**
     * Limpa o cache de navegação
     *
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        $success = $this->navigationGenerator->clearCache();

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Cache de navegação limpo com sucesso!'
                : 'Não foi possível limpar o cache de navegação.',
        ]);
    }

    /**
     * Retorna os itens de um grupo específico
     *
     * @param Request $request
     * @param string $group
     * @return JsonResponse
     */
    public function group(Request $request, string $group): JsonResponse
    {
        $useCache = $request->boolean('cache', true);
        $navigation = $this->navigationGenerator->generate($useCache);

        $groupItems = collect();

        foreach ($navigation as $item) {
            if (isset($item['isGroup']) && $item['isGroup'] && $item['label'] === $group) {
                $groupItems = $item['items'];
                break;
            }
        }

        return response()->json([
            'group' => $group,
            'items' => $groupItems,
        ]);
    }
}
