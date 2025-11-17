# PROJECT_CONTEXT.md

> Documento de referência do projeto. Mantenha-o atualizado a cada etapa concluída.
> Útil para retomar o contexto em novas sessões de desenvolvimento ou de estudo.

---

## 1. Visão Geral

Sistema de **Pedidos de Venda** desenvolvido com Laravel 12, com fins de aprendizado,
consolidação de boas práticas e construção de portfólio para vagas de desenvolvedor Laravel.

O objetivo é evoluir o projeto gradualmente — começando simples e incorporando ferramentas
e padrões usados em projetos reais do mercado.

Módulos previstos: autenticação de usuários, cadastro de clientes, cadastro de produtos
e pedidos de venda.

---

## 2. Stack e Decisões Técnicas

### Linguagem e Framework
| Tecnologia | Versão | Motivo |
|---|---|---|
| PHP | 8.3 | Versão estável instalada no host Ubuntu |
| Laravel | 12 | Versão anterior à 13 (muito recente), mais madura e com mais referências |
| Composer | 2.x | Gerenciador de dependências PHP, rodado no host |

### Front-end
| Tecnologia | Decisão |
|---|---|
| Blade | Template engine padrão do Laravel. Familiar, simples, sem configuração extra |
| Livewire | Interatividade sem JavaScript manual. A ser instalado na Fase 3 |
| Tailwind CSS | Compilado via Vite (gerado pelo Breeze) |
| Node.js | Instalado no host para compilação de assets com Vite via npm |

### Banco de Dados
| Tecnologia | Decisão |
|---|---|
| MariaDB 11 | Alternativa open-source ao MySQL, totalmente compatível. Roda em container Docker |
| Driver | `pdo_mysql` (extensão PHP padrão para MySQL/MariaDB) |
| Charset | `utf8mb4` com collation `utf8mb4_unicode_ci` (UTF-8 completo, suporta emojis) |

### Infraestrutura / Ambiente
| Tecnologia | Decisão |
|---|---|
| Docker | Usado exclusivamente para o banco de dados (MariaDB) |
| PHP (host) | PHP 8.3 instalado diretamente no Ubuntu — roda Artisan, serve a aplicação |
| Composer (host) | Instalado no Ubuntu — gerencia dependências PHP |
| npm (host) | Instalado no Ubuntu — compila assets com Vite |
| `php artisan serve` | Servidor de desenvolvimento embutido do Laravel |

### Decisões de Segurança
- Usuário de banco dedicado (`pedido_venda_user`) com acesso apenas ao banco do projeto — princípio do menor privilégio
- Arquivo `.env` no `.gitignore` — credenciais nunca vão ao repositório
- `.env.example` versionado como template sem valores sensíveis

### Decisões de Modelagem (Fase 2)
| Decisão | Escolha | Motivo |
|---|---|---|
| `orders.status` | Enum PHP nativo (`App\Enums\OrderStatus`), coluna no banco como `string` | Alterar um ENUM nativo do MySQL/MariaDB exige `ALTER TABLE ... MODIFY`, mais custoso. Com `string` + cast de Enum PHP, novos status são adicionados só editando a classe, sem migration |
| Soft deletes | `customers` e `products` (`deleted_at`) | `orders` nunca é excluído — nem física nem logicamente — apenas muda de status. É registro histórico imutável. `products` recebeu soft delete na Fase 3 para evitar `QueryException` de integridade referencial ao excluir produtos já vinculados a `order_items` |
| `customers.document` (CPF/CNPJ) | Sem `unique()` por enquanto | Decisão consciente para simplificar a Fase 2; pode ser adicionada depois via nova migration se necessário |
| `order_items.unit_price` | Copiado do produto no momento da criação do item (nunca referência dinâmica) | Preserva o preço histórico da venda, mesmo que o preço do produto mude depois |
| `order_items.quantity` | `decimal(10,2)` em vez de inteiro (alterado após Fase 2) | Permite vender produtos por peso/medida fracionária (ex: 2,5 kg), não só unidades inteiras |
| `orders.total` | Armazenado e recalculado via `Order::recalculateTotal()` | Não é calculado dinamicamente em toda consulta — evita custo de agregação repetida e mantém o valor congelado após confirmação |
| FKs com `cascadeOnDelete()` | Apenas em `order_items.order_id` | Única cascade necessária, já que `orders` é imutável na prática; demais FKs ficam sem cascade para evitar exclusão acidental de dados históricos |

---

## 3. Estrutura do Ambiente

