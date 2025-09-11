-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 12/08/2025 às 18:32
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
  `usuario_id` int NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `conteudo` text NOT NULL,
  `lida` tinyint(1) DEFAULT '0',
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `usuario_id` (`usuario_id`)
) ;

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
  PRIMARY KEY (`id`),
  KEY `portfolio_id` (`portfolio_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `obras`
--

INSERT INTO `obras` (`id`, `portfolio_id`, `tipo_obra`, `titulo`, `descricao`, `arquivo_url`, `data_publicacao`, `status`) VALUES
(1, 3, 'Visual', 'teste', 'é apenas um teste', '../images/uploads/post1.jpg', '2025-08-10 21:04:44', 'ativo'),
(2, 2, 'Visual', 'isso é apenas 1 teste 2', 'teste2 teste', '../images/uploads/post2.jpg', '2025-08-10 22:54:03', 'ativo'),
(3, 2, 'Visual', 'isso é apenas 1 teste 2', 'teste2 teste', '../images/uploads/post2.jpg', '2025-08-10 22:54:03', 'ativo'),
(4, 2, 'Visual', 'isso é apenas 1 teste 2', 'teste2 teste', '../images/uploads/post2.jpg', '2025-08-10 22:54:08', 'ativo'),
(5, 2, 'Visual', 'isso é apenas 1 teste 2', 'teste2 teste', '../images/uploads/post2.jpg', '2025-08-10 22:54:08', 'ativo');

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
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_user` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `user_tag` enum('cantor','musico','poeta') COLLATE utf8mb4_general_ci NOT NULL,
  `data_nasc` date NOT NULL,
  `bio` text COLLATE utf8mb4_general_ci,
  `data_criacao` datetime DEFAULT NULL,
  `status_conta` enum('ativo','banido','pendente') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '../images/profile.png',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome_completo`, `nome_user`, `user_tag`, `data_nasc`, `bio`, `data_criacao`, `status_conta`, `email`, `senha`, `user_avatar`) VALUES
(2, 'Luiz', 'Luiz', '', '2007-02-13', NULL, NULL, NULL, 'rodriguesdossantosl937@gmail.com', '$2y$10$/dxL/XiJWxJXqrIyMWWZVeSGpKau2fzgNX5qxKW4wCNq.9dMdD0JC', '../images/post1.jpg'),
(3, 'teste', 'testezin', '', '2005-02-02', NULL, NULL, NULL, 'znixpov@candassociates.com', '$2y$10$p0WsD5Zpb6DlPWRwrOXTTubQqD5H65YjWGqTO.Schxo5x24KhdepG', '../images/profile.png');

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
