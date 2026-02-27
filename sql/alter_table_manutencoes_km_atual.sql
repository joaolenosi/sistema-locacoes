-- Script para adicionar campos man_km_atual e man_trigger_tipo na tabela manutencoes
-- Execute este script no seu banco de dados MySQL

ALTER TABLE `manutencoes`
ADD COLUMN `man_km_atual` int(11) DEFAULT NULL COMMENT 'KM atual do veículo no momento do agendamento da manutenção' AFTER `man_km`,
ADD COLUMN `man_trigger_tipo` enum('data','km','qualquer') DEFAULT 'qualquer' COMMENT 'Tipo de trigger: data (só por data), km (só por KM), qualquer (qualquer um que atingir primeiro)' AFTER `man_km_atual`;
