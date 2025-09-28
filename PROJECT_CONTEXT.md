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
- `document`: CPF ou CNPJ

**`products`** — produtos disponíveis para venda
- `active` boolean: permite desativar sem excluir (preserva histórico)
- `price`: preço atual do produto

**`orders`** — cabeçalho do pedido
- `status` enum: `aberto`, `confirmado`, `entregue`, `cancelado`
- `total`: valor calculado e armazenado (imutável — não recalculado se preço mudar)
- `user_id`: FK para o usuário que registrou o pedido
- `customer_id`: FK para o cliente

**`order_items`** — itens do pedido (tabela pivô entre orders e products)
- `unit_price`: preço no momento da venda (imutável — histórico correto)
- `quantity`: quantidade do item
- `subtotal`: `quantity × unit_price`, armazenado por conveniência

---

## 5. O que Foi Implementado

- [x] Projeto Laravel 12 criado via `composer create-project`
- [x] Ambiente Docker configurado — apenas MariaDB em container
- [x] PHP 8.3, Composer e npm instalados no host Ubuntu
- [x] Extensão `pdo_mysql` instalada no host (`php8.3-mysql`)
- [x] Arquivo `.env` configurado com `DB_HOST=127.0.0.1` (banco via Docker na porta 3306)
- [x] Migrations padrão do Laravel executadas (`users`, `sessions`, `cache`, `jobs`)
- [x] Aplicação acessível em `http://localhost:8000` via `php artisan serve`
- [x] Modelagem do banco de dados definida (entidades, relacionamentos e decisões de design)
- [x] Laravel Breeze instalado com stack Blade + Tailwind + Vite
- [x] Assets compilados com `npm run build`
- [x] Autenticação funcionando: login, registro, logout, recuperação de senha
- [x] Campo `role` adicionado à tabela `users` via migration (`admin`, `vendedor`, `visualizador`)
- [x] Model `User` atualizado: `role` adicionado ao `$fillable` e `$casts`
- [x] Gates definidos no `AppServiceProvider` com hierarquia de papéis
- [x] Seeder `UserSeeder` criado com três usuários de teste

---

## 6. Próximos Passos

### Fase 1 — Autenticação ✅
- [x] Instalar e configurar o **Laravel Breeze**
- [x] Entender rotas protegidas com `middleware('auth')`
- [x] Adicionar o campo `role` na tabela `users`
- [x] Configurar **Gates** para controle de acesso por papel

### Fase 2 — Migrations e Models
- [ ] Criar migrations para `customers`, `products`, `orders`, `order_items`
- [ ] Criar Models com relacionamentos Eloquent (`hasMany`, `belongsTo`, `belongsToMany`)
- [ ] Criar Seeders para popular o banco com dados de exemplo

### Fase 3 — Livewire e CRUD
- [ ] Instalar o **Livewire**
- [ ] CRUD de Clientes
- [ ] CRUD de Produtos
- [ ] Criação e gestão de Pedidos (com itens dinâmicos)
- [ ] Filtros, paginação e validação em tempo real

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

### Laravel
- Lógica de negócio nos Models ou Services — Controllers finos
- Variáveis de ambiente sempre via `env()` ou `config()` — nunca hardcoded
- Migrations para toda alteração de banco — nunca alterar o banco manualmente
- Um commit por funcionalidade concluída

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
```

---

## 9. Usuários de Teste (Seeder)

| Nome | E-mail | Senha | Papel |
|---|---|---|---|
| Administrador | admin@admin.com | password | admin |
| Vendedor | vendedor@vendedor.com | password | vendedor |
| Visualizador | visualizador@visualizador.com | password | visualizador |

---

*Última atualização: Ambiente simplificado — PHP/Composer/Artisan/npm no host Ubuntu, MariaDB em Docker. Fase 1 concluída. Próximo passo: Fase 2 — Migrations e Models.*
