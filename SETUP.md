# 🚀 Guia de Configuração Inicial - Sigilosas VIP

## 📋 Checklist de Configuração

### 1. ✅ Dependências Instaladas
```bash
npm install
```
✅ **CONCLUÍDO** - Todas as dependências foram instaladas

### 2. 🔧 Configurar Supabase

#### 2.1 Criar Projeto no Supabase
1. Acesse [supabase.com](https://supabase.com)
2. Faça login/cadastro
3. Clique em "New Project"
4. Escolha uma organização
5. Digite um nome para o projeto (ex: "sigilosas-vip")
6. Escolha uma senha forte para o banco
7. Escolha uma região (recomendo São Paulo)
8. Clique em "Create new project"

#### 2.2 Executar Schema SQL
1. No painel do Supabase, vá em "SQL Editor"
2. Cole todo o conteúdo do arquivo `supabase-schema.sql`
3. Clique em "Run" para executar

#### 2.3 Obter Chaves de API
1. No painel do Supabase, vá em "Settings" > "API"
2. Copie as seguintes informações:
   - **Project URL** (ex: https://abc123.supabase.co)
   - **anon public** (chave anônima)
   - **service_role** (chave de serviço - mantenha segura!)

### 3. 🔐 Configurar Variáveis de Ambiente

#### 3.1 Arquivo Local (.env.local)
Crie um arquivo `.env.local` na raiz do projeto com:

```env
NEXT_PUBLIC_SUPABASE_URL=https://seu-projeto.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=sua-chave-anonima-aqui
SUPABASE_SERVICE_ROLE_KEY=sua-chave-service-role-aqui
```

#### 3.2 Vercel (para produção)
1. No painel do Vercel, vá em Settings > Environment Variables
2. Adicione as mesmas 3 variáveis

### 4. 🖼️ Configurar Imagens

#### 4.1 Upload de Imagens para Supabase Storage
1. No Supabase, vá em "Storage"
2. Crie um bucket chamado "images"
3. Configure as políticas de acesso:
   ```sql
   -- Permitir leitura pública
   CREATE POLICY "Public Access" ON storage.objects FOR SELECT USING (bucket_id = 'images');
   
   -- Permitir upload autenticado
   CREATE POLICY "Authenticated users can upload" ON storage.objects FOR INSERT WITH CHECK (bucket_id = 'images' AND auth.role() = 'authenticated');
   ```

#### 4.2 Atualizar URLs das Imagens
1. Faça upload das imagens antigas para o Supabase Storage
2. Atualize as URLs na tabela `configuracoes`:
   ```sql
   UPDATE configuracoes 
   SET valor = 'https://seu-projeto.supabase.co/storage/v1/object/public/images/logo.png'
   WHERE chave = 'logo';
   ```

### 5. 🗄️ Migração de Dados (Opcional)

Se quiser migrar dados do MySQL atual:

```bash
# Instalar dependência adicional
npm install mysql2

# Configurar conexão MySQL no script
# Editar scripts/migrate-data.js com suas credenciais

# Executar migração
node scripts/migrate-data.js
```

### 6. 🧪 Testar Localmente

```bash
npm run dev
```

Acesse: http://localhost:3000

### 7. 🚀 Deploy no Vercel

1. **Conectar ao GitHub:**
   - Faça push do código para um repositório GitHub
   - Conecte o repositório ao Vercel

2. **Configurar variáveis de ambiente no Vercel:**
   - Vá em Settings > Environment Variables
   - Adicione as 3 variáveis do Supabase

3. **Deploy:**
   - O Vercel detectará automaticamente que é Next.js
   - Clique em "Deploy"

## 🔍 Verificações Importantes

### ✅ Funcionalidades Básicas
- [ ] Página inicial carrega
- [ ] Menu mobile funciona
- [ ] Filtros funcionam
- [ ] Cards de acompanhantes aparecem
- [ ] Configurações carregam

### ✅ Banco de Dados
- [ ] Conexão com Supabase funciona
- [ ] Tabelas foram criadas
- [ ] Dados estão sendo carregados
- [ ] Imagens aparecem

### ✅ Performance
- [ ] Página carrega rapidamente
- [ ] Imagens otimizadas
- [ ] Sem erros no console

## 🆘 Solução de Problemas

### Erro de Conexão com Supabase
- Verifique as variáveis de ambiente
- Confirme se as chaves estão corretas
- Teste a conexão no painel do Supabase

### Imagens não Carregam
- Verifique se o bucket "images" existe
- Confirme as políticas de acesso
- Verifique as URLs no banco de dados

### Erro de Build
- Verifique se todas as dependências estão instaladas
- Confirme se o TypeScript está configurado
- Verifique os logs de erro

## 📞 Próximos Passos

Após a configuração inicial:

1. **Implementar autenticação** com Supabase Auth
2. **Criar painel administrativo**
3. **Implementar upload de imagens**
4. **Adicionar sistema de avaliações**
5. **Criar páginas de perfil**

---

**Status Atual:** ✅ Dependências instaladas, estrutura criada
**Próximo Passo:** Configurar Supabase e variáveis de ambiente 