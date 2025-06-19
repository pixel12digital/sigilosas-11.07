# Configuração do Supabase Storage

Este documento explica como configurar os buckets do Supabase Storage para o projeto Sigilosas VIP.

## Buckets Configurados

### 1. Bucket `images` (Público)
- **Propósito**: Armazenar fotos de perfil e galeria
- **Acesso**: Público para leitura, upload permitido durante cadastro
- **Estrutura de pastas**:
  - `perfil/` - Fotos principais dos acompanhantes
  - `galeria/` - Fotos da galeria dos acompanhantes

### 2. Bucket `documents` (Privado)
- **Propósito**: Armazenar documentos pessoais
- **Acesso**: Privado, apenas para usuários autenticados
- **Estrutura de pastas**:
  - `documentos/` - Documentos de identificação

### 3. Bucket `videos` (Privado)
- **Propósito**: Armazenar vídeos de verificação
- **Acesso**: Privado, apenas para usuários autenticados
- **Estrutura de pastas**:
  - `videos-verificacao/` - Vídeos de verificação

## Configuração Automática

### Pré-requisitos
1. Ter as variáveis de ambiente configuradas:
   ```env
   NEXT_PUBLIC_SUPABASE_URL=sua_url_do_supabase
   SUPABASE_SERVICE_ROLE_KEY=sua_service_role_key
   ```

2. Instalar dependências:
   ```bash
   npm install
   ```

### Executar Configuração
```bash
npm run setup-storage
```

## Configuração Manual

Se preferir configurar manualmente, execute as queries SQL do arquivo `supabase-storage-setup.sql` no SQL Editor do Supabase.

## Políticas de Segurança

### Bucket `images`
- ✅ Leitura pública
- ✅ Upload durante cadastro (sem autenticação)
- ✅ Upload para usuários autenticados
- ✅ Atualização/deleção apenas pelo proprietário

### Bucket `documents`
- ❌ Leitura privada (apenas proprietário)
- ✅ Upload durante cadastro (sem autenticação)
- ✅ Upload para usuários autenticados
- ✅ Atualização/deleção apenas pelo proprietário

### Bucket `videos`
- ❌ Leitura privada (apenas proprietário)
- ✅ Upload durante cadastro (sem autenticação)
- ✅ Upload para usuários autenticados
- ✅ Atualização/deleção apenas pelo proprietário

## Validações de Arquivo

### Tamanhos Máximos
- **Fotos**: 5MB
- **Documentos**: 5MB
- **Vídeos**: 50MB

### Tipos Permitidos
- **Fotos**: image/jpeg, image/png, image/webp
- **Documentos**: application/pdf, image/jpeg, image/png
- **Vídeos**: video/mp4, video/webm, video/quicktime

## Estrutura de URLs

### URLs Públicas (Bucket `images`)
```
https://[project].supabase.co/storage/v1/object/public/images/perfil/[filename]
https://[project].supabase.co/storage/v1/object/public/images/galeria/[filename]
```

### URLs Privadas (Buckets `documents` e `videos`)
```
https://[project].supabase.co/storage/v1/object/sign/documents/documentos/[filename]
https://[project].supabase.co/storage/v1/object/sign/videos/videos-verificacao/[filename]
```

## Troubleshooting

### Erro: "Bucket not found"
- Verifique se o script de setup foi executado
- Confirme se as variáveis de ambiente estão corretas

### Erro: "Policy violation"
- Verifique se as políticas foram aplicadas corretamente
- Confirme se o usuário tem as permissões necessárias

### Erro: "File too large"
- Verifique se o arquivo não excede os limites configurados
- Confirme se a validação no frontend está funcionando

## Monitoramento

Para monitorar o uso do storage:
1. Acesse o Dashboard do Supabase
2. Vá para Storage > Buckets
3. Verifique o uso de cada bucket
4. Monitore as políticas de acesso

## Backup e Recuperação

### Backup
- Os arquivos são automaticamente replicados pelo Supabase
- Considere fazer backup regular das URLs dos arquivos no banco de dados

### Recuperação
- Em caso de perda de dados, restaure a partir do backup do banco
- Refaça o upload dos arquivos se necessário 