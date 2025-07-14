# Sigilosas VIP - Versão PHP

Versão PHP completa do projeto Sigilosas VIP, desenvolvida para hospedagem compartilhada Hostinger.

## 📋 Características

- ✅ **PHP Puro** - Sem dependências complexas
- ✅ **MySQL** - Banco de dados relacional
- ✅ **JWT Authentication** - Sistema de autenticação seguro
- ✅ **Upload de Arquivos** - Sistema completo de uploads
- ✅ **Painel Administrativo** - Interface completa de gestão
- ✅ **Site Público** - Páginas responsivas e modernas
- ✅ **SEO Otimizado** - Meta tags e estrutura semântica
- ✅ **Segurança** - Proteções contra ataques comuns
- ✅ **Responsivo** - Funciona em todos os dispositivos
- ✅ **Sistema de Moderação** - Fluxo completo de aprovação
- ✅ **Painel da Acompanhante** - Interface personalizada para usuárias

## 🏗️ Estrutura do Projeto

```
📁 sigilosas-php/
├── 📁 config/
│   ├── database.php          # Configuração MySQL
│   └── config.php            # Configurações gerais
├── 📁 core/
│   ├── Auth.php              # Autenticação e sessões
│   └── Upload.php            # Upload de arquivos
├── 📁 admin/                 # Painel administrativo
│   ├── login.php             # Login admin
│   ├── dashboard.php         # Dashboard principal
│   ├── acompanhantes.php     # Gestão de acompanhantes
│   ├── usuarios.php          # Gestão de usuários
│   ├── cidades.php           # Gestão de cidades
│   ├── configuracoes.php     # Configurações
│   ├── estatisticas.php      # Estatísticas
│   └── denuncias.php         # Gestão de denúncias
├── 📁 api/                   # APIs/Backend
│   ├── login.php             # Autenticação
│   ├── logout.php            # Logout
│   ├── acompanhantes.php     # CRUD acompanhantes
│   └── cidades.php           # CRUD cidades
├── 📁 includes/              # Arquivos incluídos
│   ├── header.php            # Cabeçalho público
│   ├── footer.php            # Rodapé público
│   ├── admin-header.php      # Cabeçalho admin
│   └── admin-footer.php      # Rodapé admin
├── 📁 pages/                 # Páginas públicas
│   ├── home.php              # Página inicial
│   ├── login.php             # Login público
│   ├── cadastro.php          # Cadastro público
│   ├── acompanhantes.php     # Listagem de acompanhantes
│   ├── acompanhante.php      # Detalhes de acompanhante
│   ├── contato.php           # Página de contato
│   ├── sobre.php             # Página sobre
│   ├── privacidade.php       # Política de privacidade
│   ├── termos.php            # Termos de uso
│   ├── blog.php              # Blog
│   └── 404.php               # Página de erro
├── 📁 assets/                # Recursos estáticos
│   ├── css/
│   │   └── style.css         # Estilos principais
│   └── js/
│       └── main.js           # JavaScript principal
├── 📁 uploads/               # Arquivos enviados
│   ├── fotos/                # Fotos de acompanhantes
│   ├── videos/               # Vídeos
│   └── documentos/           # Documentos
├── 📁 logs/                  # Logs do sistema
├── index.php                 # Ponto de entrada
├── .htaccess                 # Configuração Apache
└── README.md                 # Este arquivo
```

## 🚀 Instalação

### 1. Requisitos do Servidor

- **PHP**: 7.4 ou superior
- **MySQL**: 5.7 ou superior
- **Apache**: com mod_rewrite habilitado
- **Extensões PHP**: PDO, PDO_MySQL, GD, mbstring, json

### 2. Configuração do Banco de Dados

1. Crie um banco MySQL na sua hospedagem
2. Importe o arquivo `schema-completo.sql`
3. Configure as credenciais em `config/database.php`

### 3. Upload dos Arquivos

1. Faça upload de todos os arquivos para a raiz do seu domínio
2. Configure as permissões:
   ```bash
   chmod 755 uploads/
   chmod 755 logs/
   chmod 644 .htaccess
   ```