```
┌─────────────────────────────────────────┐
│              Ubuntu (host)              │
│                                         │
│  PHP 8.3 + Composer + npm               │
│  php artisan serve → :8000              │
│                                         │
│  ┌──────────────────────────────────┐   │
│  │  Docker                          │   │
│  │  ┌────────────────────────────┐  │   │
│  │  │  db (MariaDB 11) → :3306   │  │   │
│  │  └────────────────────────────┘  │   │
│  └──────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

**Volume persistente:** `db_data` (dados do banco sobrevivem ao `docker-compose down`)

---

## 4. Modelagem do Banco de Dados

### Entidades e relacionamentos

```
users           → registra →    orders
customers       → realiza  →    orders
orders          → contém   →    order_items
products        → compõe   →    order_items
```

### Tabelas

**`users`** — operadores do sistema (quem usa a aplicação)
- `role` enum: `admin`, `vendedor`, `visualizador`
- Controle de acesso via Policies e Gates do Laravel

**`customers`** — clientes (quem compra)
- Separado de `users` intencionalmente: cliente ≠ usuário do sistema
- `document`: CPF ou CNPJ (sem constraint `unique`)
- Soft deletes (`deleted_at`)

**`products`** — produtos disponíveis para venda
- `active` boolean: permite desativar sem excluir (preserva histórico)
- `price`: preço atual do produto (`decimal(10,2)`)
- Soft deletes (`deleted_at`) — adicionado na Fase 3 para permitir exclusão segura mesmo com produtos já vendidos em pedidos anteriores

**`orders`** — cabeçalho do pedido
- `status`: string no banco, casteado para Enum PHP `App\Enums\OrderStatus` (`aberto`, `confirmado`, `entregue`, `cancelado`)
- `total`: valor calculado e armazenado (imutável — não recalculado se preço mudar; atualizado via `recalculateTotal()`)
- `user_id`: FK para o usuário que registrou o pedido
- `customer_id`: FK para o cliente
- Sem soft delete — pedidos nunca são excluídos, só mudam de status

**`order_items`** — itens do pedido (tabela pivô entre orders e products)
- `unit_price`: preço no momento da venda (imutável — histórico correto)
- `quantity`: quantidade do item, `decimal(10,2)` — permite frações (ex: 2,5 kg)
- `subtotal`: `quantity × unit_price`, armazenado por conveniência
- `order_id` com `cascadeOnDelete()`

---

## 5. O que Foi Implementado

### Fase 1 — Autenticação
- [x] Projeto Laravel 12 criado via `composer create-project`
- [x] Ambiente Docker configurado — apenas MariaDB em container
- [x] PHP 8.3, Composer e npm instalados no host Ubuntu
- [x] Extensão `pdo_mysql` instalada no host (`php8.3-mysql`)
- [x] Arquivo `.env` configurado com `DB_HOST=127.0.0.1` (banco via Docker na porta 3306)
- [x] Migrations padrão do Laravel executadas (`users`, `sessions`, `cache`, `jobs`)
- [x] Aplicação acessível em `http://localhost:8000` via `php artisan serve`
- [x] Laravel Breeze instalado com stack Blade + Tailwind + Vite
- [x] Assets compilados com `npm run build`
- [x] Autenticação funcionando: login, registro, logout, recuperação de senha
- [x] Campo `role` adicionado à tabela `users` via migration (`admin`, `vendedor`, `visualizador`)
- [x] Model `User` atualizado: `role` adicionado ao `$fillable` e `$casts`
- [x] Gates definidos no `AppServiceProvider` com hierarquia de papéis
- [x] Seeder `UserSeeder` criado com três usuários de teste

### Fase 2 — Migrations, Models e Seeders
- [x] Migrations criadas para `customers`, `products`, `orders`, `order_items`
- [x] Enum PHP nativo `App\Enums\OrderStatus` criado (com método `label()`)
- [x] Models `Customer`, `Product`, `Order`, `OrderItem` criados com relacionamentos Eloquent (`hasMany`, `belongsTo`)
- [x] Relacionamento `User::orders()` adicionado
- [x] `$fillable` e `$casts` configurados em todos os Models (incluindo `decimal:2` para valores monetários e cast de Enum em `Order::status`)
- [x] Método `Order::recalculateTotal()` implementado
- [x] Faker configurado com locale `pt_BR` em `config/app.php` (`fake()->cpf()`, `fake()->cnpj()`)
- [x] Factories criadas: `CustomerFactory`, `ProductFactory`, `OrderFactory` (com hook `afterCreating` para gerar itens), `OrderItemFactory`
- [x] `DatabaseSeeder` atualizado para popular `customers`, `products` e `orders` reaproveitando registros existentes via `inRandomOrder()->first()`
- [x] `migrate:fresh --seed` validado — totais dos pedidos batem com a soma dos itens

