-- Tabela para denúncias de acompanhantes
CREATE TABLE IF NOT EXISTS `denuncias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acompanhante_id` int(11) NOT NULL,
  `tipo` enum('inapropriado','fake','spam','outro') NOT NULL,
  `descricao` text NOT NULL,
  `status` enum('pendente','resolvida','invalida') NOT NULL DEFAULT 'pendente',
  `resolucao` text DEFAULT NULL,
  `resolvida_por` int(11) DEFAULT NULL,
  `resolvida_em` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_acompanhante_id` (`acompanhante_id`),
  KEY `idx_status` (`status`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_denuncias_acompanhante` FOREIGN KEY (`acompanhante_id`) REFERENCES `acompanhantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir algumas denúncias de exemplo
INSERT INTO `denuncias` (`acompanhante_id`, `tipo`, `descricao`, `status`, `created_at`) VALUES
(1, 'fake', 'Perfil parece ser fake, fotos não são da pessoa real', 'pendente', '2024-12-15 10:30:00'),
(2, 'inapropriado', 'Conteúdo inadequado nas fotos da galeria', 'pendente', '2024-12-14 15:45:00'),
(3, 'spam', 'Perfil enviando mensagens em massa para outros usuários', 'resolvida', '2024-12-13 09:20:00'),
(1, 'outro', 'Informações pessoais falsas no perfil', 'invalida', '2024-12-12 14:15:00'); 