### 4. Configuração

1. Edite `config/config.php` com suas configurações
2. Configure as variáveis de ambiente no painel da hospedagem
3. Teste o acesso ao site

## 🔗 URLs de Acesso

### 🌐 Site Principal (Público)
```
http://localhost/Sigilosas-MySQL/
http://localhost/Sigilosas-MySQL/?page=home
http://localhost/Sigilosas-MySQL/?page=acompanhantes
http://localhost/Sigilosas-MySQL/?page=contato
http://localhost/Sigilosas-MySQL/?page=sobre
```

### 👑 Painel Administrativo
```
Login: http://localhost/Sigilosas-MySQL/admin/login.php
Dashboard: http://localhost/Sigilosas-MySQL/admin/dashboard.php
Teste: http://localhost/Sigilosas-MySQL/admin/teste-admin.php
Logout: http://localhost/Sigilosas-MySQL/admin/logout.php

Gerenciamento:
- Acompanhantes: http://localhost/Sigilosas-MySQL/admin/acompanhantes.php
- Cidades: http://localhost/Sigilosas-MySQL/admin/cidades.php
- Denúncias: http://localhost/Sigilosas-MySQL/admin/denuncias.php
- Configurações: http://localhost/Sigilosas-MySQL/admin/configuracoes.php
- Estatísticas: http://localhost/Sigilosas-MySQL/admin/estatisticas.php
```

### 👩‍💼 Painel Acompanhante
```
Login: http://localhost/Sigilosas-MySQL/pages/login-acompanhante.php
Dashboard: http://localhost/Sigilosas-MySQL/acompanhante/index.php
Perfil: http://localhost/Sigilosas-MySQL/acompanhante/perfil.php
Mídia: http://localhost/Sigilosas-MySQL/acompanhante/midia.php
Logout: http://localhost/Sigilosas-MySQL/acompanhante/logout.php
```

### 🔧 Ferramentas de Diagnóstico
```
Teste de Sessões: http://localhost/Sigilosas-MySQL/teste-sessoes.php
Reset Admin: http://localhost/Sigilosas-MySQL/reset-admin.php
Teste Sistema: http://localhost/Sigilosas-MySQL/test-sistema.php
```

## 🔐 Credenciais de Acesso

### Painel Administrativo
- **Email:** `admin@sigilosas.com`
- **Senha:** `admin123`

### Painel Acompanhante
- As credenciais dependem dos acompanhantes cadastrados no banco de dados
- Para criar uma conta de acompanhante, use o cadastro público

## 📋 Estrutura de Sessões

O sistema utiliza sessões separadas para evitar conflitos:

- **Admin:** `sigilosas_admin_session`
- **Acompanhante:** `sigilosas_acompanhante_session`
- **Site Principal:** `sigilosas_session`

Cada painel pode ser acessado simultaneamente sem interferência.

## ⚙️ Configuração

### Variáveis de Ambiente

Configure estas variáveis no painel da sua hospedagem:

```env
DB_HOST=localhost
DB_USER=seu_usuario
DB_PASSWORD=sua_senha
DB_NAME=seu_banco
JWT_SECRET=chave_secreta_jwt
SITE_URL=https://seudominio.com
```

### Configurações Importantes

1. **JWT_SECRET**: Chave secreta para tokens JWT
2. **SITE_URL**: URL completa do seu site
3. **SMTP**: Configurações de email (opcional)
4. **UPLOAD_MAX_SIZE**: Tamanho máximo de upload (padrão: 10MB)

## 🔐 Segurança

### Recursos de Segurança Implementados

- ✅ **Proteção XSS**: Headers de segurança
- ✅ **CSRF Protection**: Tokens em formulários
- ✅ **SQL Injection**: Prepared statements
- ✅ **File Upload**: Validação de tipos e tamanhos
- ✅ **Session Security**: Configurações seguras com isolamento
- ✅ **HTTPS**: Suporte completo
- ✅ **Rate Limiting**: Proteção contra spam
- ✅ **Input Sanitization**: Limpeza de dados
- ✅ **Session Isolation**: Sessões separadas para admin e acompanhantes
- ✅ **Password Hashing**: Bcrypt com custo configurável

