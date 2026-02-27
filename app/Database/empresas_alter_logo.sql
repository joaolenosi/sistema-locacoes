-- Execute este SQL no banco se a tabela empresas ainda não tiver a coluna:
-- emp_logo (caminho relativo da imagem de logo/foto da empresa).

ALTER TABLE `empresas`
  ADD COLUMN `emp_logo` VARCHAR(255) NULL DEFAULT NULL AFTER `emp_fantasia`;

