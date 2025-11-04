-- Tabela para armazenar tokens de API para integração com sistemas externos
CREATE TABLE IF NOT EXISTS `api_motel` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_motel` INT(11) NULL DEFAULT NULL,
	`sistema` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`token` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`acessos` BIGINT(255) NULL DEFAULT '0',
	`date_create` TIMESTAMP NULL DEFAULT current_timestamp(),
	`date_update` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `idx_id_motel` (`id_motel`) USING BTREE,
	INDEX `idx_token` (`token`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;

-- Comentários sobre os campos:
-- id: Identificador único do token
-- id_motel: ID do motel associado ao token (FK para tabela usuarios)
-- sistema: Nome/descrição do sistema que utiliza este token
-- token: Token de autenticação (hash único de 64 caracteres)
-- acessos: Contador de quantas vezes a API foi acessada com este token
-- date_create: Data de criação do token
-- date_update: Data da última atualização do token

