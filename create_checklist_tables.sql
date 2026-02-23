-- Módulo Checklist de Veículos
-- Execute este script no banco de dados do sistema

-- Configuração do checklist por empresa (imagem do veículo)
CREATE TABLE IF NOT EXISTS `checklist_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cfc_empresa_id` bigint(20) unsigned NOT NULL,
  `cfc_imagem_caminho` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cfc_empresa` (`cfc_empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Itens do checklist (configuráveis por empresa)
CREATE TABLE IF NOT EXISTS `checklist_itens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chi_empresa_id` bigint(20) unsigned NOT NULL,
  `chi_nome` varchar(255) NOT NULL,
  `chi_ordem` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_chi_empresa` (`chi_empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Checklists (registros de preenchimento)
CREATE TABLE IF NOT EXISTS `checklists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chk_empresa_id` bigint(20) unsigned NOT NULL,
  `chk_locacao_id` bigint(20) unsigned DEFAULT NULL,
  `chk_veiculo_id` bigint(20) unsigned DEFAULT NULL,
  `chk_data` date NOT NULL,
  `chk_hodometro_saida` int(11) DEFAULT NULL,
  `chk_hodometro_chegada` int(11) DEFAULT NULL,
  `chk_data_saida` date DEFAULT NULL,
  `chk_data_chegada` date DEFAULT NULL,
  `chk_responsavel_entrega` varchar(150) DEFAULT NULL,
  `chk_responsavel_devolucao` varchar(150) DEFAULT NULL,
  `chk_imagem_desenho_caminho` varchar(500) DEFAULT NULL,
  `chk_anotacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_chk_empresa` (`chk_empresa_id`),
  KEY `idx_chk_locacao` (`chk_locacao_id`),
  KEY `idx_chk_veiculo` (`chk_veiculo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marcações OK/NÃO por item por checklist
CREATE TABLE IF NOT EXISTS `checklist_marcacoes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chm_empresa_id` bigint(20) unsigned NOT NULL,
  `chm_checklist_id` bigint(20) unsigned NOT NULL,
  `chm_item_id` bigint(20) unsigned NOT NULL,
  `chm_valor` enum('ok','nao') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chm_checklist_item` (`chm_checklist_id`,`chm_item_id`),
  KEY `idx_chm_empresa` (`chm_empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anexos do checklist
CREATE TABLE IF NOT EXISTS `checklist_anexos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cha_empresa_id` bigint(20) unsigned NOT NULL,
  `cha_checklist_id` bigint(20) unsigned NOT NULL,
  `cha_nome_arquivo` varchar(255) NOT NULL,
  `cha_caminho` varchar(500) NOT NULL,
  `cha_tamanho` int(11) DEFAULT NULL,
  `cha_tipo` varchar(50) DEFAULT NULL,
  `cha_ordem` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cha_checklist` (`cha_checklist_id`),
  KEY `idx_cha_empresa` (`cha_empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
