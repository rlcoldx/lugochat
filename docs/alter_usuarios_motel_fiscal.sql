-- Campos fiscais opcionais do motel (cadastro/edição admin e configurações).
-- Execute no banco da aplicação antes de usar o formulário.

ALTER TABLE `usuarios`
  ADD COLUMN `cnpj` VARCHAR(18) NULL DEFAULT NULL,
  ADD COLUMN `razao_social` VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN `inscr_estadual` VARCHAR(50) NULL DEFAULT NULL;
