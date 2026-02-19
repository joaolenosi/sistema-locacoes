-- phpMyAdmin SQL Dump
-- version 4.3.7
-- http://www.phpmyadmin.net
--
-- Host: mysql11-farm15.uni5.net
-- Tempo de geração: 18/02/2026 às 22:27
-- Versão do servidor: 5.6.51-log
-- Versão do PHP: 5.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de dados: `guinesoftware45`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agenda`
--

CREATE TABLE IF NOT EXISTS `agenda` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do agendamento',
  `age_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa responsável pelo agendamento',
  `age_rec_id` bigint(20) unsigned NOT NULL COMMENT 'Recurso agendado (profissional, veículo, sala, etc)',
  `age_cli_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Cliente associado ao agendamento',
  `age_data` date NOT NULL COMMENT 'Data do agendamento',
  `age_hora_inicio` time NOT NULL COMMENT 'Hora de início do agendamento',
  `age_hora_fim` time NOT NULL COMMENT 'Hora de término do agendamento',
  `age_duracao` int(11) NOT NULL COMMENT 'Duração total do agendamento em minutos',
  `age_status` enum('agendado','confirmado','aguardando','em_atendimento','finalizado','cancelado','faltou') DEFAULT 'agendado' COMMENT 'Status atual do agendamento',
  `age_origem` enum('online','interno') DEFAULT 'interno' COMMENT 'Origem do agendamento (online ou interno)',
  `age_preferencia` tinyint(1) DEFAULT '0' COMMENT 'Indica se o cliente marcou preferência pelo recurso',
  `age_cor` varchar(20) DEFAULT NULL COMMENT 'Cor personalizada do agendamento na agenda visual',
  `age_observacoes` text COMMENT 'Observações adicionais do agendamento',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `agenda_bloqueios`
--

CREATE TABLE IF NOT EXISTS `agenda_bloqueios` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do bloqueio de agenda',
  `age_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa responsável pelo bloqueio',
  `age_rec_id` bigint(20) unsigned NOT NULL COMMENT 'Recurso bloqueado (veículo, profissional, etc)',
  `age_data` date NOT NULL COMMENT 'Data do bloqueio',
  `age_hora_inicio` time NOT NULL COMMENT 'Hora de início do bloqueio',
  `age_hora_fim` time NOT NULL COMMENT 'Hora de término do bloqueio',
  `age_motivo` varchar(255) DEFAULT NULL COMMENT 'Motivo do bloqueio (manutenção, folga, indisponibilidade)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `assinaturas`
--

CREATE TABLE IF NOT EXISTS `assinaturas` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da assinatura',
  `ass_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária da assinatura',
  `ass_plano_id` bigint(20) unsigned NOT NULL COMMENT 'Plano contratado',
  `ass_status` enum('ativa','cancelada','suspensa','expirada','trial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativa' COMMENT 'Status atual da assinatura',
  `ass_periodo` enum('mensal','anual') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Período de cobrança da assinatura',
  `ass_dia_cobranca` tinyint(3) unsigned DEFAULT NULL COMMENT 'Dia específico de cobrança mensal (1 a 31)',
  `ass_cobranca_customizada` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica se a assinatura possui cobrança proporcional/customizada',
  `ass_valor_inicial` decimal(10,2) DEFAULT NULL COMMENT 'Valor da cobrança inicial proporcional',
  `ass_dias_proporcional` smallint(5) unsigned DEFAULT NULL COMMENT 'Quantidade de dias considerados na cobrança proporcional',
  `ass_preco` decimal(10,2) NOT NULL COMMENT 'Preço final da assinatura no momento da contratação',
  `ass_data_inicio` date NOT NULL COMMENT 'Data de início da assinatura',
  `ass_data_fim` date DEFAULT NULL COMMENT 'Data final da assinatura (quando expira)',
  `ass_data_fim_trial` date DEFAULT NULL COMMENT 'Data de término do período de trial',
  `ass_data_cancelamento` datetime DEFAULT NULL COMMENT 'Data e hora em que a assinatura foi cancelada',
  `ass_stripe_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID da subscription no Stripe',
  `ass_stripe_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do customer no Stripe',
  `ass_stripe_payment_method_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do método de pagamento no Stripe',
  `ass_stripe_setup_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do Setup Intent usado para capturar o cartão',
  `ass_card_last4` char(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Últimos 4 dígitos do cartão',
  `ass_card_brand` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bandeira do cartão (Visa, MasterCard, etc)',
  `ass_asaas_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID da subscription no Asaas',
  `ass_asaas_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do customer no Asaas',
  `ass_webhook_ultimo` datetime DEFAULT NULL COMMENT 'Data do último webhook processado',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação da assinatura',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Assinaturas dos planos do sistema (SaaS multi-tenant)';

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_empresa`
--

CREATE TABLE IF NOT EXISTS `categorias_empresa` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do vínculo da categoria com a empresa',
  `cae_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) dona da configuração',
  `cae_categoria_id` bigint(20) unsigned NOT NULL COMMENT 'Categoria financeira vinculada à empresa',
  `cae_ativo` tinyint(1) DEFAULT '1' COMMENT 'Indica se a categoria está ativa para a empresa',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do vínculo',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do vínculo'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_financeiras`
--

CREATE TABLE IF NOT EXISTS `categorias_financeiras` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da categoria financeira',
  `cat_nome` varchar(100) NOT NULL COMMENT 'Nome da categoria financeira',
  `cat_tipo` enum('receita','despesa') NOT NULL COMMENT 'Tipo da categoria financeira',
  `cat_padrao` tinyint(1) DEFAULT '0' COMMENT 'Indica se a categoria é padrão do sistema',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação da categoria',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização da categoria'
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `categorias_financeiras`
--

