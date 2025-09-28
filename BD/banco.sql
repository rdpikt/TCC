-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 21/09/2025 às 21:34
-- Versão do servidor: 9.1.0
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `banco`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `parent_comment_id` int DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_comment_id` (`parent_comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comunidades`
--

DROP TABLE IF EXISTS `comunidades`;
CREATE TABLE IF NOT EXISTS `comunidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `imagem` varchar(255) DEFAULT NULL,
  `dono_id` int NOT NULL,
  `privacidade` enum('publica','privada') DEFAULT 'publica',
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipo_comunidade` enum('Design','Crafts','literatura','escrita') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dono_id` (`dono_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `comunidades`
--

INSERT INTO `comunidades` (`id`, `nome`, `descricao`, `imagem`, `dono_id`, `privacidade`, `data_criacao`, `tipo_comunidade`) VALUES
(1, 'HarpHub', 'Comunidade oficial do HarpHub', '2009202515_spidey_.jpg', 4, 'publica', '2025-09-20 22:40:23', 'Design'),
(2, 'Comunidade Teste', 'Essa é uma comunidade criada apenas para testes.', 'comunidade_teste.png', 1, 'publica', '2025-09-21 00:09:43', 'Design'),
(3, 'Comunidade Design', 'Comunidade voltada para designers compartilharem ideias e projetos.', 'comunidade_design.png', 1, 'publica', '2025-09-21 00:11:01', 'Design');

-- --------------------------------------------------------

--
-- Estrutura para tabela `comunidade_membros`
--

DROP TABLE IF EXISTS `comunidade_membros`;
CREATE TABLE IF NOT EXISTS `comunidade_membros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `comunidade_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `cargo` enum('dono','adm','mod','membro') DEFAULT 'membro',
  `data_entrada` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_membro` (`comunidade_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `curtidas`
--

DROP TABLE IF EXISTS `curtidas`;
CREATE TABLE IF NOT EXISTS `curtidas` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `obra_id` int NOT NULL,
  `data_curtida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `unica_curtida` (`usuario_id`,`obra_id`),
  KEY `obra_id` (`obra_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `galerias`
--

DROP TABLE IF EXISTS `galerias`;
CREATE TABLE IF NOT EXISTS `galerias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `galeria_obras`
--

DROP TABLE IF EXISTS `galeria_obras`;
CREATE TABLE IF NOT EXISTS `galeria_obras` (
  `galeria_id` int NOT NULL,
  `obra_id` int NOT NULL,
  PRIMARY KEY (`galeria_id`,`obra_id`),
  KEY `obra_id` (`obra_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_atividades`
--

DROP TABLE IF EXISTS `log_atividades`;
CREATE TABLE IF NOT EXISTS `log_atividades` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `acao` varchar(100) NOT NULL,
  `detalhes` text,
  `data_acao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

DROP TABLE IF EXISTS `notificacoes`;
CREATE TABLE IF NOT EXISTS `notificacoes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'ID do usuário que recebe a notificação',
  `remetente_id` int DEFAULT NULL COMMENT 'ID do usuário que originou a ação (pode ser nulo para sistema)',
  `tipo` enum('curtida','seguimento','comentario','repost','sistema') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `link_id` int DEFAULT NULL COMMENT 'ID do objeto relacionado (ex: post_id, user_id)',
  `lida` tinyint(1) DEFAULT '0',
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `user_id` (`user_id`),
  KEY `remetente_id` (`remetente_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `obras`
--

DROP TABLE IF EXISTS `obras`;
CREATE TABLE IF NOT EXISTS `obras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `portfolio_id` int NOT NULL,
  `tipo_obra` enum('Visual','Áudio','ÁudioVisual') NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text,
  `arquivo_url` varchar(255) DEFAULT NULL,
  `data_publicacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('ativo','removido','rascunho') DEFAULT 'ativo',
  `tipo_imagem` enum('png','jpg','jpeg','mp4','mp3','wav') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `portfolio_id` (`portfolio_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `obras`
--

INSERT INTO `obras` (`id`, `portfolio_id`, `tipo_obra`, `titulo`, `descricao`, `arquivo_url`, `data_publicacao`, `status`, `tipo_imagem`) VALUES
(6, 4, 'Visual', 'teste de outros tipos de post', 'teste de outros tipos de post', '2109202525_miranha1.jpg', '2025-09-21 21:31:25', 'ativo', 'jpg'),
(7, 4, 'Áudio', 'teste 3 ', 'teste 3 ', '2109202539_spiderman fpf.jpeg', '2025-09-21 21:31:39', 'ativo', 'jpeg'),
(8, 4, 'ÁudioVisual', 'teste do user admin', 'teste do user admin', '2109202559_a-noite-estrelada.png', '2025-09-21 21:31:59', 'ativo', 'png');

-- --------------------------------------------------------

--
-- Estrutura para tabela `portfolios`
--

DROP TABLE IF EXISTS `portfolios`;
CREATE TABLE IF NOT EXISTS `portfolios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `visibilidade` enum('publico','privado','restrio') DEFAULT 'publico',
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `reposts`
--

DROP TABLE IF EXISTS `reposts`;
CREATE TABLE IF NOT EXISTS `reposts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `original_post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `original_post_id` (`original_post_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `reposts`
--

INSERT INTO `reposts` (`id`, `original_post_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 20, 4, NULL, '2025-09-21 16:45:47'),
(2, 22, 4, NULL, '2025-09-21 16:45:48'),
(3, 23, 4, NULL, '2025-09-21 16:46:03'),
(4, 25, 4, NULL, '2025-09-21 17:50:31'),
(5, 24, 4, NULL, '2025-09-21 18:38:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `seguidores`
--

DROP TABLE IF EXISTS `seguidores`;
CREATE TABLE IF NOT EXISTS `seguidores` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `seguidor_id` int NOT NULL,
  `seguido_id` int NOT NULL,
  `data_inicio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `unico_seguimento` (`seguidor_id`,`seguido_id`),
  KEY `seguido_id` (`seguido_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_user` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_tag` enum('cantor','musico','poeta') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `data_nasc` date NOT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `data_criacao` datetime DEFAULT NULL,
  `status_conta` enum('ativo','banido','pendente') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'profile.png',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome_completo`, `nome_user`, `user_tag`, `data_nasc`, `bio`, `data_criacao`, `status_conta`, `email`, `senha`, `user_avatar`) VALUES
(4, 'admin', 'Admin', '', '2222-02-22', 'admin aqui', NULL, NULL, 'admin@gmail.com', '$2y$10$Z/9S/uCcumV4vIR.OJbkuuZOqohqfA5JvJ7ebWZ0EPfqRyGz./HsK', '2109202557_spiderman fpf.jpeg');

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `portfolios`
--
ALTER TABLE `portfolios`
  ADD CONSTRAINT `portfolios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