### Recomendações Adicionais

1. **SSL/HTTPS**: Configure certificado SSL
2. **Backup**: Faça backups regulares
3. **Updates**: Mantenha PHP atualizado
4. **Monitoring**: Monitore logs de erro

## 📱 Funcionalidades

### Site Público

- **Página Inicial**: Banner, destaques, estatísticas
- **Listagem de Acompanhantes**: Filtros, paginação, busca
- **Perfil de Acompanhante**: Fotos, vídeos, informações
- **Sistema de Login/Cadastro**: Autenticação segura
- **Blog**: Artigos e conteúdo
- **Páginas Estáticas**: Sobre, contato, termos, privacidade

### Painel Administrativo

- **Dashboard**: Estatísticas e gráficos em tempo real
- **Gestão de Acompanhantes**: CRUD completo com aprovação/rejeição
- **Gestão de Cidades**: Cadastro de localidades
- **Configurações**: Configurações do sistema
- **Estatísticas**: Relatórios detalhados
- **Denúncias**: Moderação de conteúdo
- **Sistema de Sessões**: Isolamento completo entre admin e acompanhantes

### Painel Acompanhante

- **Dashboard**: Visão geral do perfil
- **Perfil**: Edição completa de informações pessoais
- **Mídia**: Upload e gerenciamento de fotos, vídeos e documentos
- **Galeria**: Sistema de fotos com preview e exclusão
- **Verificação**: Upload de documentos e vídeos de verificação
- **Status**: Acompanhamento de aprovação do perfil

### APIs

- **Autenticação**: Login/logout com JWT
- **CRUD Acompanhantes**: Gerenciamento via API
- **CRUD Cidades**: Gestão de localidades
- **Upload**: Sistema de upload de arquivos
- **Upload de Mídia**: Fotos, vídeos e documentos
- **Exclusão de Mídia**: Remoção segura de arquivos
- **Verificação**: Upload de documentos de identidade

## 🎨 Personalização

### Cores e Estilo

Edite `assets/css/style.css` para personalizar:

```css
:root {
    --primary-color: #dc3545;    /* Cor principal */
    --secondary-color: #6c757d;  /* Cor secundária */
    --success-color: #28a745;    /* Cor de sucesso */
    --info-color: #17a2b8;       /* Cor de informação */
    --warning-color: #ffc107;    /* Cor de aviso */
    --danger-color: #dc3545;     /* Cor de erro */
}
```

### Logo e Branding

1. Substitua `assets/img/logo.png`
2. Edite `includes/header.php` para o novo logo
3. Atualize cores no CSS

### Conteúdo

1. **Textos**: Edite os arquivos PHP nas pastas `pages/` e `admin/`
2. **Imagens**: Substitua arquivos em `assets/img/`
3. **Configurações**: Edite `config/config.php`

## 📊 Banco de Dados

### Tabelas Principais

- **usuarios**: Usuários do sistema
- **acompanhantes**: Perfis de acompanhantes
- **cidades**: Cidades disponíveis
- **estados**: Estados brasileiros
- **blog_posts**: Posts do blog
- **contatos**: Mensagens de contato
- **denuncias**: Denúncias de usuários

### Backup

```bash
# Backup manual
mysqldump -u usuario -p banco > backup.sql

# Restaurar
mysql -u usuario -p banco < backup.sql
```

## 🔧 Manutenção

### Logs

Os logs são salvos em `logs/`:
- `error_YYYY-MM-DD.log`: Erros do sistema
- `access.log`: Acessos (configurado no .htaccess)

### Monitoramento

1. **Verificar logs**: Monitore arquivos de log
2. **Performance**: Use ferramentas como GTmetrix
3. **Segurança**: Monitore tentativas de acesso
4. **Backup**: Faça backups regulares

