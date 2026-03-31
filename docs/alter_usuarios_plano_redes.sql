-- Flag opcional para habilitar Planos de Redes Sociais no motel.
-- Execute no banco da aplicação antes de usar o filtro do relatório.

ALTER TABLE `usuarios`
  ADD COLUMN `plano_redes` TINYINT(1) NULL DEFAULT 0;

