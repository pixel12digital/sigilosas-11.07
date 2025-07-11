-- Tabela para posts do blog
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `resumo` text NOT NULL,
  `conteudo` longtext NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `autor` varchar(100) NOT NULL,
  `status` enum('rascunho','publicado') NOT NULL DEFAULT 'rascunho',
  `data_publicacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `visualizacoes` int(11) NOT NULL DEFAULT 0,
  `categoria` varchar(50) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_publicacao` (`data_publicacao`),
  KEY `idx_autor` (`autor`),
  KEY `idx_visualizacoes` (`visualizacoes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir alguns posts de exemplo
INSERT INTO `blog_posts` (`titulo`, `resumo`, `conteudo`, `autor`, `status`, `data_publicacao`, `categoria`) VALUES
('Como Garantir sua Segurança ao Contratar Acompanhantes', 
'Dicas essenciais para garantir uma experiência segura e tranquila ao contratar serviços de acompanhantes de luxo.', 
'Quando se trata de contratar serviços de acompanhantes de luxo, a segurança deve ser sempre a prioridade número um. Neste artigo, vamos compartilhar dicas valiosas para garantir que sua experiência seja não apenas agradável, mas também completamente segura.

## 1. Escolha Plataformas Confiáveis

Sempre utilize plataformas reconhecidas e com boa reputação no mercado. Sites como o Sigilosas VIP oferecem perfis verificados e sistemas de segurança implementados.

## 2. Verifique o Perfil da Acompanhante

Antes de fazer qualquer contato, analise cuidadosamente o perfil da acompanhante:
- Fotos reais e de qualidade
- Informações completas e coerentes
- Avaliações de outros clientes
- Tempo de cadastro na plataforma

## 3. Comunicação Segura

Mantenha a comunicação sempre através da plataforma oficial:
- Evite compartilhar informações pessoais desnecessárias
- Não forneça dados bancários ou documentos pessoais
- Use apenas os canais de comunicação oficiais

## 4. Encontro em Local Seguro

Sempre combine encontros em locais públicos e seguros:
- Hotéis de qualidade
- Restaurantes conhecidos
- Evite locais isolados ou desconhecidos

## 5. Confie nos seus Instintos

Se algo parecer suspeito ou você se sentir desconfortável, não hesite em cancelar o encontro. Sua segurança sempre vem em primeiro lugar.

## Conclusão

Seguindo essas dicas, você pode desfrutar de uma experiência segura e agradável. Lembre-se: a segurança não é negociável.', 
'Equipe Sigilosas', 
'publicado', 
'2024-12-15 10:00:00', 
'Segurança'),

('Tendências do Mercado de Acompanhantes de Luxo em 2024', 
'Descubra as principais tendências e mudanças no mercado de acompanhantes de luxo para o ano de 2024.', 
'O mercado de acompanhantes de luxo está em constante evolução, e 2024 não é exceção. Vamos analisar as principais tendências que estão moldando este setor.

## Digitalização e Tecnologia

A tecnologia continua revolucionando o setor:
- Apps especializados com recursos avançados
- Verificação biométrica de identidade
- Sistemas de pagamento seguros e discretos
- Inteligência artificial para matching perfeito

## Personalização dos Serviços

Os clientes buscam experiências cada vez mais personalizadas:
- Serviços sob medida
- Acompanhantes especializadas em diferentes perfis
- Eventos corporativos e sociais
- Viagens e eventos especiais

## Segurança e Discretude

A segurança continua sendo prioridade:
- Verificação rigorosa de identidade
- Sistemas de avaliação e feedback
- Proteção de dados pessoais
- Códigos de conduta profissionais

## Sustentabilidade e Responsabilidade Social

Novas preocupações entram em cena:
- Acompanhantes com formação acadêmica
- Participação em eventos beneficentes
- Consciência ambiental
- Responsabilidade social corporativa

## Conclusão

O mercado está se tornando mais profissional, seguro e personalizado. As empresas que se adaptarem a essas tendências terão sucesso garantido.', 
'Equipe Sigilosas', 
'publicado', 
'2024-12-10 14:30:00', 
'Tendências'),

('Dicas para um Primeiro Encontro Perfeito', 
'Como preparar e aproveitar ao máximo seu primeiro encontro com uma acompanhante de luxo.', 
'Um primeiro encontro pode ser tanto emocionante quanto desafiador. Com as dicas certas, você pode garantir uma experiência memorável e agradável para ambos.

## Preparação Antes do Encontro

### Escolha do Local
- Restaurantes elegantes e conhecidos
- Hotéis de qualidade com boa localização
- Evite locais muito movimentados ou barulhentos
- Considere a privacidade do local

### Preparação Pessoal
- Vista-se adequadamente para a ocasião
- Chegue no horário combinado
- Tenha dinheiro ou cartão disponível
- Mantenha o celular no silencioso

## Durante o Encontro

### Comunicação
- Seja educado e respeitoso
- Mantenha conversas leves e agradáveis
- Evite assuntos polêmicos ou pessoais demais
- Demonstre interesse genuíno

### Comportamento
- Mantenha a discrição
- Respeite os limites estabelecidos
- Seja generoso com gorjetas
- Agradeça pela companhia

## Após o Encontro

### Feedback
- Deixe uma avaliação positiva se apropriado
- Mantenha a discrição sobre detalhes
- Considere futuros encontros se houver interesse mútuo

## Conclusão

Um primeiro encontro bem-sucedido depende de preparação, respeito e boa comunicação. Seguindo essas dicas, você pode criar uma experiência positiva para todos os envolvidos.', 
'Equipe Sigilosas', 
'publicado', 
'2024-12-05 16:45:00', 
'Dicas'); 