### Updates

1. **Backup**: Sempre faça backup antes
2. **Teste**: Teste em ambiente de desenvolvimento
3. **Deploy**: Faça deploy em horário de baixo tráfego
4. **Verificação**: Teste funcionalidades críticas

## 🐛 Troubleshooting

### Problemas Comuns

1. **Erro 500**: Verifique permissões e logs
2. **Upload não funciona**: Verifique permissões da pasta uploads/
3. **Página em branco**: Ative debug em config.php
4. **Conexão com banco**: Verifique credenciais

### Debug

Ative o modo debug em `config/config.php`:

```php
define('DEBUG_MODE', true);
```

### Logs de Erro

Verifique os logs em:
- `logs/error_YYYY-MM-DD.log`
- Logs do servidor web
- Logs do PHP

## 📞 Suporte

### Contato

- **Email**: contato@sigilosasvip.com
- **Telefone**: (11) 99999-9999
- **Horário**: Seg-Sex 9h às 18h

### Documentação

- **Manual do Usuário**: Disponível no painel admin
- **API Documentation**: Consulte os arquivos em `api/`
- **FAQ**: Seção de perguntas frequentes

## 📄 Licença

Este projeto é proprietário da Sigilosas VIP. Todos os direitos reservados.

## 🔄 Changelog

### v1.0.0 (2024-12-15)
- ✅ Versão inicial completa
- ✅ Painel administrativo
- ✅ Site público responsivo
- ✅ Sistema de autenticação
- ✅ Upload de arquivos
- ✅ APIs funcionais
- ✅ SEO otimizado
- ✅ Segurança implementada

## 🔄 Fluxo Completo do Sistema

### 1. Cadastro Público
- **Acesso**: `/pages/cadastro.php`
- **Processo**: 
  - Usuária preenche dados básicos (nome, email, senha, cidade)
  - Sistema valida dados e cria conta com status "pendente"
  - Redireciona para login com mensagem de sucesso
- **Segurança**: Senha hashada com `password_hash()`

### 2. Login da Acompanhante
- **Acesso**: `/pages/login.php`
- **Processo**:
  - Login com email/senha
  - Verificação de status da conta
  - Criação de sessão segura
  - Redirecionamento para painel da acompanhante
- **Controle**: Apenas contas aprovadas podem acessar o painel
- **Recuperação**: Link para recuperação de senha

### 3. Painel da Acompanhante
- **Acesso**: `/acompanhante/` (após login)
- **Funcionalidades**:
  - **Dashboard**: Visão geral do perfil e estatísticas
  - **Editar Perfil**: Completar informações pessoais e profissionais
  - **Upload de Mídia**: Fotos, vídeos e documentos
  - **Visualizar Perfil**: Como aparece no site público
- **Segurança**: Acesso restrito por sessão e status

### 4. Sistema de Upload de Mídia
- **Fotos**: `/api/upload-foto.php` - Upload de fotos do perfil
- **Vídeos**: `/api/upload-video.php` - Upload de vídeos promocionais
- **Documentos**: `/api/upload-documento.php` - Upload de documentos
- **Validações**: Tipo, tamanho, segurança
- **Armazenamento**: Organizado por ID da acompanhante

### 5. Moderação Administrativa
- **Acesso**: `/admin/` (apenas administradores)
- **Processo**:
  - Visualização de perfis pendentes
  - Aprovação/rejeição de contas
  - Edição de informações
  - Gestão de denúncias
- **Controle**: Sistema de status (pendente, aprovado, rejeitado, bloqueado)

### 6. Exibição Pública
- **Listagem**: `/pages/acompanhantes.php` - Apenas perfis aprovados
- **Perfil Individual**: `/pages/acompanhante.php?id=X` - Detalhes completos
- **Filtros**: Por cidade, serviços, preços
- **Mídia**: Fotos, vídeos e informações públicas

### 7. Recuperação de Senha
- **Solicitação**: `/pages/recuperar-senha.php` - Formulário para email
- **Processo**:
  - Validação do email cadastrado
  - Geração de token único e seguro
  - Envio de email com link de recuperação
  - Token expira em 1 hora
