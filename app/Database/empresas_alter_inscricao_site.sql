-- Execute este SQL no banco se a tabela empresas ainda não tiver as colunas:
-- emp_inscricao_estadual, emp_inscricao_municipal, emp_site
-- (O campo senha já existe no seu empresas.sql)

ALTER TABLE `empresas`
  ADD COLUMN `emp_inscricao_estadual` VARCHAR(50) NULL DEFAULT NULL AFTER `emp_obs`,
  ADD COLUMN `emp_inscricao_municipal` VARCHAR(50) NULL DEFAULT NULL AFTER `emp_inscricao_estadual`,
  ADD COLUMN `emp_site` VARCHAR(255) NULL DEFAULT NULL AFTER `emp_inscricao_municipal`;
