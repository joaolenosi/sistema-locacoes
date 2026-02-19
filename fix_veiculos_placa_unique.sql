-- Script para corrigir a constraint única da placa de veículos
-- Permite que a mesma placa exista em empresas diferentes (multi-tenant)

-- 1. Remover a constraint única atual que impede duplicidade global da placa
ALTER TABLE `veiculos` DROP INDEX `uk_vei_placa`;

-- 2. Criar uma nova constraint única composta (empresa_id + placa)
-- Isso permite que a mesma placa exista em empresas diferentes,
-- mas não permite duplicidade dentro da mesma empresa
ALTER TABLE `veiculos` 
ADD UNIQUE KEY `uk_vei_empresa_placa` (`vei_empresa_id`, `vei_placa`) 
COMMENT 'Garante que não existam veículos com a mesma placa na mesma empresa';
