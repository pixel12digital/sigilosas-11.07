# Sigilosas VIP - Migração para Vercel + Supabase

Este projeto foi migrado de PHP/MySQL para Next.js/Vercel com Supabase como banco de dados.

## 🚀 Etapas para Deploy

### 1. Configuração do Supabase

1. **Criar projeto no Supabase:**
   - Acesse [supabase.com](https://supabase.com)
   - Crie um novo projeto
   - Anote a URL e as chaves de API

2. **Executar o schema:**
   ```bash
   # No painel do Supabase, vá em SQL Editor
   # Cole e execute o conteúdo do arquivo supabase-schema.sql
   ```

3. **Configurar variáveis de ambiente:**
   ```bash
   # No painel do Supabase > Settings > API
   # Copie as seguintes variáveis:
   NEXT_PUBLIC_SUPABASE_URL=sua_url_do_supabase
   NEXT_PUBLIC_SUPABASE_ANON_KEY=sua_chave_anonima
   SUPABASE_SERVICE_ROLE_KEY=sua_chave_service_role
   ```

### 2. Configuração Local

1. **Instalar dependências:**
   ```bash
   npm install
   ```

2. **Configurar variáveis de ambiente local:**
   ```bash
   # Crie um arquivo .env.local
   NEXT_PUBLIC_SUPABASE_URL=sua_url_do_supabase
   NEXT_PUBLIC_SUPABASE_ANON_KEY=sua_chave_anonima
   SUPABASE_SERVICE_ROLE_KEY=sua_chave_service_role
   ```

3. **Executar migração de dados (opcional):**
   ```bash
   # Instalar dependência adicional
   npm install mysql2
   
   # Executar migração
   node scripts/migrate-data.js
   ```

4. **Testar localmente:**
   ```bash
   npm run dev
   ```

### 3. Deploy no Vercel

1. **Conectar ao GitHub:**
   - Faça push do código para um repositório GitHub
   - Conecte o repositório ao Vercel

2. **Configurar variáveis de ambiente no Vercel:**
   - Vá em Settings > Environment Variables
   - Adicione as mesmas variáveis do Supabase

3. **Deploy:**
   - O Vercel detectará automaticamente que é um projeto Next.js
   - Clique em "Deploy"

## 📁 Estrutura do Projeto

```
src/
├── app/                    # App Router do Next.js 13+
│   ├── layout.tsx         # Layout principal
│   ├── page.tsx           # Página inicial
│   └── globals.css        # Estilos globais
├── components/            # Componentes React
│   ├── Header.tsx         # Cabeçalho
│   ├── Footer.tsx         # Rodapé
│   ├── AcompanhanteCard.tsx # Card de acompanhante
│   └── LoadingSpinner.tsx # Spinner de loading
├── lib/                   # Utilitários
│   └── supabase.ts        # Configuração do Supabase
└── types/                 # Tipos TypeScript
```

## 🔄 Principais Mudanças

### PHP → Next.js
- **Server-side rendering** com Next.js 13+ App Router
- **TypeScript** para type safety
- **Tailwind CSS** para estilização
- **Componentes React** reutilizáveis

### MySQL → Supabase
- **PostgreSQL** como banco de dados
- **Row Level Security (RLS)** para segurança
- **API REST automática** do Supabase
- **Real-time subscriptions** (opcional)

### Upload de Arquivos
- **Supabase Storage** para imagens e vídeos
- **Otimização automática** de imagens
- **CDN global** para performance

## 🛠️ Funcionalidades Implementadas

- ✅ Página inicial responsiva
- ✅ Sistema de filtros
- ✅ Cards de acompanhantes
- ✅ Configurações dinâmicas
- ✅ Menu mobile
- ✅ Loading states
- ✅ Error handling

## 🚧 Próximos Passos

1. **Implementar autenticação:**
   - Login/registro com Supabase Auth
   - Painel administrativo
   - Perfis de usuário

2. **Páginas adicionais:**
   - Perfil de acompanhante
   - Sistema de avaliações
   - Blog
   - Páginas estáticas

3. **Funcionalidades avançadas:**
   - Upload de imagens
   - Sistema de favoritos
   - Notificações
   - Chat

## 🔧 Comandos Úteis

```bash
# Desenvolvimento
npm run dev

# Build para produção
npm run build

# Testar build local
npm start

# Verificar tipos TypeScript
npm run type-check

# Lint do código
npm run lint
```

## 📝 Notas Importantes

1. **Migração de dados:** O script `migrate-data.js` migra dados do MySQL para Supabase
2. **Imagens:** As imagens antigas precisam ser re-uploadadas para o Supabase Storage
3. **URLs:** Atualize as URLs das imagens nas configurações após o upload
4. **SEO:** O Next.js oferece melhor SEO que PHP tradicional
5. **Performance:** Vercel + Supabase oferece melhor performance global

## 🆘 Suporte

Para dúvidas ou problemas:
1. Verifique os logs do Vercel
2. Consulte a documentação do Supabase
3. Verifique as variáveis de ambiente
4. Teste localmente antes do deploy

## 📄 Licença

Este projeto é privado e proprietário.

# Sigilosas VIP - Painel Administrativo

## Configuração Inicial

1. Clone o repositório
2. Instale as dependências:
```bash
npm install
```

3. Configure as variáveis de ambiente:
- Crie um arquivo `.env.local` na raiz do projeto com:
```
NEXT_PUBLIC_SUPABASE_URL=sua_url_do_supabase
NEXT_PUBLIC_SUPABASE_ANON_KEY=sua_chave_anonima_do_supabase
SUPABASE_SERVICE_ROLE_KEY=sua_chave_de_servico_do_supabase
```

4. Execute as migrações do banco de dados:
```bash
npx supabase migration up
```

5. Atualize a senha do admin:
```bash
node scripts/update-admin-password.js
```

6. Execute o projeto:
```bash
npm run dev
```

7. Acesse o painel:
- URL: http://localhost:3000/login
- Usuário: admin
- Senha: admin123

## Estrutura do Projeto

- `/src/app`: Páginas e rotas da aplicação
- `/src/components`: Componentes reutilizáveis
- `/src/lib`: Utilitários e configurações
- `/public`: Arquivos estáticos
- `/scripts`: Scripts de configuração
- `/supabase`: Configurações do Supabase

## Tecnologias

- Next.js 13 (App Router)
- Supabase (Banco de Dados)
- Tailwind CSS
- TypeScript 

# Sigilosas VIP - Sistema de Cadastro de Acompanhantes

Sistema completo para cadastro, gerenciamento e exibição de acompanhantes, desenvolvido com Next.js, TypeScript, Tailwind CSS e Supabase.

## 🚀 Funcionalidades

- **Cadastro de Acompanhantes**: Formulário completo com upload de fotos e vídeos
- **Painel Administrativo**: Aprovação, rejeição e gerenciamento de perfis
- **Sistema de Autenticação**: Login seguro com Supabase Auth
- **Upload de Mídia**: Fotos de perfil, galeria e vídeos de verificação
- **Gestão de Cidades**: Cadastro e gerenciamento de cidades por estado
- **Responsivo**: Interface adaptada para desktop e mobile

## 🛠️ Tecnologias

- **Frontend**: Next.js 14, TypeScript, Tailwind CSS
- **Backend**: Supabase (PostgreSQL, Auth, Storage)
- **Deploy**: Vercel
- **Upload**: Supabase Storage
- **Autenticação**: Supabase Auth

## 📋 Pré-requisitos

- Node.js 18+
- Conta no Supabase
- Conta no Vercel (para deploy)

## 🔧 Instalação

1. **Clone o repositório**
```bash
git clone https://github.com/seu-usuario/sigilosas-vercel.git
cd sigilosas-vercel
```

2. **Instale as dependências**
```bash
npm install
```

3. **Configure as variáveis de ambiente**
```bash
cp .env.example .env.local
```

Edite o arquivo `.env.local` com suas credenciais do Supabase:
```env
NEXT_PUBLIC_SUPABASE_URL=sua_url_do_supabase
NEXT_PUBLIC_SUPABASE_ANON_KEY=sua_chave_anonima
SUPABASE_SERVICE_ROLE_KEY=sua_chave_service_role
```

4. **Configure o banco de dados**
```bash
# Execute o script de correção da estrutura do banco
node scripts/fix-database.js

# Teste se tudo está funcionando
node scripts/test-database.js
```

5. **Execute o projeto**
```bash
npm run dev
```

## 🗄️ Estrutura do Banco de Dados

### Correções Implementadas

O projeto inclui correções automáticas para garantir que a estrutura do banco de dados esteja alinhada com a lógica da aplicação:

#### Problemas Corrigidos:
- **Inconsistências na tabela `cidades`**: Padronizada para usar `id SERIAL` e `estado_id INTEGER`
- **Inconsistências na tabela `acompanhantes`**: Padronizada para usar `cidade_id` e `estado_id`
- **Inconsistências na tabela `fotos`**: Padronizada para usar campo `principal` em vez de `capa`
- **Função SQL `handle_new_user_signup`**: Corrigida para alinhar com a estrutura das tabelas

#### Como Executar as Correções:

**Opção 1: Script Automático**
```bash
node scripts/fix-database.js
```

**Opção 2: SQL Manual**
```bash
# No Supabase Dashboard > SQL Editor
# Execute o conteúdo de: scripts/fix-database-structure.sql
```

**Opção 3: Via psql**
```bash
psql -h [HOST] -U [USER] -d [DATABASE] -f scripts/fix-database-structure.sql
```

#### Verificação Pós-Correção:
```bash
node scripts/test-database.js
```

## 📁 Estrutura do Projeto

```
src/
├── app/                    # App Router (Next.js 14)
│   ├── api/               # API Routes
│   │   ├── cadastro/      # Endpoint de cadastro
│   │   ├── cidades/       # Endpoints de cidades
│   │   └── ...
│   ├── cadastro/          # Página de cadastro
│   ├── login/             # Página de login
│   ├── painel/            # Painel administrativo
│   └── ...
├── components/            # Componentes React
├── lib/                   # Configurações e utilitários
└── ...
```

## 🔐 Configuração do Supabase

### 1. Criar Projeto
- Acesse [supabase.com](https://supabase.com)
- Crie um novo projeto
- Anote a URL e as chaves de API

### 2. Configurar Storage
```sql
-- Criar buckets para mídia
INSERT INTO storage.buckets (id, name, public) VALUES
('images', 'images', true),
('videos', 'videos', true),
('documents', 'documents', true);
```

### 3. Configurar Políticas RLS
As políticas de segurança são aplicadas automaticamente pelo script de correção.

## 🚀 Deploy

### Vercel
1. Conecte seu repositório ao Vercel
2. Configure as variáveis de ambiente
3. Deploy automático a cada push

### Variáveis de Ambiente no Vercel:
```env
NEXT_PUBLIC_SUPABASE_URL=sua_url_do_supabase
NEXT_PUBLIC_SUPABASE_ANON_KEY=sua_chave_anonima
SUPABASE_SERVICE_ROLE_KEY=sua_chave_service_role
```

## 📊 Funcionalidades do Painel

### Administrador
- **Dashboard**: Visão geral do sistema
- **Acompanhantes**: Lista, aprovação e rejeição de perfis
- **Cidades**: Cadastro e gerenciamento de cidades
- **Usuários**: Gestão de usuários do sistema
- **Configurações**: Configurações gerais

### Acompanhante
- **Perfil**: Visualização e edição do perfil
- **Mídia**: Upload de fotos e vídeos
- **Status**: Acompanhamento do status de aprovação

## 🔧 Scripts Disponíveis

```bash
# Desenvolvimento
npm run dev          # Inicia servidor de desenvolvimento
npm run build        # Build para produção
npm run start        # Inicia servidor de produção

# Banco de dados
node scripts/fix-database.js      # Corrige estrutura do banco
node scripts/test-database.js     # Testa estrutura do banco
node scripts/setup-database.js    # Configuração inicial
node scripts/setup-admin.js       # Cria usuário admin

# Cidades e Estados
node scripts/setup-estados.js     # Cadastra estados brasileiros
node scripts/setup-cidade-policies.js  # Configura políticas de cidades
```

## 🐛 Troubleshooting

### Problemas Comuns

1. **Erro de conexão com Supabase**
   - Verifique as variáveis de ambiente
   - Confirme se o projeto está ativo

2. **Erro na função SQL**
   - Execute o script de correção: `node scripts/fix-database.js`
   - Verifique se a função `exec_sql` existe

3. **Upload de arquivos falha**
   - Verifique se os buckets do Storage foram criados
   - Confirme as políticas RLS

4. **Erro de tipos TypeScript**
   - Execute `npm run build` para verificar tipos
   - Atualize as definições de tipos se necessário

### Logs e Debug
```bash
# Ver logs detalhados
npm run dev 2>&1 | tee logs.txt

# Testar estrutura do banco
node scripts/test-database.js
```

## 📝 Documentação Adicional

- [MIGRATION_STATUS.md](./MIGRATION_STATUS.md) - Status das migrações e correções
- [SETUP.md](./SETUP.md) - Guia detalhado de configuração
- [STORAGE_SETUP.md](./STORAGE_SETUP.md) - Configuração do Storage

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 📞 Suporte

Para suporte, abra uma issue no GitHub ou entre em contato através do email: suporte@sigilosas.com

---

**Desenvolvido com ❤️ para o projeto Sigilosas VIP** 