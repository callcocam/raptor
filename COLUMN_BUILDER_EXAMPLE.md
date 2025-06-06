# Column Builder - Data Table Dinâmica

O Column Builder permite criar colunas de data table dinamicamente a partir de configurações enviadas pelo backend.

## Estrutura das Colunas do Backend

```php
// Exemplo em PHP/Laravel
$columns = [
    [
        'accessorKey' => 'name',
        'header' => 'Nome',
        'id' => 'name',
        'sortable' => true,
        'enableHiding' => true,
    ],
    [
        'accessorKey' => 'email',
        'header' => 'E-mail',
        'id' => 'email',
        'sortable' => false,
        'enableHiding' => true,
    ],
    [
        'accessorKey' => 'created_at',
        'header' => 'Criado em',
        'id' => 'created_at',
        'sortable' => true,
        'enableHiding' => true,
        'formatter' => 'formatDate',
        'formatterOptions' => 'dd/MM/yyyy HH:mm',
    ],
    [
        'accessorKey' => 'status',
        'header' => 'Status',
        'id' => 'status',
        'sortable' => true,
        'enableHiding' => true,
        'formatter' => 'formatBadge',
    ],
    [
        'accessorKey' => 'actions',
        'header' => 'Ações',
        'id' => 'actions',
        'sortable' => false,
        'enableHiding' => false,
    ],
];
```

## 🆕 Estrutura dos Filtros do Backend

```php
// Filtros dinâmicos
$filterOptions = [
    [
        'column' => 'status',
        'label' => 'Status',
        'type' => 'select',
        'options' => [
            ['value' => 'published', 'label' => 'Publicado'],
            ['value' => 'draft', 'label' => 'Rascunho'],
            ['value' => 'archived', 'label' => 'Arquivado'],
        ]
    ],
    [
        'column' => 'category',
        'label' => 'Categoria',
        'type' => 'select',
        'options' => [
            ['value' => 'news', 'label' => 'Notícias'],
            ['value' => 'blog', 'label' => 'Blog'],
            ['value' => 'tutorial', 'label' => 'Tutorial'],
        ]
    ]
];
```

## Formatadores Disponíveis

### formatDate
Formata datas para o padrão brasileiro
```typescript
formatter: 'formatDate'
formatterOptions: 'dd/MM/yyyy HH:mm' // opcional
```

### formatCurrency
Formata valores monetários para Real (R$)
```typescript
formatter: 'formatCurrency'
```

### formatBadge
Renderiza o valor como um Badge
```typescript
formatter: 'formatBadge'
```

### formatBoolean
Converte boolean para "Sim" / "Não"
```typescript
formatter: 'formatBoolean'
```

## 🆕 Uso no Frontend com Filtros

```tsx
import { DataTable, buildColumnsFromBackend, type BackendColumn, type BackendFilter } from '@raptor/components/table';

function MinhaPagina(props: { 
    data: any[], 
    columns: BackendColumn[],
    filterOptions: BackendFilter[]
}) {
    const tableConfig = {
        searchColumns: ['name', 'title', 'email'],
        searchPlaceholder: 'Pesquisar...',
        filters: props.filterOptions, // 🔥 Filtros dinâmicos do backend
        showSelectColumn: true,
        showActionsColumn: true,
    };
    
    const tableColumns = buildColumnsFromBackend(props.columns, tableConfig);
    
    return (
        <DataTable 
            data={props.data} 
            columns={tableColumns}
            searchPlaceholder={tableConfig.searchPlaceholder}
            searchColumns={tableConfig.searchColumns}
            filters={tableConfig.filters}
        />
    );
}
```

## Exemplo Completo com Inertia.js

```php
// Controller Laravel
public function index()
{
    $users = User::all();
    
    $columns = [
        [
            'accessorKey' => 'name',
            'header' => 'Nome',
            'id' => 'name',
            'sortable' => true,
            'enableHiding' => true,
        ],
        [
            'accessorKey' => 'email',
            'header' => 'E-mail', 
            'id' => 'email',
            'sortable' => true,
            'enableHiding' => true,
        ],
        [
            'accessorKey' => 'created_at',
            'header' => 'Criado em',
            'id' => 'created_at', 
            'sortable' => true,
            'enableHiding' => true,
            'formatter' => 'formatDate',
            'formatterOptions' => 'dd/MM/yyyy HH:mm',
        ],
    ];
    
    // 🆕 Filtros dinâmicos
    $filterOptions = [
        [
            'column' => 'status',
            'label' => 'Status',
            'type' => 'select',
            'options' => [
                ['value' => 'active', 'label' => 'Ativo'],
                ['value' => 'inactive', 'label' => 'Inativo'],
            ]
        ]
    ];
    
    return Inertia::render('Admin/Users/Index', [
        'data' => $users,
        'columns' => $columns,
        'filterOptions' => $filterOptions, // 🔥 Enviar filtros
        'pageTitle' => 'Usuários',
        'pageDescription' => 'Gerenciar usuários do sistema',
    ]);
}
```

## Personalização

Para adicionar novos formatadores, edite o arquivo `column-builder.tsx`:

```tsx
const formatters = {
    // Formatadores existentes...
    
    meuFormatador: (value: any, options?: string) => {
        // Sua lógica de formatação
        return <span>{value}</span>;
    }
}
```

## ✅ Funcionalidades

- ✅ **Colunas dinâmicas** configuradas pelo backend
- ✅ **Filtros dinâmicos** baseados em `filterOptions`
- ✅ **Busca flexível** em múltiplas colunas
- ✅ **Formatadores** para diferentes tipos de dados
- ✅ **Ações customizáveis** por linha
- ✅ **Ordenação e paginação** automáticas
- ✅ **Zero hardcode** no frontend
- ✅ **Prevenção de duplicação** de colunas
- ✅ **Compatibilidade** com ações do backend

## 🔧 Tratamento de Colunas Especiais

### Coluna de Seleção (`select`)
- Adicionada automaticamente se `showSelectColumn: true`
- **Não é adicionada** se o backend já enviar uma coluna com `id: "select"`

### Coluna de Ações (`actions`)
- Adicionada automaticamente se `showActionsColumn: true`
- **Não é adicionada** se o backend já enviar uma coluna com `id: "actions"`
- Se o backend enviar uma coluna `actions`, ela usará automaticamente a configuração de `actionsConfig`

### Exemplo com Ações do Backend

```php
// Controller Laravel - Enviando coluna actions
$columns = [
    [
        'accessorKey' => 'name',
        'header' => 'Nome',
        'id' => 'name',
        'sortable' => true,
        'enableHiding' => true,
    ],
    [
        'accessorKey' => 'actions', // ⚠️ Não precisa de accessorKey real
        'header' => 'Ações',
        'id' => 'actions', // 🔥 Coluna será tratada especialmente
        'sortable' => false,
        'enableHiding' => false,
    ]
];
```

**Resultado:** Apenas uma coluna de ações será renderizada, usando a configuração de `actionsConfig`. 