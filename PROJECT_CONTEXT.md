# PROJECT_CONTEXT.md

> Documento de referência do projeto. Mantenha-o atualizado a cada etapa concluída.
> Útil para retomar o contexto em novas sessões de desenvolvimento ou de estudo.

---

## 1. Visão Geral

Sistema de **Pedidos de Venda** desenvolvido com Laravel 12, com fins de aprendizado,
consolidação de boas práticas e construção de portfólio para vagas de desenvolvedor Laravel.

O objetivo é evoluir o projeto gradualmente — começando simples e incorporando ferramentas
e padrões usados em projetos reais do mercado.

---

## 2. Stack e Decisões Técnicas

### Linguagem e Framework
| Tecnologia | Versão | Motivo |
|---|---|---|
| PHP | 8.2 | Versão estável e amplamente adotada no mercado |
| Laravel | 12 | Versão anterior à 13 (muito recente), mais madura e com mais referências |

### Front-end
| Tecnologia | Decisão |
|---|---|
| Blade | Template engine padrão do Laravel. Familiar, simples, sem configuração extra |
| Livewire | Interatividade sem JavaScript manual. Instalado sem Node.js/Vite |
| CSS | Bootstrap via CDN por enquanto. Sem compilação de assets neste momento |
| Node.js / Vite | **Não utilizado** nesta fase. Decisão consciente para não misturar conceitos |

### Banco de Dados
| Tecnologia | Decisão |
|---|---|
| MariaDB 11 | Alternativa open-source ao MySQL, totalmente compatível |
| Driver | `pdo_mysql` (extensão PHP padrão para MySQL/MariaDB) |
| Charset | `utf8mb4` com collation `utf8mb4_unicode_ci` (UTF-8 completo, suporta emojis) |

### Infraestrutura / Ambiente
| Tecnologia | Decisão |
|---|---|
| Docker | Ambiente isolado e reproduzível |
| PHP-FPM | Servidor de processos PHP (mais próximo de produção que `artisan serve`) |
| Nginx | Servidor web. Expõe apenas `/public`, o restante do código fica protegido |
| Composer | Gerenciado localmente no Ubuntu do desenvolvedor |
| Artisan | CLI do Laravel, rodado via `docker-compose exec app php artisan ...` |

### Decisões de Segurança
- Usuário de banco dedicado (`pedido_venda_user`) com acesso apenas ao banco do projeto — princípio do menor privilégio
- Pasta `public/` é a única exposta pelo Nginx
- Arquivo `.env` no `.gitignore` — credenciais nunca vão ao repositório
- `.env.example` versionado como template sem valores sensíveis

---

## 3. Estrutura de Containers Docker

```
┌─────────────────────────────────────────┐
│              docker-compose             │
│                                         │
│  ┌──────────┐   ┌──────────────────┐    │
│  │  nginx   │──▶│  app (PHP-FPM)   │    │
│  │ :8080    │   │  :9000           │    │
│  └──────────┘   └────────┬─────────┘    │
│                          │              │
│                 ┌────────▼─────────┐    │
│                 │  db (MariaDB)    │    │
│                 │  :3306           │    │
│                 └──────────────────┘    │
└─────────────────────────────────────────┘
```

**Rede:** `laravel-pedido-venda_network` (bridge)
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
- [x] Ambiente Docker configurado (PHP-FPM + Nginx + MariaDB)
- [x] Arquivo `.env` configurado com conexão ao banco via nome do serviço Docker (`DB_HOST=db`)
- [x] Migrations padrão do Laravel executadas (`users`, `sessions`, `cache`, `jobs`)
- [x] Aplicação acessível em `http://localhost:8080`
- [x] Modelagem do banco de dados definida (entidades, relacionamentos e decisões de design)

---

## 6. Próximos Passos

### Fase 1 — Autenticação
- [ ] Instalar e configurar o **Laravel Breeze**
- [ ] Entender rotas protegidas com `middleware('auth')`
- [ ] Adicionar o campo `role` na tabela `users`
- [ ] Configurar **Policies e Gates** para controle de acesso por papel

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
- [ ] Deploy com Docker em produção
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

### Docker
- Comandos Artisan sempre via `docker-compose exec app php artisan ...`
- Composer sempre via terminal local (PHP instalado no Ubuntu)
- Dados do banco persistidos em volume nomeado (`db_data`)

---

## 8. Comandos Úteis

```bash
# Subir o ambiente
docker-compose up -d

# Derrubar o ambiente
docker-compose down

# Ver logs de um container
docker-compose logs -f app

# Rodar comando Artisan
docker-compose exec app php artisan <comando>

# Rodar migrations
docker-compose exec app php artisan migrate

# Corrigir permissões de storage (quando necessário)
docker-compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Acessar o banco via container
docker-compose exec db mariadb -u pedido_venda_user -p pedidos_venda
```

---

*Última atualização: ambiente configurado e funcionando. Próximo passo: autenticação com Laravel Breeze.*
