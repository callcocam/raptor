# Documentação do Sistema de Navegação do Raptor

## Introdução

O sistema de navegação do Raptor é uma solução completa para gerar automaticamente menus e submenus a partir dos seus controladores. Ele analisa todos os controladores que estendem `RaptorController` e extrai informações de navegação para construir uma estrutura hierárquica de navegação.

## Recursos Principais

- Geração automática de menus a partir dos controladores
- Agrupamento de itens em categorias
- Ícones para itens e grupos de navegação
- Sistema de ordenação flexível
- Cache para otimização de desempenho
- Integração com Inertia.js
- Comandos Artisan para facilitar o gerenciamento
- Middleware para compartilhar navegação com o frontend

## Instalação

1. Registre o Service Provider no arquivo `config/app.php`:

```php
'providers' => [
    // ...
    Callcocam\Raptor\Providers\RaptorNavigationServiceProvider::class,
],
```

2. Publique o arquivo de configuração:

```bash
php artisan vendor:publish --tag=raptor-navigation-config
```

3. Registre o middleware no grupo web em `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Callcocam\Raptor\Http\Middleware\ShareNavigationWithInertia::class,
    ],
];
```

## Configuração dos Controladores

Para que um controlador seja incluído na navegação, ele deve:

1. Estender `RaptorController`
2. Implementar `NavigationGroupInterface`
3. Definir as propriedades de navegação

Exemplo de um controlador com configurações de navegação:

```php
class UserController extends RaptorController
{
    protected ?string $model = User::class;
    protected ?string $resource = 'users';
    
    // Configurações de navegação
    protected string $modelLabel = 'Usuário';
    protected string $modelLabelPlural = 'Usuários';
    protected string $modelDescription = 'Gerenciamento de usuários do sistema';
    protected int $navigationSort = 10;
    protected string $navigationGroup = 'Administração';
    protected int $navigationGroupSort = 1;
    protected string $navigationIcon = 'UserIcon';
    protected string $navigationGroupIcon = 'ShieldIcon';
    
    // Resto do controlador...
}
```

## Uso Básico

### No Backend (PHP)

Para gerar a navegação manualmente:

```php
$navigationGenerator = app(RaptorNavigationGenerator::class);
$navigation = $navigationGenerator->generate();
```

### No Blade

```blade
@raptorNavigation

{{-- Ou para um grupo específico --}}
@raptorNavigationGroup('Administração')
```

### No Frontend (Inertia.js/Vue)

O middleware compartilha automaticamente a navegação com o Inertia. Você pode acessá-la em seus componentes Vue:

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const navigation = usePage().props.navigation;
</script>

<template>
  <div>
    <div v-for="item in navigation" :key="item.slug">
      <!-- Item simples -->
      <router-link v-if="!item.isGroup" :to="item.url">
        <icon :name="item.icon" />
        {{ item.label }}
      </router-link>
      
      <!-- Grupo com subitens -->
      <div v-else>
        <div class="group-header">
          <icon :name="item.icon" />
          {{ item.label }}
        </div>
        
        <div class="group-items">
          <router-link 
            v-for="subitem in item.items" 
            :key="subitem.slug" 
            :to="subitem.url"
          >
            <icon :name="subitem.icon" />
            {{ subitem.label }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
```

## Comandos Artisan

### Gerar Navegação

```bash
# Gerar navegação usando cache (se disponível)
php artisan raptor:navigation

# Gerar navegação sem usar cache
php artisan raptor:navigation --fresh

# Gerar e salvar como JSON
php artisan raptor:navigation --output=navigation.json
```

### Limpar Cache de Navegação

```bash
php artisan raptor:navigation:clear
```

## Cache

Por padrão, a navegação é armazenada em cache para melhorar o desempenho. O tempo de duração do cache e outras configurações podem ser ajustados no arquivo `config/raptor/navigation.php`.

Para forçar a regeneração da navegação sem usar o cache:

```php
$navigation = $navigationGenerator->generate(false);
```

## API HTTP

O pacote também fornece endpoints para acessar a navegação via API:

- `GET /api/raptor/navigation` - Retorna toda a estrutura de navegação
- `GET /api/raptor/navigation/group/{name}` - Retorna itens de um grupo específico
- `POST /api/raptor/navigation/clear-cache` - Limpa o cache de navegação

## Personalização Avançada

### Modificar a Aparência

As classes CSS usadas na renderização podem ser configuradas em `config/raptor/navigation.php`:

```php
'rendering' => [
    'css_classes' => [
        'container' => 'sua-classe-personalizada',
        // ...
    ],
],
```

### Estender o Gerador

Você pode estender o `RaptorNavigationGenerator` para personalizar ainda mais o comportamento:

```php
class CustomNavigationGenerator extends RaptorNavigationGenerator
{
    protected function buildNavigation(Collection $controllers): Collection
    {
        // Sua lógica personalizada aqui
        
        // Ou chame o método pai e depois modifique o resultado
        $navigation = parent::buildNavigation($controllers);
        
        // Adicionar itens personalizados
        $navigation->push([
            'label' => 'Dashboard',
            'icon' => 'HomeIcon',
            'sort' => -100, // Colocar primeiro
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ]);
        
        return $navigation;
    }
}
```

## Considerações de Desempenho

O escaneamento de controladores pode ser uma operação custosa, especialmente em aplicações grandes. Recomendamos:

1. Usar o cache em produção
2. Ajustar o TTL do cache conforme necessário
3. Limpar o cache após atualizar controladores:
   ```php
   // Em um Service Provider ou evento
   if (app()->environment('production')) {
       app(RaptorNavigationGenerator::class)->clearCache();
   }
   ```

## Integração com Permissões e ACL

Você pode filtrar a navegação baseada nas permissões do usuário atual:

```php
// No middleware ou controlador
$navigation = $navigationGenerator->generate();

// Filtrar itens baseados em permissões
$filteredNavigation = $navigation->map(function ($item) use ($user) {
    // Para grupos, filtrar seus subitens
    if (isset($item['isGroup']) && $item['isGroup']) {
        $item['items'] = $item['items']->filter(function ($subitem) use ($user) {
            return $user->hasPermission($subitem['slug'] . '.index');
        })->values();
        
        // Remover grupos vazios
        return $item['items']->count() > 0 ? $item : null;
    }
    
    // Para itens simples, verificar a permissão
    return $user->hasPermission($item['slug'] . '.index') ? $item : null;
})->filter()->values();

// Compartilhar a navegação filtrada
Inertia::share('navigation', $filteredNavigation);
```

## Solução de Problemas

### A navegação não está sendo gerada

- Verifique se os controladores estão nos diretórios configurados
- Certifique-se de que eles estendem `RaptorController`
- Confirme que eles implementam `NavigationGroupInterface`

### Itens faltando ou na ordem errada

- Verifique as propriedades `navigationSort` e `navigationGroupSort`
- Limpe o cache usando `php artisan raptor:navigation:clear`

### Problemas de desempenho

- Ajuste o TTL do cache
- Considere reduzir o número de diretórios escaneados
- Use o comando Artisan para pré-gerar a navegação durante o deploy

## Conclusão

O sistema de navegação do Raptor fornece uma solução robusta e flexível para gerenciar menus em sua aplicação. Ao aproveitar as informações já definidas em seus controladores, ele mantém a navegação sincronizada com as funcionalidades disponíveis na aplicação.