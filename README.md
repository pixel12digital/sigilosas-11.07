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