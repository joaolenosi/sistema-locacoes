-- --------------------------------------------------------
--
-- Estrutura para tabela `financeiro` (Cobranças/Sistema de Assinatura)
--

CREATE TABLE IF NOT EXISTS `financeiro` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Identificador único da cobrança',
  `fin_emp_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária da cobrança',
  `fin_descricao` varchar(255) NOT NULL COMMENT 'Descrição da cobrança (ex: Mensalidade do Sistema)',
  `fin_valor` decimal(10,2) NOT NULL COMMENT 'Valor da cobrança',
  `fin_data_vencimento` date NOT NULL COMMENT 'Data de vencimento da cobrança',
  `fin_data_pagamento` date DEFAULT NULL COMMENT 'Data em que o pagamento foi efetuado',
  `fin_status` enum('pendente','pago','cancelado','vencido') NOT NULL DEFAULT 'pendente' COMMENT 'Status atual da cobrança',
  `fin_codigo_pix` varchar(500) DEFAULT NULL COMMENT 'Código PIX para pagamento (Copia e Cola)',
  `fin_url_qrcode` varchar(500) DEFAULT NULL COMMENT 'URL ou caminho do QR Code PIX',
  `fin_forma_pagamento` enum('pix','boleto','cartao','transferencia') DEFAULT 'pix' COMMENT 'Forma de pagamento utilizada',
  `fin_referencia` varchar(100) DEFAULT NULL COMMENT 'Referência do pagamento (código, NSU, etc)',
  `fin_mes_referencia` varchar(7) DEFAULT NULL COMMENT 'Mês de referência no formato YYYY-MM',
  `fin_obs` text COMMENT 'Observações adicionais sobre a cobrança',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização do registro'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Cobranças mensais do sistema (assinaturas)';

-- --------------------------------------------------------
--
-- Índices para tabela `financeiro`
--

ALTER TABLE `financeiro`
  ADD KEY `idx_financeiro_emp_id` (`fin_emp_id`),
  ADD KEY `idx_financeiro_status` (`fin_status`),
  ADD KEY `idx_financeiro_mes_referencia` (`fin_mes_referencia`),
  ADD KEY `idx_financeiro_vencimento` (`fin_data_vencimento`);

-- --------------------------------------------------------
--
-- Adicionar campos na tabela empresas (se não existirem)
--

SET @dbname = DATABASE();
SET @tablename = 'empresas';
SET @columnname = 'emp_dt_assinatura';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE empresas ADD COLUMN emp_dt_assinatura date DEFAULT NULL COMMENT \"Data da assinatura do sistema\" AFTER emp_complemento'
));
PREPARE alterIfNotExists1 FROM @preparedStatement;
EXECUTE alterIfNotExists1;
DEALLOCATE PREPARE alterIfNotExists1;

SET @columnname = 'emp_plano_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE empresas ADD COLUMN emp_plano_id bigint(20) unsigned DEFAULT NULL COMMENT \"Plano contratado pela empresa\" AFTER emp_dt_assinatura'
));
PREPARE alterIfNotExists2 FROM @preparedStatement;
EXECUTE alterIfNotExists2;
DEALLOCATE PREPARE alterIfNotExists2;
