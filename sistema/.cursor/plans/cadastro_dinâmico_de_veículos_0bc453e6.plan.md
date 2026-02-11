---
name: Cadastro Dinâmico de Veículos
overview: Implementar cadastro e edição dinâmica de veículos com modal, integração ao banco de dados, atualização do GridJS em tempo real e ordenação por data de criação (mais recente primeiro).
todos: []
---

# Implementação do Cadastro Dinâmico de Veículos

## Objetivos

- Criar modal de cadastro/edição de veículos
- Conectar com banco de dados MySQL (tabela `veiculos`)
- Atualizar GridJS após cadastro/edição
- Exibir último registro cadastrado primeiro
- Adicionar botão de editar na listagem

## Estrutura da Tabela `veiculos`

Campos principais:

- `id`, `vei_empresa_id` (fixo: 1), `vei_tipo`, `vei_marca`, `vei_modelo`, `vei_ano`
- `vei_placa`, `vei_cor`, `vei_renavam`, `vei_chassi`
- `vei_data_licenciamento`, `vei_km_atual`, `vei_data_compra`, `vei_valor_compra`
- `vei_status` (enum: 'disponivel', 'locado', 'manutencao', 'inativo')

## Arquivos a Modificar/Criar

### 1. Model: `app/Models/VeiculoModel.php` (CRIAR)

- Model CodeIgniter 4 para tabela `veiculos`
- Configurar `$table`, `$primaryKey`, `$allowedFields`, `$useTimestamps`
- Campos permitidos: todos os campos `vei_*` exceto `id` e timestamps

### 2. Controller: `app/Controllers/Veiculos.php` (MODIFICAR)

- **`index()`**: Buscar veículos do banco ordenados por `created_at DESC`, calcular cards (total, livres, ocupados), passar dados para view
- **`listar()`** (novo): Endpoint GET `/admin/veiculos/listar` retorna JSON com todos os veículos
- **`criar()`** (novo): Endpoint POST `/admin/veiculos/criar` recebe dados, valida, salva no banco, retorna JSON
- **`editar($id)`** (novo): Endpoint GET `/admin/veiculos/editar/(:num)` retorna JSON de um veículo
- **`atualizar($id)`** (novo): Endpoint POST `/admin/veiculos/atualizar/(:num)` recebe dados, valida, atualiza, retorna JSON
- Usar `vei_empresa_id = 1` (fixo por enquanto)
- Tratar erros e retornar respostas JSON adequadas

### 3. Rotas: `app/Config/Routes.php` (MODIFICAR)

Adicionar rotas:

```php
$routes->get('admin/veiculos/listar', 'Veiculos::listar');
$routes->post('admin/veiculos/criar', 'Veiculos::criar');
$routes->get('admin/veiculos/editar/(:num)', 'Veiculos::editar');
$routes->post('admin/veiculos/atualizar/(:num)', 'Veiculos::atualizar');
```

### 4. View: `app/Views/admin/veiculos/index.php` (MODIFICAR)

- Adicionar modal Bootstrap com ID `#modalVeiculo`
- Campos do modal (baseados na tabela):
  - Tipo (select): Carro, Moto, Caminhão, etc.
  - Marca (input text)
  - Modelo (input text)
  - Ano (input text)
  - Placa (input text com máscara)
  - Cor (input text)
  - Renavam (input text)
  - Chassi (input text)
  - Data Licenciamento (input date)
  - KM Atual (input number)
  - Data Compra (input date, opcional)
  - Valor Compra (input text com máscara monetária, opcional)
  - Status (select): Disponível, Locado, Manutenção, Inativo
- Botões: "Cancelar" e "Salvar"
- Campo hidden `veiculo_id` para edição
- Atualizar botão "Adicionar Veículo" para abrir modal

### 5. JavaScript: `public/assets/admin/js/pages/veiculos.js` (MODIFICAR COMPLETAMENTE)

- Remover dados estáticos
- Buscar dados via AJAX do endpoint `/admin/veiculos/listar`
- Inicializar GridJS com dados dinâmicos
- Adicionar coluna "Ações" com botão "Editar" (ícone `iconamoon:edit-duotone`)
- Função `abrirModalVeiculo(veiculoId = null)`:
  - Se `veiculoId`: buscar dados via AJAX e preencher modal
  - Se `null`: limpar modal para novo cadastro
- Função `salvarVeiculo()`:
  - Validar formulário
  - Enviar via AJAX (POST `/admin/veiculos/criar` ou `/admin/veiculos/atualizar/:id`)
  - Em sucesso: fechar modal, recarregar GridJS, mostrar mensagem
  - Em erro: exibir mensagem de erro
- Função `recarregarGrid()`: Buscar dados atualizados e atualizar GridJS
- Aplicar máscaras: placa (ABC-1A23), renavam, chassi, valor monetário
- Event listeners:
  - Botão "Adicionar Veículo" → `abrirModalVeiculo(null)`
  - Botão "Editar" na tabela → `abrirModalVeiculo(id)`
  - Submit do formulário → `salvarVeiculo()`

### 6. Máscaras de Input

Usar jQuery Mask Plugin (já disponível no projeto):

- Placa: `AAA-0A00` ou `AAA-0000`
- Renavam: números apenas
- Chassi: alfanumérico
- Valor: máscara monetária (já implementada em outros formulários)

## Fluxo de Dados

```
1. Usuário clica "Adicionar Veículo"
   → Modal abre vazio
   
2. Usuário preenche e clica "Salvar"
   → AJAX POST /admin/veiculos/criar
   → Controller valida e salva no banco
   → Retorna JSON com sucesso
   → JavaScript recarrega GridJS
   → Novo veículo aparece primeiro na lista
   
3. Usuário clica "Editar" em um veículo
   → AJAX GET /admin/veiculos/editar/:id
   → Controller busca veículo do banco
   → Retorna JSON
   → JavaScript preenche modal
   → Usuário edita e salva
   → AJAX POST /admin/veiculos/atualizar/:id
   → GridJS atualizado
```

## Validações

- Campos obrigatórios: tipo, marca, modelo, ano, placa, status
- Placa: formato válido (AAA-0A00 ou AAA-0000)
- Renavam: apenas números (se preenchido)
- Chassi: alfanumérico (se preenchido)
- KM: número inteiro positivo (se preenchido)
- Valor: decimal positivo (se preenchido)

## Ordenação

- Listagem sempre ordenada por `created_at DESC` (mais recente primeiro)
- Após cadastro, novo registro aparece automaticamente no topo

## Tratamento de Erros

- Controller retorna JSON: `{success: false, message: 'Erro...'}`
- JavaScript exibe mensagem de erro (alert ou toast)
- Em caso de erro de conexão, manter fallback para dados estáticos (opcional)