- **Redefinição**: `/pages/redefinir-senha.php?token=XXX` - Nova senha
- **Segurança**: Tokens únicos, expiração, validação de senha

## 👥 Tipos de Usuário

### 1. Público Geral
- **Acesso**: Site público
- **Funcionalidades**: Visualizar acompanhantes, contato, blog
- **Restrições**: Não pode acessar painéis

### 2. Acompanhante
- **Acesso**: Painel da acompanhante (`/acompanhante/`)
- **Funcionalidades**: 
  - Gerenciar perfil pessoal
  - Upload de mídia
  - Visualizar estatísticas
  - Editar informações
- **Status**: Deve ter conta aprovada

### 3. Administrador
- **Acesso**: Painel administrativo (`/admin/`)
- **Funcionalidades**:
  - Gestão completa de acompanhantes
  - Moderação de conteúdo
  - Estatísticas do sistema
  - Configurações gerais
- **Privilégios**: Acesso total ao sistema

## 🔐 Sistema de Segurança

### Controle de Acesso
- **Sessões**: Gerenciamento seguro de sessões
- **Status**: Controle por status de conta
- **Middleware**: Verificação de permissões em cada página
- **Logout**: Destruição segura de sessões

### Validação de Dados
- **Input Sanitization**: Limpeza de dados de entrada
- **SQL Injection**: Prepared statements
- **XSS Protection**: Headers de segurança
- **File Upload**: Validação rigorosa de arquivos

### Proteção de Arquivos
- **Uploads**: Validação de tipo, tamanho e conteúdo
- **Diretórios**: Proteção contra listagem
- **Execução**: Prevenção de execução de arquivos maliciosos

## 📁 Estrutura de Arquivos Implementada

### Painel da Acompanhante
```
📁 acompanhante/
├── index.php              # Dashboard principal
├── perfil.php             # Edição de perfil
├── midia.php              # Upload de mídia
├── visualizar.php         # Visualizar perfil público
└── logout.php             # Logout seguro
```

### APIs de Upload
```
📁 api/
├── upload-foto.php        # Upload de fotos
├── upload-video.php       # Upload de vídeos
├── upload-documento.php   # Upload de documentos
└── get-midias.php         # Buscar mídias da acompanhante
```

### Sistema de Recuperação de Senha
```
📁 pages/
├── recuperar-senha.php    # Solicitar recuperação
└── redefinir-senha.php    # Redefinir senha
📁 core/
└── Email.php              # Classe para envio de emails
📁 cron/
└── limpar-tokens.php      # Limpeza automática de tokens
```

### Sistema de Autenticação
```
📁 core/
├── Auth.php               # Classe de autenticação
├── Session.php            # Gerenciamento de sessões
└── Security.php           # Funções de segurança
```

## 🎯 Pontos de Entrada

### Para Acompanhantes
1. **Cadastro**: `http://localhost/Sigilosas-MySQL/pages/cadastro-acompanhante.php`
2. **Login**: `http://localhost/Sigilosas-MySQL/pages/login-acompanhante.php`
3. **Painel**: `http://localhost/Sigilosas-MySQL/acompanhante/` (após login)

### Para Administradores
1. **Login Admin**: `http://localhost/Sigilosas-MySQL/admin/login.php`
2. **Painel Admin**: `http://localhost/Sigilosas-MySQL/admin/dashboard.php` (após login)
3. **Teste Admin**: `http://localhost/Sigilosas-MySQL/admin/teste-admin.php`

### Para Público
1. **Site Principal**: `http://localhost/Sigilosas-MySQL/`
2. **Listagem**: `http://localhost/Sigilosas-MySQL/?page=acompanhantes`
3. **Perfil Individual**: `http://localhost/Sigilosas-MySQL/?page=acompanhante&id=X`

## 🔄 Status do Sistema

