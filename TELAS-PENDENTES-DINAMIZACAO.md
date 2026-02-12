# Telas e cadastros pendentes de dinamização

Documento gerado a partir da análise do projeto. Lista o que **já está dinâmico** (CRUD via API, GridJS com dados reais, modais de criar/editar) e o que **ainda falta** tornar dinâmico.

---

## Padrão considerado “dinâmico”

- **Listagem**: GridJS alimentado por API (`/listar` ou equivalente).
- **Criar/Editar**: Modal com formulário; envio via POST para endpoints `criar` e `atualizar/(:num)`.
- **Controller**: métodos `listar()`, `criar()`, `editar($id)`, `atualizar($id)`.
- **Rotas**: GET para listar e editar; POST para criar e atualizar.
- **JS da página**: `fetch()` para buscar dados e submeter formulários, sem dados fixos no front.

---

## Telas já dinâmicas

| Tela           | Rota base              | Observação                                                |
|----------------|-------------------------|-----------------------------------------------------------|
| Veículos       | `admin/veiculos`        | listar, criar, editar, atualizar, consultarPlaca          |
| Locatários     | `admin/locatarios`      | listar, criar, editar, atualizar                          |
| Locações       | `admin/locacoes`        | listar, criar, editar, atualizar                         |
| Financeiro     | `admin/financeiro`      | listar, criar, editar, atualizar, getCategorias           |
| Produtos       | `admin/cadastro/produtos` | listar, criar, editar, atualizar                       |
| Serviços       | `admin/cadastro/servicos` | listar, criar, editar, atualizar                      |
| Configurações  | `admin/configuracoes`  | listarPlanos, atualizarEmpresa (parcial, sem grid CRUD)   |

---

## Telas / cadastros que ainda precisam ser dinamizados

### 1. Categorias Financeiras

- **View**: `admin/categorias-financeiras/index.php`
- **Controller**: `CategoriasFinanceiras` — só `index()`.
- **JS**: `categorias-financeiras.js` — Grid com **dados fixos** no array `data: [...]`.
- **Rotas**: existe só `admin/cadastro/categorias-financeiras` (GET index). Não há listar/criar/editar/atualizar.

**O que falta:**

- [ ] Rotas: `listar`, `criar`, `editar/(:num)`, `atualizar/(:num)`.
- [ ] Controller: métodos correspondentes + uso de `CategoriaFinanceiraModel` (já existe).
- [ ] JS: trocar dados fixos por `fetch('/admin/cadastro/categorias-financeiras/listar')`.
- [ ] Modal de criar/editar (nome, tipo receita/despesa, padrão sim/não).
- [ ] Botão “Adicionar Categoria” e “Detalhes”/Editar na grid ligados ao modal e à API.

---

### 2. Contratos (aba “Meus contratos”)

- **View**: `admin/contratos/index.php`
- **Controller**: `Contratos` — em `index()` envia `meus_contratos` **mock** (array fixo no PHP).
- **JS**: `contratos.js` — usa `window.__MEUS_CONTRATOS__` (dados injetados pelo PHP), não chama API.
- **Rotas**: só `admin/contratos` (GET index). Nenhuma rota de listar/criar/editar/atualizar para contratos.

**O que falta:**

- [ ] Rotas para contratos: `listar`, `criar`, `editar/(:num)`, `atualizar/(:num)` (e modelo/tabela de contratos se ainda não houver).
- [ ] Controller: métodos que leiam/escrevam em banco (ou integrem com locações/contratos reais).
- [ ] JS: carregar a tabela “Meus contratos” via `fetch(.../listar)` em vez de `__MEUS_CONTRATOS__`.
- [ ] Modal criar/editar contrato (número, locatário, veículo, início, término, valor, status, etc.).
- [ ] Aba “Modelos de contratos” já usa DB; manter e, se desejado, padronizar com o mesmo fluxo (listar/editar via API).

---

### 3. Cobranças

- **View**: `admin/cobrancas/index.php`
- **Controller**: `Cobrancas` — só `index()`, sem dados do banco.
- **JS**: `cobrancas.js` — Grid com **dataset simulado** (array fixo de objetos no JS).
- **Rotas**: só `admin/cobrancas` (GET index).

**O que falta:**

- [ ] Rotas: `listar`, e, se for cadastro completo, `criar`, `editar/(:num)`, `atualizar/(:num)`.
- [ ] Controller + Model para cobranças (ou reaproveitar de financeiro/locações, conforme regra de negócio).
- [ ] JS: listagem via `fetch(.../listar)`; remover dados mock.
- [ ] Modal criar/editar cobrança (locação, competência, vencimento, valor, status, etc.), se o módulo for CRUD.
- [ ] Ações “Gerar boleto”, “Registrar pagamento” etc. conforme definição do produto.