**Problemas encontrados e resolvidos na Fase 2** *(registrado para referência futura)*:
- `Unknown format "cnpj"` no Faker → causado pela linha padrão `'faker_locale' => env('APP_FAKER_LOCALE', 'pt_BR')` em `config/app.php` não estar de fato resolvendo para `pt_BR` (conflito com `.env`/cache). Corrigido fixando o valor diretamente: `'faker_locale' => 'pt_BR'`
- `Class "App\Enums\OrderStatus" not found` → o `php artisan make:enum OrderStatus` criou o arquivo em diretório diferente do esperado. Corrigido movendo o arquivo para `app/Enums/OrderStatus.php`, compatível com o namespace `App\Enums` (PSR-4)

### Ajustes pós-Fase 2
- [x] `order_items.quantity` alterado de `unsignedInteger` para `decimal(10,2)`, via migration adicional (`change_quantity_to_decimal_in_order_items_table`) usando `->change()`. Motivo: permitir quantidades fracionárias (venda por peso/medida). `$casts` do Model `OrderItem` e `OrderItemFactory` (com `randomFloat`) atualizados de acordo

### Fase 3 — Livewire e CRUD (em andamento)
- [x] Livewire instalado (`composer require livewire/livewire`)
- [x] `@livewireStyles` e `@livewireScripts` adicionados ao `layouts/app.blade.php`
- [x] Gate `manage-customers` criada no `AppServiceProvider` (regra: `admin` e `vendedor` têm acesso total; `visualizador` só leitura)
- [x] CRUD de Clientes completo via componentes Livewire full-page:
  - `App\Livewire\Customers\Index` — listagem com busca (nome/documento/e-mail) e paginação
  - `App\Livewire\Customers\Create` e `Edit` — páginas separadas (não modal), com validação via `rules()`
  - Exclusão (soft delete) feita direto no `Index`, protegida por `Gate::authorize()`
- [x] Rotas de clientes protegidas com `middleware('can:manage-customers')` em criar/editar; listagem acessível a todos autenticados
- [x] CRUD de Produtos completo via componentes Livewire full-page:
  - `App\Livewire\Products\Index` — listagem com busca por nome, filtro por status (ativo/inativo/todos) e paginação
  - `App\Livewire\Products\Create` e `Edit` — páginas separadas, com validação via `rules()`
  - Ação `toggleActive()` para ativar/desativar sem excluir (`active` boolean)
  - Exclusão feita via soft delete, protegida por `Gate::authorize()`
- [x] Soft delete adicionado a `products` via migration adicional (`add_soft_deletes_to_products_table`) — decisão tomada durante a Fase 3, antes mesmo de o problema de FK ocorrer, ao perceber que exclusão física de produto já vendido geraria `QueryException` de integridade referencial
- [x] CRUD de Pedidos completo via componentes Livewire full-page:
  - `App\Livewire\Orders\Index` — listagem com busca por cliente, filtro por status e paginação; botões de transição de status (Confirmar, Entregar, Cancelar) respeitando a ordem do fluxo
  - `App\Livewire\Orders\Create` e `Edit` — páginas separadas, com itens dinâmicos (adicionar/remover produto, quantidade), preço unitário copiado do produto no momento da adição, subtotal e total calculados ao vivo
  - `Edit` só é acessível enquanto `status === Aberto`; fora disso, retorna 403
  - Lógica de manipulação de itens (`addItem`, `removeItem`, cálculo de subtotal/total) extraída para a trait `App\Livewire\Orders\Concerns\HasDynamicOrderItems`, reaproveitada entre `Create` e `Edit`
  - Tabela de itens do formulário extraída para partial `livewire.orders.partials._customer-and-items-form`, reaproveitada entre `create.blade.php` e `edit.blade.php`
- [x] Duas Gates para pedidos: `manage-orders` (criar, editar itens, confirmar, entregar — `admin` e `vendedor`) e `cancel-orders` (cancelar — somente `admin`)

**Problemas encontrados e resolvidos na Fase 3** *(registrado para referência futura)*:
- `MissingLayoutException: Livewire page component layout view not found: [components.layouts.app]` → Livewire 3, ao usar um componente como página inteira (via rota), procura por padrão um layout em `resources/views/components/layouts/app.blade.php`. O Breeze cria o layout em `resources/views/layouts/app.blade.php` (sem ser Blade component). Corrigido publicando a config (`php artisan livewire:publish --config`) e ajustando `'layout' => 'layouts.app'` em `config/livewire.php`
- `Uncaught (in promise) TypeError: Alpine.navigate is not a function` ao usar `redirect(..., navigate: true)` → conflito entre a instância do Alpine.js importada manualmente pelo Breeze em `resources/js/app.js` e a instância própria (com plugins) que o Livewire injeta via `@livewireScripts`. Corrigido removendo a importação e inicialização manual do Alpine (`import Alpine from 'alpinejs'`, `window.Alpine = Alpine`, `Alpine.start()`) do `app.js`, deixando o Livewire ser a única fonte do Alpine
- Decisão preventiva: `products` recebeu soft delete durante a Fase 3 (não na Fase 2) — motivo: exclusão física de um produto já referenciado em `order_items` violaria a FK, já que só `order_items.order_id` tem `cascadeOnDelete()`