INSERT INTO `categorias_financeiras` (`id`, `cat_nome`, `cat_tipo`, `cat_padrao`, `created_at`, `updated_at`) VALUES
(1, 'Locação de veículos', 'receita', 1, '2026-01-14 11:03:14', '2026-01-14 11:03:14'),
(2, 'Caução', 'receita', 1, '2026-01-14 11:03:14', '2026-01-14 11:03:14'),
(3, 'Multa por atraso', 'receita', 1, '2026-01-14 11:03:14', '2026-01-14 11:03:14'),
(4, 'Taxa administrativa', 'receita', 1, '2026-01-14 11:03:14', '2026-01-14 11:03:14'),
(5, 'Serviços adicionais', 'receita', 1, '2026-01-14 11:03:14', '2026-01-14 11:03:14'),
(6, 'Venda de serviços', 'receita', 1, '2026-01-14 11:03:14', '2026-01-14 11:03:14'),
(7, 'Combustível', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(8, 'Manutenção de veículos', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(9, 'Peças e acessórios', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(10, 'Seguro', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(11, 'IPVA', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(12, 'Licenciamento', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(13, 'Multas de trânsito', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(14, 'Internet', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(15, 'Aluguel', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(16, 'Energia elétrica', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(17, 'Água', 'despesa', 1, '2026-01-14 11:03:20', '2026-01-14 11:03:20'),
(18, 'Folha de pagamento', 'despesa', 1, '2026-01-14 11:03:20', '2026-02-18 15:44:15'),
(19, 'teste', 'receita', 0, '2026-02-18 15:44:22', '2026-02-18 15:44:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE IF NOT EXISTS `clientes` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do cliente',
  `cli_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa à qual o cliente pertence (tenant)',
  `cli_tipo_pessoa` enum('fisica','juridica','estrangeiro') DEFAULT 'fisica' COMMENT 'Tipo de pessoa do cliente',
  `cli_nome` varchar(150) NOT NULL COMMENT 'Nome completo ou razão social do cliente',
  `cli_cpf_cnpj` varchar(20) DEFAULT NULL COMMENT 'CPF ou CNPJ do cliente (armazenado sem formatação)',
  `cli_data_nascimento` date DEFAULT NULL COMMENT 'Data de nascimento do cliente (pessoa física)',
  `cli_email` varchar(150) DEFAULT NULL COMMENT 'E-mail principal do cliente',
  `cli_telefone` varchar(20) DEFAULT NULL COMMENT 'Telefone principal do cliente',
  `cli_whatsapp` varchar(20) DEFAULT NULL COMMENT 'Número de WhatsApp do cliente',
  `cli_cnh_numero` varchar(20) DEFAULT NULL COMMENT 'Número de registro da CNH do cliente',
  `cli_cnh_validade` date DEFAULT NULL COMMENT 'Data de vencimento da CNH do cliente',
  `cli_cep` varchar(10) DEFAULT NULL COMMENT 'CEP do endereço do cliente',
  `cli_estado` varchar(2) DEFAULT NULL COMMENT 'Estado (UF) do endereço do cliente',
  `cli_cidade` varchar(100) DEFAULT NULL COMMENT 'Cidade do endereço do cliente',
  `cli_bairro` varchar(100) DEFAULT NULL COMMENT 'Bairro do endereço do cliente',
  `cli_rua` varchar(150) DEFAULT NULL COMMENT 'Rua ou logradouro do endereço do cliente',
  `cli_numero` varchar(20) DEFAULT NULL COMMENT 'Número do endereço do cliente',
  `cli_complemento` varchar(100) DEFAULT NULL COMMENT 'Complemento do endereço do cliente',
  `cli_obs` text COMMENT 'Observações internas sobre o cliente',
  `cli_ativo` tinyint(1) DEFAULT '1' COMMENT 'Indica se o cliente está ativo no sistema',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `clientes`
--

INSERT INTO `clientes` (`id`, `cli_empresa_id`, `cli_tipo_pessoa`, `cli_nome`, `cli_cpf_cnpj`, `cli_data_nascimento`, `cli_email`, `cli_telefone`, `cli_whatsapp`, `cli_cnh_numero`, `cli_cnh_validade`, `cli_cep`, `cli_estado`, `cli_cidade`, `cli_bairro`, `cli_rua`, `cli_numero`, `cli_complemento`, `cli_obs`, `cli_ativo`, `created_at`, `updated_at`) VALUES
(1, 1, '', 'João Silva', '12345678900', '1988-05-12', 'joao.silva@email.com', '(11) 98765-4321', '(11) 98765-4321', '12345678901', '2028-05-12', '01001000', 'SP', 'São Paulo', 'Centro', 'Rua A', '100', NULL, 'Cliente pessoa física', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(2, 1, '', 'Maria Santos', '98765432100', '1990-08-22', 'maria.santos@email.com', '(21) 99876-5432', '(21) 99876-5432', '22345678901', '2027-08-22', '20040002', 'RJ', 'Rio de Janeiro', 'Centro', 'Rua B', '200', NULL, 'Cliente pessoa física', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(3, 1, '', 'Pedro Oliveira', '45678912300', '1985-03-18', 'pedro.oliveira@email.com', '(31) 98765-1234', '(31) 98765-1234', '32345678901', '2026-03-18', '30140071', 'MG', 'Belo Horizonte', 'Savassi', 'Rua C', '300', NULL, 'Cliente pessoa física', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(4, 1, '', 'Ana Costa', '78912345600', '1992-11-05', 'ana.costa@email.com', '(41) 99876-3210', '(41) 99876-3210', '42345678901', '2029-11-05', '80010000', 'PR', 'Curitiba', 'Centro', 'Rua D', '400', NULL, 'Cliente pessoa física', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(5, 1, '', 'Carlos Pereira', '32165498700', '1980-01-30', 'carlos.pereira@email.com', '(51) 98765-9876', '(51) 98765-9876', '52345678901', '2025-01-30', '90010000', 'RS', 'Porto Alegre', 'Centro Histórico', 'Rua E', '500', NULL, 'Cliente pessoa física', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(6, 1, '', 'Transportes ABC Ltda', '12345678000190', NULL, 'contato@transportesabc.com.br', '(11) 3456-7890', '(11) 3456-7890', NULL, NULL, '06010000', 'SP', 'Osasco', 'Industrial', 'Av. das Empresas', '1000', 'Galpão 2', 'Cliente pessoa jurídica', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(7, 1, '', 'Fernanda Lima', '65432198700', '1995-06-14', 'fernanda.lima@email.com', '(21) 98765-4321', '(21) 98765-4321', '72345678901', '2030-06-14', '22740010', 'RJ', 'Rio de Janeiro', 'Barra', 'Rua F', '700', NULL, 'Cliente pessoa física', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(8, 1, '', 'Roberto Alves', '14725836900', '1978-09-09', 'roberto.alves@email.com', '(31) 99876-5432', '(31) 99876-5432', '82345678901', '2024-09-09', '30130010', 'MG', 'Belo Horizonte', 'Funcionários', 'Rua G', '800', NULL, 'Cliente inativo', 0, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(9, 1, '', 'Logística XYZ EIRELI', '98765432000110', NULL, 'financeiro@logisticaxyz.com.br', '(41) 3456-7890', '(41) 3456-7890', NULL, NULL, '83010010', 'PR', 'São José dos Pinhais', 'Aeroporto', 'Av. Logística', '1500', 'Sala 10', 'Cliente pessoa jurídica', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(10, 1, '', 'Juliana Ferreira', '25836914700', '1993-04-27', 'juliana.ferreira@email.com', '(51) 98765-1234', '(51) 98765-1234', '10234567890', '2029-04-27', '90560030', 'RS', 'Porto Alegre', 'Petrópolis', 'Rua H', '900', NULL, 'Cliente pessoa física', 1, '2026-01-16 11:14:17', '2026-01-16 11:14:17'),
(11, 1, 'fisica', 'test 1234567899010', '23093901232', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-02-17 18:26:56', '2026-02-18 14:02:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos`
--

CREATE TABLE IF NOT EXISTS `contratos` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do contrato',
  `con_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do contrato',
  `con_locacao_id` bigint(20) unsigned NOT NULL COMMENT 'Locação vinculada ao contrato',
  `con_modelo_id` bigint(20) unsigned NOT NULL COMMENT 'Modelo de contrato utilizado',
  `con_numero` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número do contrato (ex: C-000001-1)',
  `con_status` enum('rascunho','gerado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho' COMMENT 'Status do documento',
  `con_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token para link público de envio ao cliente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos de contrato gerados (locação + modelo)';

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos_modelos`
--

CREATE TABLE IF NOT EXISTS `contratos_modelos` (
  `id` bigint(20) unsigned NOT NULL,
  `con_empresa_id` bigint(20) unsigned DEFAULT NULL,
  `con_nome` varchar(150) NOT NULL,
  `con_descricao` varchar(255) DEFAULT NULL,
  `con_conteudo` longtext NOT NULL,
  `con_padrao` tinyint(1) DEFAULT '0',
  `con_ativo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `contratos_modelos`
--

INSERT INTO `contratos_modelos` (`id`, `con_empresa_id`, `con_nome`, `con_descricao`, `con_conteudo`, `con_padrao`, `con_ativo`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Contrato de Locação de Veículo', 'Modelo padrão de contrato de locação de veículo automotor, com campos dinâmicos.', 'CONTRATO DE LOCAÇÃO DE VEÍCULO AUTOMOTOR\r\n\r\nPelo presente instrumento particular, de um lado {{locadora.nome_completo}}, inscrita no CPF/CNPJ sob o nº {{locadora.cpf_cnpj}}, com endereço à {{locadora.endereco}}, nº {{locadora.numero}}, {{locadora.complemento}}, bairro {{locadora.bairro}}, {{locadora.cidade}} – {{locadora.estado}}, CEP {{locadora.cep}}, doravante denominada LOCADORA;\r\n\r\ne, de outro lado, {{locatario.nome_completo}}, inscrito no CPF/CNPJ sob o nº {{locatario.cpf_cnpj}}, portador da CNH nº {{locatario.cnh_numero}}, com vencimento em {{locatario.cnh_vencimento}}, residente e domiciliado à {{locatario.endereco}}, nº {{locatario.numero}}, {{locatario.complemento}}, bairro {{locatario.bairro}}, {{locatario.cidade}} – {{locatario.estado}}, CEP {{locatario.cep}}, telefone {{locatario.telefone}}, WhatsApp {{locatario.whatsapp}}, doravante denominado LOCATÁRIO, têm entre si justo e contratado o que segue:\r\n\r\nCLÁUSULA 1ª – DO OBJETO\r\nO presente contrato tem como objeto a locação do veículo automotor abaixo descrito:\r\nMarca: {{veiculo.marca}}\r\nModelo: {{veiculo.modelo}}\r\nAno: {{veiculo.ano}}\r\nCor: {{veiculo.cor}}\r\nPlaca: {{veiculo.placa}}\r\nChassi: {{veiculo.chassi}}\r\nRenavam: {{veiculo.renavam}}\r\nTipo: {{veiculo.tipo}}\r\n\r\nCLÁUSULA 2ª – DO PRAZO\r\nA locação terá início em {{locacao.data_inicio}}, pelo período de {{locacao.tempo}}, conforme condições acordadas entre as partes.\r\n\r\nCLÁUSULA 3ª – DO VALOR E FORMA DE PAGAMENTO\r\nPela locação, o LOCATÁRIO pagará à LOCADORA o valor total de {{locacao.valor}}, conforme a recorrência de pagamento definida em {{locacao.recorrencia_pagamento}}, com início em {{locacao.inicio_pagamento}}.\r\nEm caso de atraso, incidirá multa no valor de {{locacao.taxa_multa}} e juros de {{locacao.taxa_juros}}, calculados conforme legislação vigente.\r\n\r\nCLÁUSULA 4ª – DA CAUÇÃO\r\nComo garantia do cumprimento das obrigações contratuais, o LOCATÁRIO prestará caução no valor de {{locacao.valor_caucao}}, a ser devolvida ao final da locação, desde que inexistam débitos, danos ou pendências.\r\n\r\nCLÁUSULA 5ª – DO USO E CONSERVAÇÃO DO VEÍCULO\r\nO LOCATÁRIO compromete-se a utilizar o veículo de forma responsável, zelando por sua conservação, responsabilizando-se por danos causados por mau uso, negligência ou imprudência.\r\nO veículo será entregue com quilometragem registrada de {{veiculo.km_na_retirada}}, devendo ser devolvido em condições compatíveis com o uso regular.\r\n\r\nCLÁUSULA 6ª – DAS OBRIGAÇÕES DO LOCATÁRIO\r\nSão obrigações do LOCATÁRIO:\r\na) Manter o veículo em boas condições de uso;\r\nb) Arcar com multas, infrações e danos ocorridos durante o período da locação;\r\nc) Não ceder, emprestar ou sublocar o veículo sem autorização da LOCADORA;\r\nd) Respeitar a legislação de trânsito vigente.\r\n\r\nCLÁUSULA 7ª – DA RESCISÃO\r\nO presente contrato poderá ser rescindido por qualquer das partes em caso de descumprimento de quaisquer de suas cláusulas, sem prejuízo das penalidades cabíveis e da apuração de eventuais perdas e danos. Caso ocorra rescisão antecipada, permanecem devidos os valores proporcionais ao período utilizado, bem como encargos, multas e despesas decorrentes do uso do veículo, quando aplicáveis.\r\n\r\nCLÁUSULA 8ª – DO FORO\r\nPara dirimir quaisquer controvérsias oriundas deste contrato, as partes elegem o foro da comarca de {{locadora.cidade}} – {{locadora.estado}}, renunciando a qualquer outro, por mais privilegiado que seja.\r\n\r\nE, por estarem assim justas e contratadas, firmam o presente instrumento na data de {{data_de_hoje}}.', 1, 1, '2026-01-14 16:29:02', '2026-01-14 16:29:02');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos_variaveis`
--

CREATE TABLE IF NOT EXISTS `contratos_variaveis` (
  `id` bigint(20) unsigned NOT NULL,
  `cov_chave` varchar(100) NOT NULL,
  `cov_entidade` varchar(50) NOT NULL,
  `cov_campo` varchar(50) NOT NULL,
  `cov_label` varchar(150) NOT NULL,
  `cov_descricao` varchar(255) DEFAULT NULL,
  `cov_origem_tabela` varchar(50) NOT NULL,
  `cov_origem_campo` varchar(50) NOT NULL,
  `cov_tipo` enum('texto','data','numero','valor','booleano') DEFAULT 'texto',
  `cov_ativo` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `contratos_variaveis`
--

INSERT INTO `contratos_variaveis` (`id`, `cov_chave`, `cov_entidade`, `cov_campo`, `cov_label`, `cov_descricao`, `cov_origem_tabela`, `cov_origem_campo`, `cov_tipo`, `cov_ativo`, `created_at`, `updated_at`) VALUES
(1, 'data_de_hoje', 'global', 'data_de_hoje', 'Data de Hoje', NULL, 'sistema', 'data_atual', 'data', 1, '2026-01-14 16:11:46', '2026-01-14 16:11:46'),
(2, 'locacao.data_inicio', 'locacao', 'data_inicio', 'Data de Início da Locação', NULL, 'locacoes', 'loc_data_inicio', 'data', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(3, 'locacao.inicio_pagamento', 'locacao', 'inicio_pagamento', 'Início do Pagamento', NULL, 'locacoes', 'loc_inicio_pagamento', 'data', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(4, 'locacao.dia_semana_pagamento', 'locacao', 'dia_semana_pagamento', 'Dia da Semana de Pagamento', NULL, 'locacoes', 'loc_dia_pagamento', 'texto', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(5, 'locacao.recorrencia_pagamento', 'locacao', 'recorrencia_pagamento', 'Recorrência de Pagamento', NULL, 'locacoes', 'loc_recorrencia', 'texto', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(6, 'locacao.taxa_juros', 'locacao', 'taxa_juros', 'Taxa de Juros', NULL, 'locacoes', 'loc_taxa_juros', 'valor', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(7, 'locacao.taxa_multa', 'locacao', 'taxa_multa', 'Taxa de Multa', NULL, 'locacoes', 'loc_taxa_multa', 'valor', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(8, 'locacao.tempo', 'locacao', 'tempo', 'Tempo da Locação', NULL, 'locacoes', 'loc_tempo', 'numero', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(9, 'locacao.valor', 'locacao', 'valor', 'Valor da Locação', NULL, 'locacoes', 'loc_valor_total', 'valor', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(10, 'locacao.valor_caucao', 'locacao', 'valor_caucao', 'Valor da Caução', NULL, 'locacoes', 'loc_caucao', 'valor', 1, '2026-01-14 16:11:57', '2026-01-14 16:11:57'),
(11, 'locadora.nome_completo', 'locadora', 'nome_completo', 'Nome da Locadora', NULL, 'locadoras', 'loc_nome', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(12, 'locadora.cpf_cnpj', 'locadora', 'cpf_cnpj', 'CPF/CNPJ da Locadora', NULL, 'locadoras', 'loc_cpf_cnpj', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(13, 'locadora.endereco', 'locadora', 'endereco', 'Endereço da Locadora', NULL, 'locadoras', 'loc_endereco', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(14, 'locadora.numero', 'locadora', 'numero', 'Número', NULL, 'locadoras', 'loc_numero', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(15, 'locadora.complemento', 'locadora', 'complemento', 'Complemento', NULL, 'locadoras', 'loc_complemento', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(16, 'locadora.bairro', 'locadora', 'bairro', 'Bairro', NULL, 'locadoras', 'loc_bairro', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(17, 'locadora.cidade', 'locadora', 'cidade', 'Cidade', NULL, 'locadoras', 'loc_cidade', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(18, 'locadora.estado', 'locadora', 'estado', 'Estado', NULL, 'locadoras', 'loc_estado', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(19, 'locadora.cep', 'locadora', 'cep', 'CEP', NULL, 'locadoras', 'loc_cep', 'texto', 1, '2026-01-14 16:12:05', '2026-01-14 16:12:05'),
(20, 'locatario.nome_completo', 'locatario', 'nome_completo', 'Nome do Locatário', NULL, 'locatarios', 'loc_nome', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(21, 'locatario.cpf_cnpj', 'locatario', 'cpf_cnpj', 'CPF/CNPJ do Locatário', NULL, 'locatarios', 'loc_cpf_cnpj', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(22, 'locatario.data_nascimento', 'locatario', 'data_nascimento', 'Data de Nascimento', NULL, 'locatarios', 'loc_data_nascimento', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(23, 'locatario.cnh_numero', 'locatario', 'cnh_numero', 'Número da CNH', NULL, 'locatarios', 'loc_cnh_numero', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(24, 'locatario.cnh_vencimento', 'locatario', 'cnh_vencimento', 'Vencimento da CNH', NULL, 'locatarios', 'loc_cnh_vencimento', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(25, 'locatario.email', 'locatario', 'email', 'E-mail', NULL, 'locatarios', 'loc_email', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(26, 'locatario.telefone', 'locatario', 'telefone', 'Telefone', NULL, 'locatarios', 'loc_telefone', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(27, 'locatario.whatsapp', 'locatario', 'whatsapp', 'WhatsApp', NULL, 'locatarios', 'loc_whatsapp', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(28, 'locatario.endereco', 'locatario', 'endereco', 'Endereço', NULL, 'locatarios', 'loc_endereco', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(29, 'locatario.numero', 'locatario', 'numero', 'Número', NULL, 'locatarios', 'loc_numero', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(30, 'locatario.complemento', 'locatario', 'complemento', 'Complemento', NULL, 'locatarios', 'loc_complemento', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(31, 'locatario.bairro', 'locatario', 'bairro', 'Bairro', NULL, 'locatarios', 'loc_bairro', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(32, 'locatario.cidade', 'locatario', 'cidade', 'Cidade', NULL, 'locatarios', 'loc_cidade', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(33, 'locatario.estado', 'locatario', 'estado', 'Estado', NULL, 'locatarios', 'loc_estado', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(34, 'locatario.cep', 'locatario', 'cep', 'CEP', NULL, 'locatarios', 'loc_cep', 'texto', 1, '2026-01-14 16:12:11', '2026-01-14 16:12:11'),
(35, 'veiculo.ano', 'veiculo', 'ano', 'Ano do Veículo', NULL, 'veiculos', 'vei_ano', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(36, 'veiculo.chassi', 'veiculo', 'chassi', 'Chassi do Veículo', NULL, 'veiculos', 'vei_chassi', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(37, 'veiculo.cor', 'veiculo', 'cor', 'Cor do Veículo', NULL, 'veiculos', 'vei_cor', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(38, 'veiculo.km_atual', 'veiculo', 'km_atual', 'KM Atual', NULL, 'veiculos', 'vei_km_atual', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(39, 'veiculo.km_na_retirada', 'veiculo', 'km_na_retirada', 'KM na Retirada', NULL, 'veiculos', 'vei_km_retirada', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(40, 'veiculo.marca', 'veiculo', 'marca', 'Marca do Veículo', NULL, 'veiculos', 'vei_marca', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(41, 'veiculo.modelo', 'veiculo', 'modelo', 'Modelo do Veículo', NULL, 'veiculos', 'vei_modelo', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(42, 'veiculo.placa', 'veiculo', 'placa', 'Placa do Veículo', NULL, 'veiculos', 'vei_placa', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(43, 'veiculo.renavam', 'veiculo', 'renavam', 'Renavam', NULL, 'veiculos', 'vei_renavam', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18'),
(44, 'veiculo.tipo', 'veiculo', 'tipo', 'Tipo do Veículo', NULL, 'veiculos', 'vei_tipo', 'texto', 1, '2026-01-14 16:12:18', '2026-01-14 16:12:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE IF NOT EXISTS `empresas` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da empresa (tenant do sistema)',
  `emp_nome` varchar(150) NOT NULL COMMENT 'Razão social ou nome principal da empresa',
  `emp_fantasia` varchar(150) DEFAULT NULL COMMENT 'Nome fantasia da empresa',
  `emp_cpf_cnpj` varchar(20) DEFAULT NULL COMMENT 'CPF ou CNPJ da empresa (armazenado sem formatação)',
  `emp_tipo` enum('salao','locadora','clinica','outro') NOT NULL COMMENT 'Tipo de negócio da empresa',
  `emp_telefone` varchar(20) DEFAULT NULL COMMENT 'Telefone principal da empresa (fixo ou celular)',
  `emp_email` varchar(150) DEFAULT NULL COMMENT 'E-mail principal de contato da empresa',
  `emp_cep` varchar(10) DEFAULT NULL COMMENT 'CEP do endereço da empresa',
  `emp_estado` varchar(2) DEFAULT NULL COMMENT 'Estado (UF) do endereço da empresa',
  `emp_cidade` varchar(100) DEFAULT NULL COMMENT 'Cidade do endereço da empresa',
  `emp_rua` varchar(150) DEFAULT NULL COMMENT 'Rua ou logradouro do endereço da empresa',
  `emp_numero` varchar(20) DEFAULT NULL COMMENT 'Número do endereço da empresa',
  `emp_complemento` varchar(100) DEFAULT NULL COMMENT 'Complemento do endereço (sala, bloco, etc)',
  `emp_ativo` tinyint(1) DEFAULT '1' COMMENT 'Indica se a empresa está ativa no sistema',
  `emp_obs` text COMMENT 'Observações internas sobre a empresa',
  `emp_inscricao_estadual` varchar(50) DEFAULT NULL,
  `emp_inscricao_municipal` varchar(50) DEFAULT NULL,
  `emp_site` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `empresas`
--

INSERT INTO `empresas` (`id`, `emp_nome`, `emp_fantasia`, `emp_cpf_cnpj`, `emp_tipo`, `emp_telefone`, `emp_email`, `emp_cep`, `emp_estado`, `emp_cidade`, `emp_rua`, `emp_numero`, `emp_complemento`, `emp_ativo`, `emp_obs`, `emp_inscricao_estadual`, `emp_inscricao_municipal`, `emp_site`, `senha`, `created_at`, `updated_at`) VALUES
(1, 'MOBILI LOCACOES LTDA', 'MOBILI LOCAÇÕES', '18887327000192', '', '84981359585', 'contato@mobili.com.br', '59518000', 'RN', 'São Rafael 1223', 'Avenida Tristão de Barros', '01', 'Centro', 1, 'Empresa de locação de veículos e serviços correlatos', NULL, NULL, NULL, '$2y$10$tBrcbhYhnOvm73u/PRAp7ezDBRjyHQijs2hadiX6/u4XhZQzOdb7y', '2026-01-16 11:11:08', '2026-02-18 14:58:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamentos_financeiros`
--

CREATE TABLE IF NOT EXISTS `lancamentos_financeiros` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do lançamento financeiro',
  `lan_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do lançamento financeiro',
  `lan_tipo` enum('receita','despesa') NOT NULL COMMENT 'Tipo do lançamento financeiro: receita ou despesa',
  `lan_categoria_id` bigint(20) unsigned NOT NULL COMMENT 'Categoria financeira associada ao lançamento',
  `lan_descricao` varchar(255) NOT NULL COMMENT 'Descrição detalhada do lançamento financeiro',
  `lan_data_lancamento` date NOT NULL COMMENT 'Data em que o fato financeiro ocorreu (permite lançamento retroativo)',
  `lan_data_vencimento` date NOT NULL COMMENT 'Data de vencimento do lançamento financeiro',
  `lan_data_pagamento` date DEFAULT NULL COMMENT 'Data em que o lançamento foi efetivamente pago ou recebido',
  `lan_valor` decimal(10,2) NOT NULL COMMENT 'Valor previsto do lançamento financeiro',
  `lan_valor_pago` decimal(10,2) DEFAULT NULL COMMENT 'Valor efetivamente pago ou recebido (permite pagamento parcial)',
  `lan_status` enum('pendente','pago','cancelado') DEFAULT 'pendente' COMMENT 'Status atual do lançamento financeiro',
  `lan_forma_pagamento` enum('dinheiro','pix','cartao_credito','cartao_debito','boleto','transferencia') DEFAULT NULL COMMENT 'Forma de pagamento utilizada no lançamento',
  `lan_referencia` varchar(100) DEFAULT NULL COMMENT 'Referência do pagamento (NSU, código PIX, comprovante, etc)',
  `lan_locacao_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Locação vinculada ao lançamento financeiro, se aplicável',
  `lan_veiculo_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Veículo vinculado ao lançamento financeiro, se aplicável',
  `lan_obs` text COMMENT 'Observações adicionais do lançamento financeiro',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora em que o lançamento foi criado no sistema',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do lançamento'
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `lancamentos_financeiros`
--

INSERT INTO `lancamentos_financeiros` (`id`, `lan_empresa_id`, `lan_tipo`, `lan_categoria_id`, `lan_descricao`, `lan_data_lancamento`, `lan_data_vencimento`, `lan_data_pagamento`, `lan_valor`, `lan_valor_pago`, `lan_status`, `lan_forma_pagamento`, `lan_referencia`, `lan_locacao_id`, `lan_veiculo_id`, `lan_obs`, `created_at`, `updated_at`) VALUES
(1, 1, 'receita', 1, 'Locação veículo ABC-1234 - João Silva', '2026-01-15', '2026-01-15', '2026-01-15', '1200.00', '1200.00', 'pago', 'pix', 'Locação de veículos', 1, 1, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(2, 1, 'receita', 1, 'Locação veículo XYZ-5678 - Maria Santos', '2026-01-10', '2026-01-10', '2026-01-10', '1000.00', '1000.00', 'pago', '', 'Locação de veículos', 2, 2, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(3, 1, 'receita', 1, 'Locação veículo DEF-9012 - Pedro Oliveira', '2026-01-20', '2026-01-20', '2026-01-16', '1800.00', '0.00', 'pago', NULL, 'Locação de veículos', NULL, NULL, 'Aguardando pagamento', '2026-01-16 11:37:12', '2026-01-16 20:37:41'),
(4, 1, 'despesa', 2, 'Abastecimento veículo ABC-1234', '2026-01-08', '2026-01-08', '2026-01-08', '250.00', '250.00', 'pago', 'dinheiro', 'Combustível', NULL, 1, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(5, 1, 'receita', 3, 'Caução locação veículo JKL-7890', '2026-01-12', '2026-01-12', '2026-01-12', '500.00', '500.00', 'pago', 'pix', 'Caução', 4, 5, 'Valor de garantia', '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(6, 1, 'despesa', 4, 'Revisão veículo GHI-3456', '2026-01-18', '2026-01-18', '2026-01-18', '450.00', '450.00', 'pago', 'boleto', 'Manutenção de veículos', NULL, 4, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(7, 1, 'receita', 1, 'Locação veículo PQR-1357 - Fernanda Lima', '2026-01-22', '2026-01-22', NULL, '2200.00', '0.00', 'pendente', NULL, 'Locação de veículos', 5, 7, 'Pagamento em aberto', '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(8, 1, 'despesa', 5, 'Troca de pneus veículo MNO-2468', '2026-01-05', '2026-01-05', '2026-01-05', '1200.00', '1200.00', 'pago', '', 'Peças e acessórios', NULL, 6, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(9, 1, 'receita', 6, 'Taxa administrativa contrato CT-2026-009', '2026-01-25', '2026-01-25', '2026-01-25', '150.00', '150.00', 'pago', 'pix', 'Taxa administrativa', NULL, NULL, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(10, 1, 'despesa', 7, 'Seguro veículo STU-8024', '2026-01-14', '2026-01-14', '2026-01-14', '380.00', '380.00', 'pago', 'boleto', 'Seguro', NULL, 8, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(11, 1, 'receita', 8, 'Lavagem completa veículo YZA-2468', '2026-01-16', '2026-01-16', '2026-01-16', '80.00', '80.00', 'pago', 'dinheiro', 'Serviços adicionais', NULL, 10, NULL, '2026-01-16 11:37:12', '2026-01-16 11:37:12'),
(12, 1, 'despesa', 9, 'Conta de energia - Janeiro/2026', '2026-01-19', '2026-01-25', NULL, '320.00', '0.00', 'pendente', NULL, 'Energia elétrica', NULL, NULL, 'Despesa fixa mensal', '2026-01-16 11:37:12', '2026-01-16 11:37:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `locacoes`
--

CREATE TABLE IF NOT EXISTS `locacoes` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da locação',
  `loc_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa proprietária da locação (tenant)',
  `loc_cli_id` bigint(20) unsigned NOT NULL COMMENT 'Cliente (locatário) associado à locação',
  `loc_vei_id` bigint(20) unsigned NOT NULL COMMENT 'Veículo associado à locação',
  `loc_data_inicio` date NOT NULL COMMENT 'Data de início da locação',
  `loc_data_fim_prevista` date NOT NULL COMMENT 'Data prevista para devolução do veículo',
  `loc_data_fim_real` date DEFAULT NULL COMMENT 'Data real da devolução do veículo',
  `loc_status` enum('reservada','ativa','atrasada','finalizada','cancelada','inadimplente') DEFAULT 'reservada' COMMENT 'Status atual da locação',
  `loc_valor_locacao` decimal(10,2) NOT NULL COMMENT 'Valor base da locação',
  `loc_valor_caucao` decimal(10,2) DEFAULT NULL COMMENT 'Valor da caução da locação',
  `loc_valor_total` decimal(10,2) DEFAULT NULL COMMENT 'Valor total final da locação (locação + encargos)',
  `loc_recorrencia_pagamento` enum('diaria','semanal','quinzenal','mensal') DEFAULT NULL COMMENT 'Recorrência de cobrança da locação',
  `loc_data_inicio_pagamento` date DEFAULT NULL COMMENT 'Data de início da cobrança da locação',
  `loc_taxa_juros` decimal(10,2) DEFAULT NULL COMMENT 'Valor da taxa de juros por atraso',
  `loc_taxa_multa` decimal(10,2) DEFAULT NULL COMMENT 'Valor da multa por atraso',
  `loc_km_retirada` int(11) DEFAULT NULL COMMENT 'Quilometragem do veículo no momento da retirada',
  `loc_km_devolucao` int(11) DEFAULT NULL COMMENT 'Quilometragem do veículo no momento da devolução',
  `loc_responsavel_entrega` varchar(150) DEFAULT NULL COMMENT 'Nome do responsável pela entrega do veículo',
  `loc_responsavel_devolucao` varchar(150) DEFAULT NULL COMMENT 'Nome do responsável pela devolução do veículo',
  `loc_obs_operacionais` text COMMENT 'Observações operacionais sobre o estado do veículo',
  `loc_obs_financeiras` text COMMENT 'Observações financeiras da locação',
  `loc_valores_recebidos` tinyint(1) DEFAULT '0' COMMENT 'Indica se os valores da locação já foram recebidos',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação da locação',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização da locação'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `locacoes`
--

INSERT INTO `locacoes` (`id`, `loc_empresa_id`, `loc_cli_id`, `loc_vei_id`, `loc_data_inicio`, `loc_data_fim_prevista`, `loc_data_fim_real`, `loc_status`, `loc_valor_locacao`, `loc_valor_caucao`, `loc_valor_total`, `loc_recorrencia_pagamento`, `loc_data_inicio_pagamento`, `loc_taxa_juros`, `loc_taxa_multa`, `loc_km_retirada`, `loc_km_devolucao`, `loc_responsavel_entrega`, `loc_responsavel_devolucao`, `loc_obs_operacionais`, `loc_obs_financeiras`, `loc_valores_recebidos`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 10, '2026-02-18', '2026-02-20', NULL, 'reservada', '200.00', '100.00', '200.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-18 14:33:47', '2026-02-18 14:33:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `locacoes_pagamentos`
--

CREATE TABLE IF NOT EXISTS `locacoes_pagamentos` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do pagamento da locação',
  `lop_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa proprietária do pagamento (tenant)',
  `lop_loc_id` bigint(20) unsigned NOT NULL COMMENT 'Locação associada ao pagamento',
  `lop_forma_pagamento` enum('dinheiro','pix','cartao_credito','cartao_debito','boleto','transferencia') NOT NULL COMMENT 'Forma de pagamento utilizada',
  `lop_valor` decimal(10,2) NOT NULL COMMENT 'Valor pago nesta forma de pagamento',
  `lop_data_pagamento` date NOT NULL COMMENT 'Data em que o pagamento foi realizado',
  `lop_referencia` varchar(100) DEFAULT NULL COMMENT 'Referência do pagamento (NSU, TXID, comprovante, etc)',
  `lop_status` enum('pendente','pago','estornado','cancelado') DEFAULT 'pago' COMMENT 'Status do pagamento',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do pagamento',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do pagamento'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencao_controles_log`
--

CREATE TABLE IF NOT EXISTS `manutencao_controles_log` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do log de atualização de controle',
  `mcl_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do log',
  `mcl_manutencao_id` bigint(20) unsigned NOT NULL COMMENT 'Manutenção que gerou a atualização do controle',
  `mcl_veiculo_controle_id` bigint(20) unsigned NOT NULL COMMENT 'Controle do veículo que foi atualizado',
  `mcl_km_anterior` int(11) DEFAULT NULL COMMENT 'KM anterior registrado no controle antes da atualização',
  `mcl_km_atual` int(11) NOT NULL COMMENT 'KM usado na manutenção para atualizar o controle',
  `mcl_intervalo_km` int(11) NOT NULL COMMENT 'Intervalo em KM aplicado nesta atualização (snapshot)',
  `mcl_proximo_km` int(11) DEFAULT NULL COMMENT 'Próximo KM calculado após a atualização',
  `mcl_acao` enum('ativado','atualizado','desativado') DEFAULT 'atualizado' COMMENT 'Ação executada no controle (ativado/atualizado/desativado)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do log',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do log'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencao_financeiro`
--

CREATE TABLE IF NOT EXISTS `manutencao_financeiro` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do vínculo manutenção x financeiro',
  `maf_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do vínculo',
  `maf_manutencao_id` bigint(20) unsigned NOT NULL COMMENT 'Manutenção relacionada ao lançamento financeiro',
  `maf_lancamento_id` bigint(20) unsigned NOT NULL COMMENT 'Lançamento financeiro relacionado (lancamentos_financeiros.id)',
  `maf_tipo` enum('despesa','receita') NOT NULL COMMENT 'Tipo do lançamento no contexto da manutenção',
  `maf_valor` decimal(10,2) NOT NULL COMMENT 'Valor vinculado da manutenção a este lançamento (snapshot)',
  `maf_obs` varchar(255) DEFAULT NULL COMMENT 'Observação do vínculo (ex: itens pagos pelo locatário)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do vínculo',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do vínculo'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencoes`
--

CREATE TABLE IF NOT EXISTS `manutencoes` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da manutenção',
  `man_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária da manutenção',
  `man_veiculo_id` bigint(20) unsigned NOT NULL COMMENT 'Veículo relacionado à manutenção',
  `man_locacao_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Locação relacionada à manutenção (quando originada de uma locação)',
  `man_data` date NOT NULL COMMENT 'Data da manutenção',
  `man_km` int(11) DEFAULT NULL COMMENT 'KM do veículo no momento da manutenção',
  `man_km_atual` int(11) DEFAULT NULL COMMENT 'KM atual do veículo no momento do agendamento da manutenção',
  `man_trigger_tipo` enum('data','km','qualquer') DEFAULT 'qualquer' COMMENT 'Tipo de trigger: data (só por data), km (só por KM), qualquer (qualquer um que atingir primeiro)',
  `man_tipo` enum('preventiva','corretiva') DEFAULT 'corretiva' COMMENT 'Tipo principal da manutenção (pode ser definido pelo contexto)',
  `man_status` enum('rascunho','aberta','finalizada','cancelada') DEFAULT 'aberta' COMMENT 'Status do fluxo da manutenção',
  `man_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Valor total calculado da manutenção (soma dos itens)',
  `man_pago` tinyint(1) DEFAULT '0' COMMENT 'Indica se a manutenção foi marcada como paga',
  `man_data_pagamento` date DEFAULT NULL COMMENT 'Data do pagamento da manutenção (se paga)',
  `man_valor_pago` decimal(10,2) DEFAULT NULL COMMENT 'Valor efetivamente pago (permite pagamento parcial)',
  `man_forma_pagamento` enum('dinheiro','pix','cartao_credito','cartao_debito','boleto','transferencia') DEFAULT NULL COMMENT 'Forma de pagamento do custo da manutenção (se controlado no sistema)',
  `man_obs` text COMMENT 'Observações gerais da manutenção (diagnóstico, detalhes, notas)',
  `man_lancamento_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID do lançamento financeiro vinculado (integração com lancamentos_financeiros)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `manutencoes`
--

INSERT INTO `manutencoes` (`id`, `man_empresa_id`, `man_veiculo_id`, `man_locacao_id`, `man_data`, `man_km`, `man_km_atual`, `man_trigger_tipo`, `man_tipo`, `man_status`, `man_total`, `man_pago`, `man_data_pagamento`, `man_valor_pago`, `man_forma_pagamento`, `man_obs`, `man_lancamento_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, '2026-01-10', 35000, NULL, 'qualquer', 'preventiva', 'finalizada', '370.00', 1, '2026-01-12', '370.00', 'pix', 'Revisão + troca de óleo', 6, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(2, 1, 2, NULL, '2026-01-18', 52000, NULL, 'qualquer', 'corretiva', '', '179.00', 0, NULL, '0.00', NULL, 'Troca de pastilhas', NULL, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(3, 1, 3, NULL, '2026-01-22', 18000, NULL, 'qualquer', 'corretiva', 'finalizada', '1520.00', 1, '2026-01-25', '1520.00', '', 'Pneus e alinhamento', 8, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(4, 1, 4, NULL, '2026-01-28', 74000, NULL, 'qualquer', '', '', '560.00', 0, NULL, '0.00', NULL, 'Bateria e sistema elétrico', NULL, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(5, 1, 5, NULL, '2026-01-05', 89000, NULL, 'qualquer', 'preventiva', 'finalizada', '450.00', 1, '2026-01-07', '450.00', 'dinheiro', 'Revisão geral', 6, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(6, 1, 6, NULL, '2026-01-15', 61000, NULL, 'qualquer', 'corretiva', 'finalizada', '620.00', 1, '2026-01-17', '620.00', 'boleto', 'Troca de correia dentada', 6, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(7, 1, 7, NULL, '2026-01-30', 42000, NULL, 'qualquer', 'preventiva', '', '250.00', 0, NULL, '0.00', NULL, 'Suspensão', NULL, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(8, 1, 8, NULL, '2026-01-08', 15000, NULL, 'qualquer', 'preventiva', 'finalizada', '210.00', 1, '2026-01-10', '210.00', 'pix', 'Troca de filtros', 6, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(9, 1, 9, NULL, '2026-01-25', 80000, NULL, 'qualquer', 'corretiva', '', '300.00', 0, NULL, '0.00', NULL, 'Ar condicionado', NULL, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(10, 1, 10, NULL, '2026-01-12', 57000, NULL, 'qualquer', 'preventiva', 'finalizada', '340.00', 1, '2026-01-14', '340.00', '', 'Revisão e balanceamento', 6, '2026-01-16 12:03:28', '2026-01-16 12:03:28'),
(11, 1, 11, NULL, '2026-02-18', 0, NULL, 'qualquer', 'preventiva', 'finalizada', '0.00', 0, NULL, NULL, NULL, 'O que aconteceu primeiro.', NULL, '2026-02-18 18:52:50', '2026-02-18 20:15:04'),
(12, 1, 11, NULL, '2026-02-18', 60000, NULL, 'qualquer', 'preventiva', 'finalizada', '0.00', 0, NULL, NULL, NULL, 'asd', NULL, '2026-02-18 20:19:41', '2026-02-18 23:03:49'),
(13, 1, 11, NULL, '2026-02-19', 9999, 58868, 'qualquer', 'preventiva', 'finalizada', '0.00', 0, NULL, NULL, NULL, NULL, NULL, '2026-02-19 00:20:23', '2026-02-19 00:20:36'),
(14, 1, 11, NULL, '2027-02-19', 19999, 9999, 'qualquer', 'preventiva', 'aberta', '0.00', 0, NULL, NULL, NULL, 'Agendada automaticamente ao completar manutenção anterior.', NULL, '2026-02-19 00:20:36', '2026-02-19 00:20:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencoes_itens`
--

CREATE TABLE IF NOT EXISTS `manutencoes_itens` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do item da manutenção',
  `mai_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do item',
  `mai_manutencao_id` bigint(20) unsigned NOT NULL COMMENT 'Manutenção à qual este item pertence',
  `mai_tipo_item` enum('produto','servico') NOT NULL COMMENT 'Tipo do item: produto (peça) ou serviço',
  `mai_produto_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Produto/peça vinculada quando mai_tipo_item=produto',
  `mai_servico_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Serviço vinculado quando mai_tipo_item=servico',
  `mai_classificacao` enum('preventiva','corretiva') DEFAULT NULL COMMENT 'Classificação do item (preventiva/corretiva); útil principalmente para peças',
  `mai_descricao` varchar(255) DEFAULT NULL COMMENT 'Descrição livre do item (fallback caso não use cadastro)',
  `mai_quantidade` int(11) NOT NULL DEFAULT '1' COMMENT 'Quantidade do item',
  `mai_valor_unitario` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor unitário do item no momento da manutenção',
  `mai_valor_total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor total do item (quantidade x valor unitário)',
  `mai_pago_pelo_locatario` tinyint(1) DEFAULT '0' COMMENT 'Indica se este item será pago pelo locatário (repasse/lançamento)',
  `mai_obs` text COMMENT 'Observações do item (detalhes, garantia, etc)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `manutencoes_itens`
--

INSERT INTO `manutencoes_itens` (`id`, `mai_empresa_id`, `mai_manutencao_id`, `mai_tipo_item`, `mai_produto_id`, `mai_servico_id`, `mai_classificacao`, `mai_descricao`, `mai_quantidade`, `mai_valor_unitario`, `mai_valor_total`, `mai_pago_pelo_locatario`, `mai_obs`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'servico', NULL, 5, '', 'Troca de óleo', 1, '120.00', '120.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(2, 1, 1, 'produto', 1, NULL, '', 'Filtro de óleo', 1, '45.90', '45.90', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(3, 1, 1, 'servico', NULL, 4, '', 'Revisão preventiva', 1, '250.00', '250.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(4, 1, 2, 'produto', 2, NULL, '', 'Pastilhas de freio', 1, '89.50', '89.50', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(5, 1, 2, 'servico', NULL, 6, '', 'Instalação das pastilhas', 1, '89.50', '89.50', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(6, 1, 3, 'produto', 3, NULL, '', 'Pneu aro 15', 4, '320.00', '1280.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(7, 1, 3, 'servico', NULL, 6, '', 'Alinhamento e balanceamento', 1, '240.00', '240.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(8, 1, 4, 'produto', 4, NULL, '', 'Bateria 60Ah', 1, '280.00', '280.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(9, 1, 4, 'servico', NULL, 4, '', 'Instalação elétrica', 1, '280.00', '280.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(10, 1, 5, 'servico', NULL, 4, '', 'Revisão completa', 1, '450.00', '450.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(11, 1, 6, 'servico', NULL, 4, '', 'Troca de correia dentada', 1, '620.00', '620.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(12, 1, 7, 'servico', NULL, 4, '', 'Suspensão', 1, '250.00', '250.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(13, 1, 8, 'produto', 1, NULL, '', 'Filtro de óleo', 2, '45.90', '91.80', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(14, 1, 8, 'servico', NULL, 5, '', 'Troca de filtros', 1, '118.20', '118.20', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35'),
(15, 1, 10, 'servico', NULL, 6, '', 'Balanceamento', 1, '340.00', '340.00', 0, NULL, '2026-01-16 12:03:35', '2026-01-16 12:03:35');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE IF NOT EXISTS `notificacoes` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da notificação',
  `not_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) que receberá a notificação',
  `not_usuario_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Usuário específico da empresa (NULL = todos da empresa)',
  `not_titulo` varchar(150) NOT NULL COMMENT 'Título da notificação',
  `not_mensagem` text NOT NULL COMMENT 'Conteúdo da notificação',
  `not_tipo` enum('sistema','financeiro','manutencao','locacao','contrato','plano') NOT NULL DEFAULT 'sistema' COMMENT 'Tipo da notificação para categorização',
  `not_lida` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica se a notificação já foi lida',
  `not_acao_url` varchar(255) DEFAULT NULL COMMENT 'URL para ação ao clicar na notificação',
  `not_prioridade` enum('baixa','media','alta') NOT NULL DEFAULT 'media' COMMENT 'Prioridade da notificação',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação da notificação',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Notificações operacionais do sistema por empresa e usuário';

-- --------------------------------------------------------

--
-- Estrutura para tabela `novidades`
--

CREATE TABLE IF NOT EXISTS `novidades` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da novidade',
  `nov_versao` varchar(20) NOT NULL COMMENT 'Versão do sistema (ex: 1.14)',
  `nov_titulo` varchar(200) NOT NULL COMMENT 'Título da novidade ou atualização',
  `nov_descricao` longtext NOT NULL COMMENT 'Conteúdo detalhado da atualização (HTML ou Markdown)',
  `nov_data_publicacao` date NOT NULL COMMENT 'Data oficial de publicação da novidade',
  `nov_tipo` enum('feature','melhoria','correcao','aviso') NOT NULL DEFAULT 'feature' COMMENT 'Tipo da novidade',
  `nov_status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo' COMMENT 'Status da novidade (ativa para exibição ou não)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do registro',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tabela de novidades e changelog do sistema';

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos`
--

CREATE TABLE IF NOT EXISTS `planos` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do plano',
  `pla_nome` varchar(50) NOT NULL COMMENT 'Nome do plano (Start, Pro, Enterprise)',
  `pla_slug` varchar(50) NOT NULL COMMENT 'Identificador técnico do plano (start, pro, enterprise)',
  `pla_descricao` text COMMENT 'Descrição resumida do plano exibida ao usuário',
  `pla_preco_mensal` decimal(10,2) NOT NULL COMMENT 'Valor do plano na cobrança mensal',
  `pla_preco_anual` decimal(10,2) NOT NULL COMMENT 'Valor do plano na cobrança anual',
  `pla_desconto_anual_percentual` decimal(5,2) DEFAULT NULL COMMENT 'Percentual de desconto aplicado no plano anual',
  `pla_limite_veiculos` int(11) DEFAULT NULL COMMENT 'Quantidade máxima de veículos permitidos (NULL = ilimitado)',
  `pla_limite_locatarios` int(11) DEFAULT NULL COMMENT 'Quantidade máxima de clientes/locatários (NULL = ilimitado)',
  `pla_limite_locacoes` int(11) DEFAULT NULL COMMENT 'Quantidade máxima de locações (NULL = ilimitado)',
  `pla_suporte_tipo` enum('email','whatsapp','prioritario') NOT NULL DEFAULT 'email' COMMENT 'Tipo de suporte oferecido no plano',
  `pla_backup_diario` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica se o plano possui backup automático diário',
  `pla_relatorios_avancados` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica se o plano permite relatórios avançados/personalizados',
  `pla_acesso_antecipado` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica acesso antecipado a novas funcionalidades',
  `pla_status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo' COMMENT 'Status do plano',
  `pla_ordem` int(11) NOT NULL DEFAULT '1' COMMENT 'Ordem de exibição dos planos na tela',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do plano',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização do plano'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Tabela de planos de assinatura do sistema';

--
-- Fazendo dump de dados para tabela `planos`
--

INSERT INTO `planos` (`id`, `pla_nome`, `pla_slug`, `pla_descricao`, `pla_preco_mensal`, `pla_preco_anual`, `pla_desconto_anual_percentual`, `pla_limite_veiculos`, `pla_limite_locatarios`, `pla_limite_locacoes`, `pla_suporte_tipo`, `pla_backup_diario`, `pla_relatorios_avancados`, `pla_acesso_antecipado`, `pla_status`, `pla_ordem`, `created_at`, `updated_at`) VALUES
(1, 'Pulse', 'pulse', 'Plano ideal para pequenas operações', '40.49', '340.13', '30.00', 5, 50, 100, 'email', 0, 0, 0, 'ativo', 1, '2026-01-14 16:53:26', '2026-01-14 16:53:39'),
(2, 'Flow', 'flow', 'Plano mais completo para negócios em crescimento', '64.79', '544.25', '30.00', 25, NULL, NULL, 'whatsapp', 1, 0, 0, 'ativo', 2, '2026-01-14 16:53:26', '2026-01-14 16:53:39'),
(3, 'Orbit', 'orbit', 'Plano avançado para grandes operações', '89.99', '755.93', '30.00', NULL, NULL, NULL, 'prioritario', 1, 1, 1, 'ativo', 3, '2026-01-14 16:53:26', '2026-01-14 16:53:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE IF NOT EXISTS `produtos` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do produto/peça',
  `pro_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do produto',
  `pro_nome` varchar(150) NOT NULL COMMENT 'Nome do produto/peça (ex: lona de freio)',
  `pro_categoria` varchar(100) DEFAULT NULL COMMENT 'Categoria do produto/peça (ex: freios, motor, suspensão)',
  `pro_marca` varchar(100) DEFAULT NULL COMMENT 'Marca do produto/peça (ex: Vipal)',
  `pro_obs` text COMMENT 'Observações internas do produto/peça',
  `pro_sku` varchar(60) DEFAULT NULL COMMENT 'Código interno/SKU do produto (opcional)',
  `pro_preco_custo` decimal(10,2) DEFAULT NULL COMMENT 'Preço de custo do produto (para cálculo de margem)',
  `pro_preco_venda` decimal(10,2) DEFAULT NULL COMMENT 'Preço de venda sugerido do produto (para manutenção)',
  `pro_controlado` tinyint(1) DEFAULT '0' COMMENT 'Indica se o produto é controlado por intervalo de KM (manutenção preventiva)',
  `pro_intervalo_km` int(11) DEFAULT NULL COMMENT 'Intervalo em KM para recomendação quando controlado (ex: 20000)',
  `pro_estoque_atual` int(11) DEFAULT NULL COMMENT 'Quantidade atual em estoque (se quiser controlar estoque)',
  `pro_estoque_minimo` int(11) DEFAULT NULL COMMENT 'Estoque mínimo recomendado para alertas',
  `pro_ativo` tinyint(1) DEFAULT '1' COMMENT 'Indica se o produto está ativo para uso',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `produtos`
--

INSERT INTO `produtos` (`id`, `pro_empresa_id`, `pro_nome`, `pro_categoria`, `pro_marca`, `pro_obs`, `pro_sku`, `pro_preco_custo`, `pro_preco_venda`, `pro_controlado`, `pro_intervalo_km`, `pro_estoque_atual`, `pro_estoque_minimo`, `pro_ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Filtro de Óleo Motor', 'Peças', 'Bosch', 'Filtro compatível com motores 1.0 a 2.0', 'FIL-001', '30.00', '45.90', 1, 5000, 25, 10, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(2, 1, 'Pastilhas de Freio Dianteiras', 'Peças', 'Cobreq', 'Jogo com 4 pastilhas', 'PAS-002', '60.00', '89.50', 1, 20000, 15, 8, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(3, 1, 'Pneu Aro 15 185/65', 'Peças', 'Pirelli', 'Pneu radial para veículos passeio', 'PNE-003', '250.00', '320.00', 1, 40000, 8, 12, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(4, 1, 'Bateria 60Ah', 'Peças', 'Moura', 'Bateria automotiva com 18 meses de garantia', 'BAT-004', '210.00', '280.00', 1, 30000, 12, 10, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(5, 1, 'Capa para Bancos Universal', 'Acessórios', 'AutoFlex', 'Compatível com diversos modelos', 'CAP-005', '55.00', '85.00', 0, NULL, 30, 15, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(6, 1, 'Tapete Automotivo Completo', 'Acessórios', 'AutoStyle', 'Tapetes emborrachados', 'TAP-006', '80.00', '120.00', 0, NULL, 18, 10, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(7, 1, 'Suporte para Celular', 'Acessórios', 'Multilaser', 'Fixação no painel', 'SUP-007', '20.00', '35.90', 0, NULL, 45, 20, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(8, 1, 'Shampoo Automotivo 5L', 'Limpeza', 'Vonixx', 'Alto rendimento', 'SHA-008', '28.00', '42.50', 0, NULL, 22, 15, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(9, 1, 'Cera Líquida Automotiva', 'Limpeza', '3M', 'Proteção e brilho intenso', 'CER-009', '22.00', '38.00', 0, NULL, 5, 10, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(10, 1, 'Desengraxante 1L', 'Limpeza', 'Proauto', 'Uso profissional', 'DES-010', '18.00', '28.90', 0, NULL, 35, 20, 1, '2026-01-16 11:32:50', '2026-01-16 11:32:50'),
(11, 1, 'teste', NULL, NULL, NULL, '23423434', NULL, '99.99', 0, NULL, 10, 4, 1, '2026-02-18 15:23:12', '2026-02-18 15:23:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE IF NOT EXISTS `servicos` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do serviço',
  `ser_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do serviço',
  `ser_nome` varchar(150) NOT NULL COMMENT 'Nome do serviço (ex: alinhamento, revisão, pintura)',
  `ser_categoria` varchar(100) DEFAULT NULL COMMENT 'Categoria do serviço (ex: suspensão, revisão, alinhamento)',
  `ser_descricao` text COMMENT 'Descrição detalhada do serviço',
  `ser_obs` text COMMENT 'Observações internas do serviço',
  `ser_preco_padrao` decimal(10,2) DEFAULT NULL COMMENT 'Preço padrão sugerido para o serviço',
  `ser_controlado` tinyint(1) DEFAULT '0' COMMENT 'Indica se o serviço é controlado por intervalo de KM (manutenção inteligente)',
  `ser_intervalo_km` int(11) DEFAULT NULL COMMENT 'Intervalo em KM para recomendação do serviço quando controlado (ex: 10000)',
  `ser_ativo` tinyint(1) DEFAULT '1' COMMENT 'Indica se o serviço está ativo para uso',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `servicos`
--

INSERT INTO `servicos` (`id`, `ser_empresa_id`, `ser_nome`, `ser_categoria`, `ser_descricao`, `ser_obs`, `ser_preco_padrao`, `ser_controlado`, `ser_intervalo_km`, `ser_ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Lavagem Completa', 'Lavagem', 'Lavagem externa e interna do veículo', NULL, '80.00', 0, NULL, 1, '2026-01-16 11:16:47', '2026-01-16 20:37:05'),
(2, 1, 'Lavagem Simples', 'Lavagem', 'Lavagem externa do veículo', NULL, '35.00', 0, NULL, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(3, 1, 'Enceramento', 'Estética', 'Aplicação de cera protetora na pintura', 'Recomendado a cada 6 meses', '150.00', 0, NULL, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(4, 1, 'Revisão Preventiva', 'Manutenção', 'Revisão geral de itens mecânicos e de segurança', 'Checklist completo', '250.00', 1, 10000, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(5, 1, 'Troca de Óleo', 'Manutenção', 'Troca de óleo do motor e filtro', 'Óleo padrão 5W30', '120.00', 1, 5000, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(6, 1, 'Alinhamento e Balanceamento', 'Manutenção', 'Correção de alinhamento e balanceamento das rodas', NULL, '90.00', 1, 10000, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(7, 1, 'Higienização Interna', 'Estética', 'Limpeza profunda de estofados e interior', 'Uso de produtos bactericidas', '200.00', 0, NULL, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(8, 1, 'Polimento Completo', 'Estética', 'Polimento técnico da pintura automotiva', 'Remove micro riscos', '300.00', 0, NULL, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(9, 1, 'Limpeza de Motor', 'Manutenção', 'Limpeza técnica do compartimento do motor', 'Sem uso de água sob pressão', '100.00', 0, NULL, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(10, 1, 'Lavagem Premium', 'Lavagem', 'Lavagem completa com acabamento especial', 'Inclui cera líquida', '120.00', 0, NULL, 1, '2026-01-16 11:16:47', '2026-01-16 11:16:47'),
(11, 1, 'teste', 'teste', 'fsdfds', NULL, '233.33', 0, NULL, 0, '2026-02-18 15:21:59', '2026-02-18 15:22:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos`
--

CREATE TABLE IF NOT EXISTS `veiculos` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do veículo',
  `vei_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa proprietária do veículo',
  `vei_tipo` varchar(50) NOT NULL COMMENT 'Tipo do veículo (carro, moto, caminhão, etc)',
  `vei_marca` varchar(100) NOT NULL COMMENT 'Marca do veículo',
  `vei_modelo` varchar(150) NOT NULL COMMENT 'Modelo do veículo',
  `vei_ano` varchar(20) NOT NULL COMMENT 'Ano e/ou combustível do veículo (ex: 2009 Gasolina)',
  `vei_placa` varchar(10) NOT NULL COMMENT 'Placa do veículo',
  `vei_cor` varchar(50) DEFAULT NULL COMMENT 'Cor do veículo',
  `vei_renavam` varchar(20) DEFAULT NULL COMMENT 'Número do RENAVAM do veículo',
  `vei_chassi` varchar(30) DEFAULT NULL COMMENT 'Número do chassi do veículo',
  `vei_data_licenciamento` date DEFAULT NULL COMMENT 'Data do último licenciamento do veículo',
  `vei_km_atual` int(11) DEFAULT NULL COMMENT 'Quilometragem atual do veículo',
  `vei_data_compra` date DEFAULT NULL COMMENT 'Data de compra do veículo',
  `vei_valor_compra` decimal(10,2) DEFAULT NULL COMMENT 'Valor pago na compra do veículo',
  `vei_status` enum('disponivel','locado','manutencao','inativo') DEFAULT 'disponivel' COMMENT 'Status atual do veículo no sistema',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `veiculos`
--

INSERT INTO `veiculos` (`id`, `vei_empresa_id`, `vei_tipo`, `vei_marca`, `vei_modelo`, `vei_ano`, `vei_placa`, `vei_cor`, `vei_renavam`, `vei_chassi`, `vei_data_licenciamento`, `vei_km_atual`, `vei_data_compra`, `vei_valor_compra`, `vei_status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Carro', 'Chevrolet', 'Onix', '2022', 'ABC-1234', 'Branco', '12345678901', '9BGKS48U0NG123456', '2024-03-10', 35000, '2022-02-15', '68000.00', 'disponivel', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(2, 1, 'Carro', 'Hyundai', 'HB20', '2021', 'XYZ-5678', 'Prata', '22345678901', '95PZN18A0MG234567', '2024-01-20', 52000, '2021-06-10', '62000.00', '', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(3, 1, 'Carro', 'Toyota', 'Corolla', '2023', 'DEF-9012', 'Preto', '32345678901', '9BRBD48E0PG345678', '2024-04-05', 18000, '2023-01-25', '135000.00', 'disponivel', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(4, 1, 'Carro', 'Honda', 'Civic', '2020', 'GHI-3456', 'Vermelho', '42345678901', '93HFC26J0LG456789', '2023-12-18', 74000, '2020-05-20', '110000.00', 'manutencao', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(5, 1, 'Carro', 'Ford', 'Fiesta', '2019', 'JKL-7890', 'Azul', '52345678901', '9BFZP52P0KG567890', '2023-11-30', 89000, '2019-08-14', '55000.00', 'disponivel', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(6, 1, 'Carro', 'Volkswagen', 'Gol', '2021', 'MNO-2468', 'Branco', '62345678901', '9BWAG45U1MT678901', '2024-02-08', 61000, '2021-04-02', '58000.00', '', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(7, 1, 'SUV', 'Jeep', 'Compass', '2022', 'PQR-1357', 'Prata', '72345678901', '988675H4NNK789012', '2024-03-22', 42000, '2022-03-18', '145000.00', 'disponivel', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(8, 1, 'SUV', 'Volkswagen', 'T-Cross', '2023', 'STU-8024', 'Preto', '82345678901', '9BWDB45Z5PT890123', '2024-05-01', 15000, '2023-02-10', '132000.00', 'disponivel', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(9, 1, 'carro', 'Jeep', 'Renegade', '2020', 'VWX-4680', 'Laranja', '92345678901', '988611H4LLK901234', '2023-10-15', 80000, '2020-07-05', '98000.00', 'manutencao', '2026-01-16 11:06:39', '2026-01-16 15:23:51'),
(10, 1, 'SUV', 'Hyundai', 'Creta', '2021', 'YZA-2468', 'Branco', '10345678901', '95PJ381A0MG012345', '2024-01-12', 57000, '2021-09-01', '115000.00', '', '2026-01-16 11:06:39', '2026-01-16 11:06:39'),
(11, 1, 'carro', 'HYUNDAI', 'HB20 1.0M COMFOR111', '2016', 'QGI-1750', 'Branca', NULL, '9BHBG51CAGP523220', '2026-01-16', NULL, NULL, NULL, 'disponivel', '2026-01-16 15:24:38', '2026-02-18 13:40:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculo_controles`
--

CREATE TABLE IF NOT EXISTS `veiculo_controles` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único do controle de manutenção por veículo',
  `vec_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do controle',
  `vec_veiculo_id` bigint(20) unsigned NOT NULL COMMENT 'Veículo que terá o item controlado',
  `vec_tipo_item` enum('produto','servico') NOT NULL COMMENT 'Tipo do item controlado: produto (peça) ou serviço',
  `vec_produto_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Produto/peça controlado quando vec_tipo_item=produto',
  `vec_servico_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Serviço controlado quando vec_tipo_item=servico',
  `vec_intervalo_km` int(11) NOT NULL COMMENT 'Intervalo em KM para este controle (pode sobrescrever o padrão do item)',
  `vec_status` enum('ativo','nao_ativo') DEFAULT 'nao_ativo' COMMENT 'Status do controle: ativo ou nao_ativo (cinza até primeira vinculação em manutenção)',
  `vec_ultimo_km` int(11) DEFAULT NULL COMMENT 'Último KM registrado para este controle (inicia após primeira manutenção vinculada)',
  `vec_proximo_km` int(11) DEFAULT NULL COMMENT 'Próximo KM previsto para manutenção deste item (calculado: ultimo_km + intervalo)',
  `vec_ultima_manutencao_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Última manutenção que atualizou este controle',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_age_empresa` (`age_empresa_id`) COMMENT 'Índice para filtragem de agendamentos por empresa', ADD KEY `idx_age_rec` (`age_rec_id`) COMMENT 'Índice para filtragem de agendamentos por recurso', ADD KEY `idx_age_data` (`age_data`) COMMENT 'Índice para busca rápida de agendamentos por data';

--
-- Índices de tabela `agenda_bloqueios`
--
ALTER TABLE `agenda_bloqueios`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_bloq_empresa` (`age_empresa_id`) COMMENT 'Índice para busca de bloqueios por empresa', ADD KEY `idx_bloq_rec` (`age_rec_id`) COMMENT 'Índice para busca de bloqueios por recurso', ADD KEY `idx_bloq_data` (`age_data`) COMMENT 'Índice para filtragem de bloqueios por data';

--
-- Índices de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_ass_empresa` (`ass_empresa_id`), ADD KEY `idx_ass_plano` (`ass_plano_id`), ADD KEY `idx_ass_status` (`ass_status`), ADD KEY `idx_ass_stripe` (`ass_stripe_subscription_id`(191)), ADD KEY `idx_ass_asaas` (`ass_asaas_subscription_id`(191));

--
-- Índices de tabela `categorias_empresa`
--
ALTER TABLE `categorias_empresa`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uk_empresa_categoria` (`cae_empresa_id`,`cae_categoria_id`) COMMENT 'Impede duplicidade da categoria para a mesma empresa';

--
-- Índices de tabela `categorias_financeiras`
--
ALTER TABLE `categorias_financeiras`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_cli_empresa` (`cli_empresa_id`) COMMENT 'Índice para busca de clientes por empresa', ADD KEY `idx_cli_cpf_cnpj` (`cli_cpf_cnpj`) COMMENT 'Índice para busca de clientes por CPF ou CNPJ';

--
-- Índices de tabela `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_con_empresa_id` (`con_empresa_id`), ADD KEY `idx_con_locacao_id` (`con_locacao_id`), ADD KEY `idx_con_status` (`con_status`), ADD KEY `idx_con_numero` (`con_numero`);

--
-- Índices de tabela `contratos_modelos`
--
ALTER TABLE `contratos_modelos`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_empresa` (`con_empresa_id`);

--
-- Índices de tabela `contratos_variaveis`
--
ALTER TABLE `contratos_variaveis`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uk_cov_chave` (`cov_chave`), ADD KEY `idx_entidade` (`cov_entidade`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uk_emp_cpf_cnpj` (`emp_cpf_cnpj`) COMMENT 'Garante que o CPF/CNPJ não seja duplicado no sistema';

--
-- Índices de tabela `lancamentos_financeiros`
--
ALTER TABLE `lancamentos_financeiros`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_lan_empresa` (`lan_empresa_id`) COMMENT 'Índice para filtragem de lançamentos por empresa', ADD KEY `idx_lan_tipo` (`lan_tipo`) COMMENT 'Índice para filtragem por tipo de lançamento', ADD KEY `idx_lan_status` (`lan_status`) COMMENT 'Índice para filtragem por status do lançamento', ADD KEY `idx_lan_lancamento` (`lan_data_lancamento`) COMMENT 'Índice para consultas por data do fato financeiro', ADD KEY `idx_lan_vencimento` (`lan_data_vencimento`) COMMENT 'Índice para consultas por data de vencimento', ADD KEY `idx_lan_categoria` (`lan_categoria_id`) COMMENT 'Índice para consultas por categoria financeira';

--
-- Índices de tabela `locacoes`
--
ALTER TABLE `locacoes`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_loc_empresa` (`loc_empresa_id`) COMMENT 'Índice para busca de locações por empresa', ADD KEY `idx_loc_cliente` (`loc_cli_id`) COMMENT 'Índice para busca de locações por cliente', ADD KEY `idx_loc_veiculo` (`loc_vei_id`) COMMENT 'Índice para busca de locações por veículo', ADD KEY `idx_loc_status` (`loc_status`) COMMENT 'Índice para filtragem por status da locação';

--
-- Índices de tabela `locacoes_pagamentos`
--
ALTER TABLE `locacoes_pagamentos`
  ADD PRIMARY KEY (`id`), ADD KEY `idx_lop_empresa` (`lop_empresa_id`) COMMENT 'Índice para busca de pagamentos por empresa', ADD KEY `idx_lop_locacao` (`lop_loc_id`) COMMENT 'Índice para busca de pagamentos por locação';

--
-- Índices de tabela `manutencao_controles_log`
--
ALTER TABLE `manutencao_controles_log`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `manutencao_financeiro`
--
ALTER TABLE `manutencao_financeiro`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `manutencoes_itens`
--
ALTER TABLE `manutencoes_itens`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `novidades`
--
ALTER TABLE `novidades`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `pla_slug` (`pla_slug`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `veiculos`
--
ALTER TABLE `veiculos`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uk_vei_placa` (`vei_placa`) COMMENT 'Garante que não existam veículos com a mesma placa', ADD KEY `idx_vei_empresa` (`vei_empresa_id`) COMMENT 'Índice para busca rápida de veículos por empresa', ADD KEY `idx_vei_status` (`vei_status`) COMMENT 'Índice para filtragem por status do veículo';

--
-- Índices de tabela `veiculo_controles`
--
ALTER TABLE `veiculo_controles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do agendamento';
--
-- AUTO_INCREMENT de tabela `agenda_bloqueios`
--
ALTER TABLE `agenda_bloqueios`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do bloqueio de agenda';
--
-- AUTO_INCREMENT de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da assinatura';
--
-- AUTO_INCREMENT de tabela `categorias_empresa`
--
ALTER TABLE `categorias_empresa`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do vínculo da categoria com a empresa';
--
-- AUTO_INCREMENT de tabela `categorias_financeiras`
--
ALTER TABLE `categorias_financeiras`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da categoria financeira',AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do cliente',AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT de tabela `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do contrato';
--
-- AUTO_INCREMENT de tabela `contratos_modelos`
--
ALTER TABLE `contratos_modelos`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de tabela `contratos_variaveis`
--
ALTER TABLE `contratos_variaveis`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da empresa (tenant do sistema)',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de tabela `lancamentos_financeiros`
--
ALTER TABLE `lancamentos_financeiros`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do lançamento financeiro',AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT de tabela `locacoes`
--
ALTER TABLE `locacoes`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da locação',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de tabela `locacoes_pagamentos`
--
ALTER TABLE `locacoes_pagamentos`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do pagamento da locação';
--
-- AUTO_INCREMENT de tabela `manutencao_controles_log`
--
ALTER TABLE `manutencao_controles_log`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do log de atualização de controle';
--
-- AUTO_INCREMENT de tabela `manutencao_financeiro`
--
ALTER TABLE `manutencao_financeiro`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do vínculo manutenção x financeiro';
--
-- AUTO_INCREMENT de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da manutenção',AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT de tabela `manutencoes_itens`
--
ALTER TABLE `manutencoes_itens`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do item da manutenção',AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da notificação';
--
-- AUTO_INCREMENT de tabela `novidades`
--
ALTER TABLE `novidades`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da novidade';
--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do plano',AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do produto/peça',AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do serviço',AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT de tabela `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do veículo',AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT de tabela `veiculo_controles`
--
ALTER TABLE `veiculo_controles`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do controle de manutenção por veículo';
--
-- Restrições para dumps de tabelas
--

--
-- Restrições para tabelas `assinaturas`
--
ALTER TABLE `assinaturas`
ADD CONSTRAINT `fk_ass_empresa` FOREIGN KEY (`ass_empresa_id`) REFERENCES `empresas` (`id`),
ADD CONSTRAINT `fk_ass_plano` FOREIGN KEY (`ass_plano_id`) REFERENCES `planos` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