---

### 4. Manutenções

- **View**: `admin/manutencoes/index.php`
- **Controller**: `Manutencao` — só `index()`.
- **JS**: `manutencao.js` — Grid com **data hardcoded** (array de arrays).
- **Rotas**: só `admin/manutencao` (GET index).

**O que falta:**

- [ ] Rotas: `listar`, `criar`, `editar/(:num)`, `atualizar/(:num)`.
- [ ] Controller + Model de manutenções (veículo, datas, descrição, status, etc.).
- [ ] JS: dados do grid via `fetch(.../listar)`; botões Editar/Detalhes ligados a modal e API.
- [ ] Modal criar/editar manutenção.

---

### 5. Manutenção Inteligente

- **View**: `admin/manutencao-inteligente/index.php`
- **Controller**: `ManutencaoInteligente` — só `index()`.
- **JS**: `manutencao-inteligente.js` — Grid com **data hardcoded**.

**O que falta:**

- [ ] Definir se é uma listagem “visão” (agregada de veículos + prazos + alertas) ou um CRUD próprio.
- [ ] Rotas e endpoints para alimentar o grid (ex.: `listar` com dados de alertas/previsões).
- [ ] JS: remover dados fixos e usar `fetch(...)`.
- [ ] Se houver CRUD (agendamentos, tipos, etc.), criar modais e rotas de criar/editar/atualizar.

---

### 6. Financeiro — Nova movimentação

- **View**: `admin/financeiro/movimentacoes.php`
- **Rota**: `admin/financeiro/movimentacoes` (GET). Controller `Financeiro::movimentacoes()` carrega categorias e exibe o form.
- **Comportamento atual**: Formulário “Nova movimentação” faz apenas `alert('Movimentação pronta para envio (teste de UI).')` no submit; **não envia** para o backend.

**O que falta:**

- [ ] No submit do form: enviar via POST para um endpoint de criação (ex. `admin/financeiro/criar` ou `admin/financeiro/movimentacoes/criar`).
- [ ] Ajustar `Financeiro::criar()` (ou novo método) para aceitar os campos desse form (tipo, categoria, valor, data, etc.) e gravar via `LancamentoFinanceiroModel`.
- [ ] Após sucesso: redirecionar para `admin/financeiro` ou mostrar mensagem e limpar form, sem depender de `alert`.

---

### 7. Relatórios

- **Menu**: sidebar aponta para `admin/relatorios`.
- **Rotas / Controller / View**: **não há** rota, controller nem view para `admin/relatorios` no projeto.

**O que falta:**

- [ ] Criar rota `admin/relatorios` → controller de relatórios (ex. `Relatorios::index`).
- [ ] Criar view `admin/relatorios/index.php` (painel com filtros e opções de relatório).
- [ ] Implementar relatórios desejados (ex.: locações por período, financeiro, cobranças, manutenções) com endpoints que retornem JSON/HTML/PDF conforme o tipo de tela.

---

## Resumo por prioridade sugerida

| Prioridade | Tela / Funcionalidade                     | Motivo |
|-----------|-------------------------------------------|--------|
| Alta      | Categorias Financeiras                    | Base para categorização no Financeiro e em Movimentações; modelo já existe. |
| Alta      | Financeiro — submit da Nova movimentação  | Form já existe; falta só integrar com backend. |
| Média     | Cobranças                                 | Usado no dia a dia; hoje 100% mock. |
| Média     | Contratos (Meus contratos)                | Dados mock; impacto grande na operação. |
| Média     | Manutenções                               | Cadastro básico com dados fixos. |
| Baixa     | Manutenção Inteligente                    | Definir escopo (somente leitura ou CRUD). |
| Baixa     | Relatórios                                | Tela inexistente; criar do zero. |

---

## Referência de arquivos

| Tema                    | Controller          | View (pasta)              | JS (pasta pages)           |
|-------------------------|---------------------|---------------------------|-----------------------------|
| Categorias Financeiras | CategoriasFinanceiras | categorias-financeiras/  | categorias-financeiras.js   |
| Contratos               | Contratos           | contratos/                | contratos.js, contratos-modelos.js |
| Cobranças               | Cobrancas           | cobrancas/                | cobrancas.js                |
| Manutenções             | Manutencao          | manutencoes/              | manutencao.js               |
| Manut. Inteligente      | ManutencaoInteligente | manutencao-inteligente/ | manutencao-inteligente.js   |
| Financeiro / Movim.     | Financeiro          | financeiro/movimentacoes.php | (inline na view)        |
| Relatórios              | (a criar)           | (a criar)                 | (a criar)                   |

---

*Documento gerado com base na estrutura de rotas, controllers, views e JS do projeto em janeiro/2026.*
