-- Banco de dados do Retroway
-- Em desenvolvimento (XAMPP), este arquivo é executado automaticamente.
-- Em produção, rode uma vez: php instalar.php
-- Pode ser executado várias vezes sem problema (só cria o que ainda não existe).

-- Usuários
CREATE TABLE IF NOT EXISTS `usuario` (
  `usuario_id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(220) NOT NULL,
  `email` VARCHAR(190) DEFAULT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `arquivo` VARCHAR(220) NOT NULL DEFAULT 'padrao.svg',
  `bio` VARCHAR(160) NOT NULL DEFAULT '',
  `data` DATETIME DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `usuario_unico` (`usuario`),
  UNIQUE KEY `email_unico` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Amizades (status: solicitado, aguardando, amigo)
CREATE TABLE IF NOT EXISTS `amigos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(220) NOT NULL,
  `amigo` VARCHAR(220) NOT NULL,
  `status` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `por_usuario` (`usuario`),
  KEY `por_amigo` (`amigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Áudios postados (a coluna "audio" guarda o caminho do arquivo em uploads/audios/)
CREATE TABLE IF NOT EXISTS `chataudio` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `audio` LONGBLOB NOT NULL,
  `usuario` VARCHAR(220) NOT NULL,
  `estrela` INT NOT NULL DEFAULT 0,
  `assunto` VARCHAR(220) NOT NULL DEFAULT '',
  `data` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `por_usuario` (`usuario`)
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

-- Bloqueios (quem bloqueou quem)
CREATE TABLE IF NOT EXISTS `bloqueios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(220) NOT NULL,
  `bloqueado` VARCHAR(220) NOT NULL,
  `data` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_bloqueio` (`usuario`, `bloqueado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Denúncias (tipo: audio, comentario, story, usuario)
CREATE TABLE IF NOT EXISTS `denuncias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `denunciante` VARCHAR(220) NOT NULL,
  `tipo` VARCHAR(20) NOT NULL,
  `alvo_id` INT NOT NULL DEFAULT 0,
  `alvo_usuario` VARCHAR(220) NOT NULL DEFAULT '',
  `motivo` VARCHAR(60) NOT NULL,
  `detalhe` VARCHAR(300) NOT NULL DEFAULT '',
  `status` VARCHAR(20) NOT NULL DEFAULT 'aberta',
  `data` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `por_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Limites de uso (tentativas de login, posts por minuto, etc.)
CREATE TABLE IF NOT EXISTS `limites` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(255) NOT NULL,
  `data` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `por_chave` (`chave`, `data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recuperação de senha (guarda só o "hash" do código enviado por e-mail)
CREATE TABLE IF NOT EXISTS `recuperacoes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(220) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expira` DATETIME NOT NULL,
  `usado` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `por_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Controle interno (versão da estrutura do banco)
CREATE TABLE IF NOT EXISTS `sistema` (
  `chave` VARCHAR(64) NOT NULL,
  `valor` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