### Fluxo de Status das Contas
1. **Pendente**: Conta criada, aguardando aprovação
2. **Aprovado**: Conta aprovada, pode usar painel
3. **Rejeitado**: Conta rejeitada, não pode acessar
4. **Bloqueado**: Conta bloqueada por violação

### Controle de Visibilidade
- **Site Público**: Apenas contas "aprovadas"
- **Painel da Acompanhante**: Apenas contas "aprovadas"
- **Painel Admin**: Apenas administradores

## 📊 Funcionalidades Implementadas

### ✅ Completamente Funcional
- Cadastro público com validações
- Sistema de login/logout seguro
- **Sistema de recuperação de senha** com tokens seguros
- Painel da acompanhante completo
- Upload de mídia (fotos, vídeos, documentos)
- Sistema de moderação administrativa
- Exibição pública de perfis aprovados
- Controle de acesso por status
- Interface responsiva e moderna

### 🔄 Próximos Passos (Opcionais)
- Sistema de mensagens entre usuários
- Notificações por email
- Sistema de avaliações
- Relatórios avançados
- Integração com pagamentos
- App mobile

## 🚨 Troubleshooting

### Problemas Comuns

#### 1. "Sessão Expirada" no Painel Acompanhante
- **Causa**: Conflito de sessões entre painéis
- **Solução**: Use abas separadas para admin e acompanhante
- **Prevenção**: Sessões já estão isoladas (`sigilosas_admin_session` vs `sigilosas_acompanhante_session`)

#### 2. Erro de Conexão com Banco
- **Causa**: Configuração incorreta ou banco remoto inacessível
- **Solução**: Verifique `config/database.php`
- **Teste**: Acesse `http://localhost/Sigilosas-MySQL/admin/teste-admin.php`

#### 3. Upload de Arquivos Falhando
- **Causa**: Permissões de diretório ou limite de tamanho
- **Solução**: 
  ```bash
  chmod 755 uploads/
  chmod 755 uploads/galeria/
  chmod 755 uploads/documentos/
  chmod 755 uploads/videos/
  ```

#### 4. Login Admin Não Funciona
- **Solução**: Execute `http://localhost/Sigilosas-MySQL/reset-admin.php`
- **Credenciais**: admin@sigilosas.com / admin123

### Ferramentas de Diagnóstico

- **Teste de Sessões**: `http://localhost/Sigilosas-MySQL/teste-sessoes.php`
- **Teste Admin**: `http://localhost/Sigilosas-MySQL/admin/teste-admin.php`
- **Teste Sistema**: `http://localhost/Sigilosas-MySQL/test-sistema.php`
- **Reset Admin**: `http://localhost/Sigilosas-MySQL/reset-admin.php`

---

**Desenvolvido com ❤️ para a Sigilosas VIP** 

## Configuração de E-mail SMTP para Recuperação de Senha

Para que o sistema envie e-mails de recuperação de senha (e outros avisos) usando seu e-mail profissional na Hostinger, siga as instruções abaixo:

### 1. Dados da Caixa de E-mail
- **E-mail:** recuperacao@sigilosasvip.com.br
- **Senha:** (definida no painel da Hostinger)

### 2. Configurações do Servidor SMTP (Hostinger)
- **Servidor SMTP:** smtp.hostinger.com
- **Porta:** 465
- **Criptografia:** SSL
- **Usuário:** recuperacao@sigilosasvip.com.br
- **Senha:** (a mesma definida ao criar o e-mail)

### 3. Exemplo de Configuração no Código (PHPMailer ou similar)
```php
$mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->SMTPAuth = true;
$mail->Username = 'recuperacao@sigilosasvip.com.br';
$mail->Password = 'SUA_SENHA_AQUI';
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;
```

### 4. Observações
- Altere a senha no código/configuração sempre que mudar no painel da Hostinger.
- Nunca deixe a senha exposta em repositórios públicos.
- Use variáveis de ambiente ou arquivos de configuração protegidos para armazenar as credenciais.

---
Essas informações garantem que o envio de e-mails de recuperação de senha funcione corretamente em produção. 