-- Banco de dados do Chat de Áudio
-- Este arquivo é executado automaticamente pelo audioConexao.php na primeira
-- vez que o site é aberto. Também pode ser importado manualmente no phpMyAdmin.

CREATE DATABASE IF NOT EXISTS chataudiobd CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE chataudiobd;

-- Usuários
CREATE TABLE IF NOT EXISTS `usuario` (
  `usuario_id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(220) NOT NULL,
  `senha` VARCHAR(220) NOT NULL,
  `arquivo` VARCHAR(220) NOT NULL DEFAULT 'padrao.svg',
  `bio` VARCHAR(160) NOT NULL DEFAULT '',
  `data` DATETIME DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `usuario_unico` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Amizades (status: solicitado, aguardando, amigo)
CREATE TABLE IF NOT EXISTS `amigos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(220) NOT NULL,
  `amigo` VARCHAR(220) NOT NULL,
  `status` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Áudios postados
CREATE TABLE IF NOT EXISTS `chataudio` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `audio` LONGBLOB NOT NULL,
  `usuario` VARCHAR(220) NOT NULL,
  `estrela` INT NOT NULL DEFAULT 0,
  `assunto` VARCHAR(220) NOT NULL DEFAULT '',
  `data` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Estrelas (curtidas) — apagadas junto com o áudio
CREATE TABLE IF NOT EXISTS `estrelas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `audio_id` INT NOT NULL,
  `usuario` VARCHAR(220) NOT NULL,
  `hora` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_voto` (`audio_id`, `usuario`),
  CONSTRAINT `fk_estrelas_audio` FOREIGN KEY (`audio_id`) REFERENCES `chataudio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stories de 24h
CREATE TABLE IF NOT EXISTS `chataudiostory` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `audio` LONGBLOB NOT NULL,
  `usuario` VARCHAR(220) DEFAULT NULL,
  `data` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ecos (quando alguém repete o áudio de outra pessoa no próprio perfil)
CREATE TABLE IF NOT EXISTS `ecos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `audio_id` INT NOT NULL,
  `usuario` VARCHAR(220) NOT NULL,
  `data` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_eco` (`audio_id`, `usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comentários em áudio
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `audio_id` INT NOT NULL,
  `usuario` VARCHAR(220) NOT NULL,
  `audio` LONGBLOB NOT NULL,
  `data` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `por_audio` (`audio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuários de teste (senha de ambos: 123456)
INSERT IGNORE INTO `usuario` (`usuario`, `senha`, `arquivo`, `data`) VALUES
('admin',  MD5('123456'), 'padrao.svg', NOW()),
('admin2', MD5('123456'), 'padrao.svg', NOW());
