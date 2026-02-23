-- =============================================================================
-- CHECKLIST DE VEÍCULOS - Tabelas e itens padrão (baseado no PDF cheklist_veiculos.pdf)
-- Execute este script no banco de dados. Ajuste @empresa_id se necessário (padrão: 1).
-- =============================================================================

SET @empresa_id = 1;

-- -----------------------------------------------------------------------------
-- TABELAS
-- -----------------------------------------------------------------------------

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

-- -----------------------------------------------------------------------------
-- INSERTS: Itens do checklist (ordem conforme PDF Checklist de Veículos)
-- Documentos, Inspeção Visual, Equipamentos
-- -----------------------------------------------------------------------------

INSERT INTO `checklist_itens` (`chi_empresa_id`, `chi_nome`, `chi_ordem`) VALUES
(@empresa_id, 'Documentos', 1),
(@empresa_id, 'Cartão Seguro', 2),
(@empresa_id, 'Retrovisor Interno', 3),
(@empresa_id, 'Retrovisor E/D', 4),
(@empresa_id, 'Vidro Dianteiro', 5),
(@empresa_id, 'Vidro Traseiro', 6),
(@empresa_id, 'Vidro Lateral', 7),
(@empresa_id, 'Placa', 8),
(@empresa_id, 'Modelo', 9),
(@empresa_id, 'Coordenação', 10),
(@empresa_id, 'Vidro Elétrico', 11),
(@empresa_id, 'Capota', 12),
(@empresa_id, 'Farol Esquerdo', 13),
(@empresa_id, 'Farol Direito', 14),
(@empresa_id, 'Nível Líquido Arrefecimento', 15),
(@empresa_id, 'Nível Óleo Motor', 16),
(@empresa_id, 'Nível Óleo Hidráulica', 17),
(@empresa_id, 'Nível Limpador Parabrisa', 18),
(@empresa_id, 'Verif. Bateria', 19),
(@empresa_id, 'Rádio', 20),
(@empresa_id, 'Nível Fluído de Freio', 21),
(@empresa_id, 'Limp. de Parabrisa', 22),
(@empresa_id, 'Pisca Esquerdo', 23),
(@empresa_id, 'Pisca Direito', 24),
(@empresa_id, 'Lanterna Esq.', 25),
(@empresa_id, 'Luz Freio', 26),
(@empresa_id, 'Lanterna Dir.', 27),
(@empresa_id, 'Luz Placa', 28),
(@empresa_id, 'Buzina', 29),
(@empresa_id, 'Funcionamento Ar Frio', 30),
(@empresa_id, 'Forro das Portas', 31),
(@empresa_id, 'Estofamento Bancos', 32),
(@empresa_id, 'Extintor', 33),
(@empresa_id, 'Triângulo', 34),
(@empresa_id, 'Manual', 35),
(@empresa_id, 'Macaco', 36),
(@empresa_id, 'Chave de Roda', 37),
(@empresa_id, 'Estepe', 38),
(@empresa_id, 'Chave Reserva', 39),
(@empresa_id, 'Tapetes', 40),
(@empresa_id, 'Cinto Segurança', 41);

-- -----------------------------------------------------------------------------
-- (Opcional) Inserir registro de configuração para a empresa usar imagem padrão
-- Descomente e ajuste @empresa_id se quiser criar a linha em checklist_config.
-- -----------------------------------------------------------------------------
-- INSERT IGNORE INTO `checklist_config` (`cfc_empresa_id`, `cfc_imagem_caminho`) VALUES (@empresa_id, NULL);
