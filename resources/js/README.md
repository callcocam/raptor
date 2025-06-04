# 📊 Raptor DataTable System

Sistema de tabelas dinâmicas profissional para o pacote Raptor, integrado com Laravel e React.

## 🏗️ Estrutura

```
packages/callcocam/raptor/resources/js/
├── components/
│   ├── ui/           # Componentes base reutilizáveis
│   │   └── table.tsx # Componentes base de tabela (Shadcn/UI style)
│   ├── data-table/   # Sistema completo de DataTable
│   │   ├── data-table.tsx
│   │   └── index.ts
│   └── crud/         # Componentes CRUD específicos
│       └── crud-index.tsx
├── hooks/            # Hooks customizados
│   └── use-data-table.ts
├── lib/              # Utilitários e helpers
│   └── utils.ts
├── types/            # Definições TypeScript
│   └── index.ts
├── index.ts          # Exportações principais
└── README.md         # Este arquivo
```

## 🚀 Uso Básico

### 1. Importação

```typescript
import { DataTable, CrudIndex } from 'packages/callcocam/raptor/resources/js';
```

### 2. Componente DataTable

```tsx
import { DataTable } from 'packages/callcocam/raptor/resources/js';

function MyPage({ data, columns, actions, routeNameBase }) {
  return (
    <DataTable
      data={data}              // PaginatedData do Laravel
      columns={columns}        // Configuração das colunas
      actions={actions}        // Ações disponíveis (ver, editar, etc.)
      routeNameBase={routeNameBase} // Base das rotas (ex: "admin.users")
    />
  );
}
```

### 3. Componente CrudIndex (Mais Completo)

```tsx
import { CrudIndex } from 'packages/callcocam/raptor/resources/js';

function UserIndex(props) {
  return (
    <CrudIndex
      {...props}
      pageTitle="Usuários"
      pageDescription="Gerencie os usuários do sistema"
      createButtonText="Criar Usuário"
    />
  );
}
```

## 📋 Props e Tipos

### DataTableProps

```typescript
interface DataTableProps<T = any> {
  data: PaginatedData<T>;        // Dados paginados do Laravel
  columns: TableColumn[];        // Configuração das colunas
  actions?: Action[];            // Ações disponíveis
  routeNameBase: string;         // Base das rotas
  searchable?: boolean;          // Permitir busca
  sortable?: boolean;            // Permitir ordenação
  // ... outros props opcionais
}
```

### TableColumn

```typescript
interface TableColumn {
  accessorKey: string;           // Chave do campo no objeto
  header: string;                // Título da coluna
  sortable?: boolean;            // Coluna ordenável
  searchable?: boolean;          // Coluna pesquisável
  hideable?: boolean;            // Coluna pode ser ocultada
  type?: 'text' | 'number' | 'date' | 'boolean' | 'select' | 'image' | 'html';
  formatter?: 'formatDate' | 'renderBadge' | 'currency' | 'percentage';
  options?: Record<string, any>; // Opções do formatter
  cell?: (row: any) => React.ReactNode; // Função customizada para célula
}
```

### Action

```typescript
interface Action {
  id: string;                    // ID único da ação
  icon: string;                  // Ícone da ação
  color: 'primary' | 'secondary' | 'success' | 'warning' | 'danger';
  routeNameBase: string;         // Base da rota
  routeSuffix: string;           // Sufixo da rota (show, edit, destroy)
  header: string;                // Nome da ação
  tooltip?: string;              // Tooltip
}
```

## 🎨 Formatação de Colunas

### Tipos Suportados

```typescript
// Texto simples
{ accessorKey: 'name', header: 'Nome', type: 'text' }

// Número formatado
{ accessorKey: 'price', header: 'Preço', type: 'number' }

// Data formatada
{ accessorKey: 'created_at', header: 'Criado em', type: 'date' }

// Boolean (Sim/Não)
{ accessorKey: 'active', header: 'Ativo', type: 'boolean' }

// HTML renderizado
{ accessorKey: 'description', header: 'Descrição', type: 'html' }
```

### Formatters Especiais

```typescript
// Data customizada
{
  accessorKey: 'created_at',
  header: 'Data',
  type: 'date',
  formatter: 'formatDate',
  options: { day: '2-digit', month: '2-digit', year: 'numeric' }
}

// Badge colorido
{
  accessorKey: 'status',
  header: 'Status',
  formatter: 'renderBadge',
  options: {
    'active': 'success',
    'inactive': 'secondary',
    'pending': 'warning'
  }
}

// Moeda
{
  accessorKey: 'amount',
  header: 'Valor',
  formatter: 'currency'
}

// Porcentagem
{
  accessorKey: 'completion',
  header: 'Progresso',
  formatter: 'percentage'
}
```

### Célula Customizada

```typescript
{
  accessorKey: 'user',
  header: 'Usuário',
  cell: (row) => (
    <div className="flex items-center space-x-2">
      <img src={row.avatar} className="w-8 h-8 rounded-full" />
      <span>{row.name}</span>
    </div>
  )
}
```

## 🔗 Integração com AbstractController

O sistema é totalmente integrado com o `AbstractController.php`:

```php
// No Controller
public function index()
{
    return inertia('admin/crud/Index', [
        'data' => $this->paginated(),
        'columns' => $this->columns(),
        'actions' => $this->actions(),
        'routeNameBase' => 'admin.users',
        // ...
    ]);
}
```

## 🎯 Roadmap

- ✅ Estrutura base criada
- ✅ Componente DataTable básico
- ✅ Integração com tipos existentes
- ✅ Componente CrudIndex
- 🔄 Integração com TanStack Table (em progresso)
- ⏳ Sistema de filtros avançados
- ⏳ Busca global
- ⏳ Ordenação por colunas
- ⏳ Exportação de dados
- ⏳ Seleção múltipla
- ⏳ Ações em lote

## 📦 Dependências

```json
{
  "dependencies": {
    "@tanstack/react-table": "^8.x",
    "clsx": "^2.x",
    "tailwind-merge": "^2.x"
  }
}
```

## 🏃‍♂️ Próximos Passos

1. **Instalar dependências** do TanStack Table
2. **Testar integração** com dados reais
3. **Implementar filtros** avançados
4. **Adicionar busca** global
5. **Criar componentes** para formulários (Create/Edit) 