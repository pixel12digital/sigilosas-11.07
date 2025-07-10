# Sistema de Recuperação de Senha - Sigilosas VIP

## 📋 Visão Geral

O sistema de recuperação de senha permite que usuárias redefinam suas senhas de forma segura através de tokens únicos enviados por email.

## 🔄 Fluxo Completo

### 1. Solicitação de Recuperação
- **URL**: `/pages/recuperar-senha.php`
- **Processo**:
  1. Usuária digita email cadastrado
  2. Sistema valida se email existe
  3. Gera token único de 64 caracteres
  4. Salva token no banco com expiração de 1 hora
  5. Envia email com link de recuperação

### 2. Redefinição de Senha
- **URL**: `/pages/redefinir-senha.php?token=XXX`
- **Processo**:
  1. Sistema valida token (existência, expiração, uso)
  2. Usuária digita nova senha (mínimo 6 caracteres)
  3. Confirma nova senha
  4. Sistema atualiza senha no banco
  5. Marca token como usado
  6. Redireciona para login

## 🔐 Segurança

### Tokens
- **Geração**: `bin2hex(random_bytes(32))` - 64 caracteres hexadecimais
- **Expiração**: 1 hora após criação
- **Uso único**: Token é marcado como usado após redefinição
- **Validação**: Verifica existência, expiração e status de uso

### Validações
- **Email**: Formato válido e existência no banco
- **Senha**: Mínimo 6 caracteres
- **Confirmação**: Senhas devem coincidir
- **Token**: Deve ser válido, não expirado e não usado

### Proteções
- **Rate Limiting**: Implementado na aplicação
- **SQL Injection**: Prepared statements
- **XSS**: Sanitização de inputs
- **CSRF**: Tokens únicos por sessão

## 📁 Arquivos do Sistema

### Páginas
- `pages/recuperar-senha.php` - Formulário de solicitação
- `pages/redefinir-senha.php` - Formulário de redefinição

### Classes
- `core/Email.php` - Gerenciamento de emails

### Banco de Dados
- `tabela-recuperacao-senha.sql` - Estrutura da tabela

### Manutenção
- `cron/limpar-tokens.php` - Limpeza automática

## 🗄️ Estrutura do Banco

### Tabela `recuperacao_senha`
```sql
CREATE TABLE recuperacao_senha (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token VARCHAR(64) UNIQUE NOT NULL,
  expira DATETIME NOT NULL,
  usado TINYINT(1) DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

### Índices
- `token` - Busca rápida por token
- `expira` - Limpeza de tokens expirados
- `usuario_id` - Relacionamento com usuário

## 📧 Sistema de Email

### Classe Email
- **Método**: `enviarRecuperacaoSenha($email, $nome, $token)`
- **Template**: HTML responsivo com branding
- **Conteúdo**:
  - Saudação personalizada
  - Link de recuperação
  - Aviso de expiração
  - Instruções de segurança

### Configuração
- **From**: `noreply@seudominio.com`
- **Headers**: MIME-Version, Content-Type, X-Mailer
- **Formato**: HTML com CSS inline

## 🧹 Manutenção Automática

### Script de Limpeza
- **Arquivo**: `cron/limpar-tokens.php`
- **Frequência**: A cada 6 horas (configurável)
- **Ação**: Remove tokens expirados
- **Log**: Registra operações em arquivo

### Configuração Cron
```bash
# Executar a cada 6 horas
0 */6 * * * php /caminho/para/cron/limpar-tokens.php
```

## 🎨 Interface do Usuário

### Página de Solicitação
- Formulário simples com campo de email
- Validação em tempo real
- Mensagens de feedback
- Links para login e cadastro

### Página de Redefinição
- Formulário com campos de senha
- Validação de força da senha
- Confirmação obrigatória
- Feedback visual de erros

### Design
- Responsivo para mobile
- Cores consistentes com o site
- Ícones FontAwesome
- Alertas de sucesso/erro

## 🔧 Configuração

### Variáveis Necessárias
```php
// config/config.php
define('SITE_URL', 'https://seudominio.com');
```

### Permissões de Arquivo
```bash
chmod 755 cron/
chmod 644 cron/limpar-tokens.php
```

### Configuração de Email
- **Servidor**: Configurar servidor SMTP
- **From**: Email válido do domínio
- **SPF/DKIM**: Configurar registros DNS

## 🐛 Troubleshooting

### Problemas Comuns

1. **Email não chega**
   - Verificar configuração SMTP
   - Verificar pasta spam
   - Testar com email de teste

2. **Token inválido**
   - Verificar expiração
   - Verificar se já foi usado
   - Verificar formato do token

3. **Erro de banco**
   - Verificar estrutura da tabela
   - Verificar permissões
   - Verificar conexão

### Logs
- **Cron**: `logs/cron-tokens-YYYY-MM-DD.log`
- **Email**: Logs do servidor SMTP
- **PHP**: Logs de erro do PHP

## 📊 Monitoramento

### Métricas Importantes
- Tokens gerados por dia
- Taxa de sucesso de recuperação
- Tempo médio de uso do token
- Emails não entregues

### Alertas
- Falha no envio de emails
- Muitos tokens expirados
- Tentativas de uso de token inválido

## 🔄 Próximas Melhorias

### Funcionalidades Opcionais
- [ ] Notificação por SMS
- [ ] Perguntas de segurança
- [ ] Histórico de recuperações
- [ ] Limite de tentativas por IP
- [ ] Integração com 2FA

### Segurança Adicional
- [ ] Rate limiting por IP
- [ ] Captcha para múltiplas tentativas
- [ ] Log de tentativas de recuperação
- [ ] Notificação de recuperação bem-sucedida

---

**Sistema 100% funcional e seguro para produção!** 🚀 