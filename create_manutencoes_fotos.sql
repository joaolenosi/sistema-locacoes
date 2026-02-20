-- Tabela para fotos anexadas às manutenções
-- Execute este script no banco de dados do sistema

CREATE TABLE IF NOT EXISTS `manutencoes_fotos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da foto',
  `maf_empresa_id` bigint(20) unsigned NOT NULL COMMENT 'Empresa (tenant) proprietária da foto',
  `maf_manutencao_id` bigint(20) unsigned NOT NULL COMMENT 'Manutenção à qual a foto pertence',
  `maf_nome_arquivo` varchar(255) NOT NULL COMMENT 'Nome original ou gerado do arquivo',
  `maf_caminho` varchar(500) NOT NULL COMMENT 'Caminho relativo do arquivo no servidor',
  `maf_tamanho` int(11) DEFAULT NULL COMMENT 'Tamanho do arquivo em bytes',
  `maf_tipo` varchar(50) DEFAULT NULL COMMENT 'MIME type do arquivo',
  `maf_ordem` int(11) DEFAULT 0 COMMENT 'Ordem de exibição',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_manutencao` (`maf_manutencao_id`),
  KEY `idx_empresa` (`maf_empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fotos anexadas às manutenções';
