-- Intervalos por dia da semana (tela separada de Feriados). Uma linha por intervalo.
-- Execute no banco da aplicação.
-- Se você criou as tabelas antigas de feriado em duas tabelas, remova-as antes:

DROP TABLE IF EXISTS `motel_feriado_intervalo`;
DROP TABLE IF EXISTS `motel_feriado_grupo`;

CREATE TABLE IF NOT EXISTS `motel_intervalo_dia_semana` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_motel` BIGINT NOT NULL,
	`nome` VARCHAR(50) NOT NULL DEFAULT '',
	`dia_semana_inicio` TINYINT UNSIGNED NOT NULL COMMENT '1=Segunda … 7=Domingo (ISO 8601)',
	`hora_inicio` TIME NOT NULL,
	`dia_semana_fim` TINYINT UNSIGNED NOT NULL COMMENT '1=Segunda … 7=Domingo (ISO 8601)',
	`hora_fim` TIME NOT NULL,
	`date_create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`date_update` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_motel_intervalo_dia_semana_motel` (`id_motel`),
	KEY `idx_motel_intervalo_dia_semana_ordem` (`id_motel`, `dia_semana_inicio`, `hora_inicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
