<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

return [
    'navigation' => [
        /*
        |--------------------------------------------------------------------------
        | Mapeamento de Namespaces para Diretórios de Controladores
        |--------------------------------------------------------------------------
        |
        | Este mapeamento associa namespaces a diretórios onde o gerador deve
        | procurar por controladores Raptor. A chave é o namespace base e o valor
        | é o caminho para o diretório correspondente.
        |
        */
        'controller_directories' => [
            // Controladores do pacote Raptor
            // 'Callcocam\\Raptor\\Http\\Controllers' => __DIR__.'/../vendor/callcocam/raptor/src/Http/Controllers',
            
            // Controladores da aplicação
            'App\\Http\\Controllers\\Raptor' => app_path('Http/Controllers/Raptor'), 
            
            // Adicione outros mapeamentos namespace => diretório conforme necessário
        ],
    
        /*
        |--------------------------------------------------------------------------
        | Configurações de Cache
        |--------------------------------------------------------------------------
        |
        | Configurações relacionadas ao cache da navegação.
        |
        */
        'cache' => [
            // Tempo de duração do cache em segundos (1 hora por padrão)
            'ttl' => 3600,
            
            // Chave utilizada para armazenar a navegação no cache
            'key' => 'raptor_navigation',
            
            // Determina se o cache deve ser usado por padrão
            'enabled' => true,
        ],
    
        /*
        |--------------------------------------------------------------------------
        | Configurações de Renderização
        |--------------------------------------------------------------------------
        |
        | Configurações relacionadas à renderização da navegação.
        |
        */
        'rendering' => [
            // Classes CSS para elementos de navegação
            'css_classes' => [
                'container' => 'raptor-navigation',
                'group' => 'raptor-navigation-group',
                'group_header' => 'raptor-navigation-group-header',
                'group_items' => 'raptor-navigation-group-items',
                'item' => 'raptor-navigation-item',
                'link' => 'raptor-navigation-link',
                'icon' => 'raptor-navigation-icon',
                'label' => 'raptor-navigation-label',
                'active' => 'active',
            ],
            
            // Prefixo para ícones (ex: 'heroicon-' para Heroicons)
            'icon_prefix' => '',
            
            // Sufixo para ícones (ex: '-icon')
            'icon_suffix' => '',
        ],
    ]
];
