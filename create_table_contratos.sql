-- Tabela de documentos de contrato (cada contrato gerado a partir de uma locação + modelo)
-- Execute este script no banco de dados antes de usar o fluxo Novo Contrato / PDF.

CREATE TABLE IF NOT EXISTS `contratos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do contrato',
  `con_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária do contrato',
  `con_locacao_id` bigint(20) unsigned NOT NULL COMMENT 'Locação vinculada ao contrato',
  `con_modelo_id` bigint(20) unsigned NOT NULL COMMENT 'Modelo de contrato utilizado',
  `con_numero` varchar(50) NOT NULL COMMENT 'Número do contrato (ex: C-000001-1)',
  `con_status` enum('rascunho','gerado') NOT NULL DEFAULT 'rascunho' COMMENT 'Status do documento',
  `con_token` varchar(64) DEFAULT NULL COMMENT 'Token para link público de envio ao cliente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_con_empresa_id` (`con_empresa_id`),
  KEY `idx_con_locacao_id` (`con_locacao_id`),
  KEY `idx_con_status` (`con_status`),
  KEY `idx_con_numero` (`con_numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos de contrato gerados (locação + modelo)';
