-- =============================================================================
-- Checklist: adicionar coluna chk_tipo (Check-in / Check-out)
-- Execute no banco de dados do sistema.
-- =============================================================================

-- Coluna para indicar se o checklist é de chegada (check-in) ou saída (check-out)
ALTER TABLE `checklists`
  ADD COLUMN `chk_tipo` ENUM('checkin','checkout') NOT NULL DEFAULT 'checkout'
  COMMENT 'checkin = chegada, checkout = saída'
  AFTER `chk_data`;

-- (Opcional) Atualizar registros existentes que já tenham a coluna
-- UPDATE `checklists` SET `chk_tipo` = 'checkout' WHERE `chk_tipo` IS NULL;
