-- Intervalos por dia da semana (tela separada de Feriados). Uma linha por intervalo.
-- dia_semana_inicio / dia_semana_fim: abreviaturas seg, ter, qua, qui, sex, sab, dom
-- Execute no banco da aplicação.

DROP TABLE IF EXISTS `motel_feriado_intervalo`;
DROP TABLE IF EXISTS `motel_feriado_grupo`;

CREATE TABLE IF NOT EXISTS `motel_intervalo_dia_semana` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_motel` BIGINT NOT NULL,
	`nome` VARCHAR(50) NOT NULL DEFAULT '',
	`dia_semana_inicio` VARCHAR(3) NOT NULL COMMENT 'seg,ter,qua,qui,sex,sab,dom',
	`hora_inicio` TIME NOT NULL,
	`dia_semana_fim` VARCHAR(3) NOT NULL COMMENT 'seg,ter,qua,qui,sex,sab,dom',
	`hora_fim` TIME NOT NULL,
	`date_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`date_update` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_motel_intervalo_dia_semana_motel` (`id_motel`),
	KEY `idx_motel_intervalo_dia_semana_ordem` (`id_motel`, `dia_semana_inicio`, `hora_inicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Se a tabela já existia com TINYINT numérico, altere as colunas e migre os dados manualmente, por exemplo:
-- UPDATE motel_intervalo_dia_semana SET dia_semana_inicio = 'seg' WHERE dia_semana_inicio IN ('1', 1);
-- (repita para ter..dom conforme o valor antigo)
-- ALTER TABLE motel_intervalo_dia_semana MODIFY `dia_semana_inicio` VARCHAR(3) NOT NULL;
-- ALTER TABLE motel_intervalo_dia_semana MODIFY `dia_semana_fim` VARCHAR(3) NOT NULL;