---

## 6. Próximos Passos

### Fase 1 — Autenticação ✅
- [x] Instalar e configurar o **Laravel Breeze**
- [x] Entender rotas protegidas com `middleware('auth')`
- [x] Adicionar o campo `role` na tabela `users`
- [x] Configurar **Gates** para controle de acesso por papel

### Fase 2 — Migrations e Models ✅
- [x] Criar migrations para `customers`, `products`, `orders`, `order_items`
- [x] Criar Models com relacionamentos Eloquent (`hasMany`, `belongsTo`, `belongsToMany`)
- [x] Criar Seeders para popular o banco com dados de exemplo

### Fase 3 — Livewire e CRUD
- [X] Instalar o **Livewire**
- [X] CRUD de Clientes
- [X] CRUD de Produtos
- [X] Criação e gestão de Pedidos (com itens dinâmicos)
- [X] Filtros, paginação e validação em tempo real

### Fase 4 — Recursos Avançados
- [ ] Notificações por e-mail
- [ ] Filas com **Laravel Horizon**
- [ ] Debug com **Laravel Telescope**
- [ ] Testes com **Pest**

### Fase 5 — Portfólio
- [ ] Painel administrativo com **Filament**
- [ ] Deploy com Docker em produção (stack completa: PHP-FPM + Nginx + MariaDB)
- [ ] Documentação da API (se aplicável)

---

## 7. Convenções e Padrões Adotados

### Nomenclatura
| Elemento | Convenção | Exemplo |
|---|---|---|
| Tabelas | `snake_case`, plural | `order_items` |
| Models | `PascalCase`, singular | `OrderItem` |
| Controllers | `PascalCase` + sufixo | `OrderController` |
| Migrations | prefixo de data automático | `2024_01_01_000000_create_orders_table` |
| Rotas | `kebab-case` | `/pedidos-venda` |
| Variáveis PHP | `camelCase` | `$orderItem` |
| Métodos | `camelCase` | `getTotal()` |

### Banco de Dados
- Sempre usar `utf8mb4` e `utf8mb4_unicode_ci`
- Chaves estrangeiras com `constrained()` nas migrations (integridade referencial)
- Preços e valores monetários como `decimal(10, 2)`
- Nunca excluir registros que fazem parte de histórico — usar `active` ou `soft deletes`
- Armazenar `unit_price` no item do pedido — nunca recalcular pelo preço atual do produto
- Status de entidades com fluxo de ciclo de vida (ex: `orders.status`) usam Enum PHP nativo casteado, não ENUM de banco

### Laravel
- Lógica de negócio nos Models ou Services — Controllers finos
- Variáveis de ambiente sempre via `env()` ou `config()` — nunca hardcoded
- Migrations para toda alteração de banco — nunca alterar o banco manualmente
- Um commit por funcionalidade concluída
- Lógica repetida entre componentes Livewire de páginas diferentes (ex: `Create`/`Edit` do mesmo domínio) vai para uma trait em `Concerns/`; views repetidas entre essas páginas vão para uma partial Blade

### Ambiente de Desenvolvimento
- PHP, Composer, Artisan e npm rodam **diretamente no host** Ubuntu
- Apenas o banco de dados (MariaDB) roda em container Docker
- Comandos sem prefixo — `php artisan`, `composer`, `npm` direto no terminal

---

## 8. Comandos Úteis

```bash
# Subir o banco de dados
docker-compose up -d

# Derrubar o banco de dados
docker-compose down

# Ver logs do banco
docker-compose logs -f db

# Subir a aplicação
php artisan serve
# → acessível em http://localhost:8000

# Rodar migrations
php artisan migrate

# Recriar banco do zero e rodar seeders (apenas em desenvolvimento)
php artisan migrate:fresh --seed

# Instalar dependências PHP
composer install

# Instalar dependências JS e compilar assets
npm install
npm run build

# Compilar assets em modo watch (desenvolvimento)
npm run dev

# Acessar o banco via container
docker-compose exec db mariadb -u pedido_venda_user -p laravel-pedido-venda

# Limpar cache de config (útil se alterações em config/*.php não refletirem)
php artisan config:clear
```

---

## 9. Usuários de Teste (Seeder)

| Nome | E-mail | Senha | Papel |
|---|---|---|---|
| Administrador | admin@admin.com | password | admin |
| Vendedor | vendedor@vendedor.com | password | vendedor |
| Visualizador | visualizador@visualizador.com | password | visualizador |

---

*Última atualização: Fase 3 iniciada — instalação do Livewire e CRUD de Clientes. Próximo passo: Fase 3 — CRUD de produtos.*
