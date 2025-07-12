# Histórico: Vídeos Públicos de Perfil com Moderação

## 📋 Resumo Executivo
Implementação de sistema de vídeos públicos de perfil com moderação administrativa, mantendo o vídeo de verificação privado e separado.

---

## 🎯 Objetivo
Criar uma nova seção onde acompanhantes podem postar múltiplos vídeos que serão exibidos publicamente no perfil após aprovação do admin.

---

## 🔄 Fluxo Atual vs. Desejado

### **Atual (Vídeo de Verificação)**
- ✅ Privado (apenas admin vê)
- ✅ Upload único
- ✅ Validação de identidade
- ✅ Não aparece no perfil público

### **Desejado (Vídeos Públicos)**
- 🌐 Públicos (após aprovação)
- 📹 Múltiplos vídeos
- ⏳ Moderação obrigatória
- 👀 Exibição no perfil público

---

## 🗄️ Estrutura do Banco de Dados

### **Nova Tabela: `videos_perfil`**
```sql
CREATE TABLE videos_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acompanhante_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    aprovado_por INT DEFAULT NULL,
    aprovado_em DATETIME DEFAULT NULL,
    FOREIGN KEY (acompanhante_id) REFERENCES acompanhantes(id)
);
```

### **Campos Explicados**
- `id`: Identificador único do vídeo
- `acompanhante_id`: Relacionamento com a acompanhante
- `url`: Caminho do arquivo de vídeo
- `status`: Estado da moderação (pendente/aprovado/rejeitado)
- `criado_em`: Data/hora do upload
- `aprovado_por`: ID do admin que aprovou
- `aprovado_em`: Data/hora da aprovação

---

## 🛠️ Implementação Técnica

### **1. Painel da Acompanhante**
**Arquivo:** `acompanhante/perfil.php`

#### **Funcionalidades:**
- Seção "Meus Vídeos" com upload múltiplo
- Listagem de vídeos com status
- Possibilidade de excluir vídeos próprios (pendentes/rejeitados)
- Preview dos vídeos enviados

#### **Upload:**
```javascript
// Upload via AJAX/Fetch
fetch(SITE_URL + '/api/upload-video-perfil.php', {
    method: 'POST',
    body: formData
})
```

#### **Exibição:**
- Status visual (pendente = amarelo, aprovado = verde, rejeitado = vermelho)
- Contador de vídeos por status
- Botão de exclusão para vídeos próprios

### **2. Painel Admin**
**Arquivo:** `admin/videos-pendentes.php` (novo)

#### **Funcionalidades:**
- Listagem de vídeos pendentes de moderação
- Visualização do vídeo antes da decisão
- Botões: Aprovar / Rejeitar / Excluir
- Filtros por status e acompanhante

#### **Ações:**
- **Aprovar:** Status → "aprovado", vídeo aparece no perfil público
- **Rejeitar:** Status → "rejeitado", acompanhante pode excluir
- **Excluir:** Remove vídeo permanentemente

### **3. Perfil Público**
**Arquivo:** `pages/acompanhante.php`

#### **Exibição:**
- Apenas vídeos com status "aprovado"
- Player HTML5 responsivo
- Grid ou carrossel de vídeos
- Contador de vídeos disponíveis

---

## 📁 Estrutura de Arquivos

### **Novos Arquivos:**
```
api/
├── upload-video-perfil.php          # Upload de vídeos públicos
├── listar-videos-perfil.php         # Listar vídeos da acompanhante
└── aprovar-video.php               # Aprovação/rejeição pelo admin

admin/
├── videos-pendentes.php            # Moderação de vídeos
└── videos-aprovados.php           # Lista de vídeos aprovados

uploads/
└── videos/                        # Pasta para vídeos públicos
    ├── acompanhante_1/
    ├── acompanhante_2/
    └── ...
```

### **Arquivos Modificados:**
```
acompanhante/
└── perfil.php                     # Adicionar seção de vídeos

pages/
└── acompanhante.php              # Exibir vídeos aprovados
```

---

## 🎨 Interface do Usuário

### **Painel Acompanhante:**
```
┌─────────────────────────────────────┐
│ 📹 Meus Vídeos                      │
├─────────────────────────────────────┤
│ [Upload Vídeo] [Selecionar Arquivo] │
├─────────────────────────────────────┤
│ 🟡 Vídeo 1 - Pendente     [🗑️]     │
│ 🟢 Vídeo 2 - Aprovado     [👁️]     │
│ 🔴 Vídeo 3 - Rejeitado    [🗑️]     │
└─────────────────────────────────────┘
```

### **Painel Admin:**
```
┌─────────────────────────────────────┐
│ 📹 Vídeos Pendentes de Moderação    │
├─────────────────────────────────────┤
│ [Filtrar por Acompanhante] [Status] │
├─────────────────────────────────────┤
│ 👤 Ariel - Vídeo 1                  │
│ [▶️ Ver] [✅ Aprovar] [❌ Rejeitar] │
├─────────────────────────────────────┤
│ 👤 Maria - Vídeo 2                  │
│ [▶️ Ver] [✅ Aprovar] [❌ Rejeitar] │
└─────────────────────────────────────┘
```

### **Perfil Público:**
```
┌─────────────────────────────────────┐
│ 📹 Vídeos (3)                       │
├─────────────────────────────────────┤
│ [▶️] [▶️] [▶️] [▶️] [▶️] [▶️]      │
│ Vídeo 1    Vídeo 2    Vídeo 3      │
└─────────────────────────────────────┘
```

---

## 🔧 Detalhes Técnicos

### **Upload de Vídeos:**
- **Formato:** MP4, AVI, MOV (máx. 50MB)
- **Duração:** Máximo 5 minutos
- **Resolução:** Mínimo 480p
- **Pasta:** `/uploads/videos/acompanhante_[ID]/`

### **Segurança:**
- Validação de tipo de arquivo
- Verificação de tamanho
- Sanitização de nomes
- Controle de acesso por sessão

### **Performance:**
- Compressão automática de vídeos
- Thumbnails gerados automaticamente
- Lazy loading no perfil público
- Cache de listagens

---

## 📊 Status e Controles

### **Status dos Vídeos:**
- 🟡 **Pendente:** Aguardando moderação
- 🟢 **Aprovado:** Disponível no perfil público
- 🔴 **Rejeitado:** Não aprovado, pode ser excluído

### **Permissões:**
- **Acompanhante:** Upload, visualizar próprios, excluir pendentes/rejeitados
- **Admin:** Visualizar todos, aprovar, rejeitar, excluir qualquer
- **Público:** Visualizar apenas aprovados

---

## 🚀 Próximos Passos

### **Fase 1: Estrutura Base**
1. ✅ Criar tabela `videos_perfil`
2. 🔄 API de upload de vídeos
3. 🔄 Listagem no painel acompanhante
4. 🔄 Moderação no painel admin

### **Fase 2: Interface**
1. 🔄 Seção "Meus Vídeos" no perfil
2. 🔄 Página de moderação admin
3. 🔄 Exibição no perfil público

### **Fase 3: Melhorias**
1. 🔄 Notificações de novos vídeos
2. 🔄 Filtros avançados
3. 🔄 Estatísticas de moderação

---

## 📝 Notas Importantes

### **Separação de Responsabilidades:**
- Vídeo de verificação = Privado, validação de identidade
- Vídeos de perfil = Públicos, conteúdo promocional

### **Moderação:**
- Obrigatória para todos os vídeos
- Admin pode aprovar ou rejeitar
- Acompanhante recebe feedback do status

### **Performance:**
- Vídeos são otimizados automaticamente
- Thumbnails gerados para preview
- Lazy loading implementado

---

## 🔗 Links Relacionados

- **Vídeo de Verificação:** `/api/upload-video-verificacao.php`
- **Perfil Acompanhante:** `/acompanhante/perfil.php`
- **Admin Visualizar:** `/admin/acompanhante-visualizar.php`
- **Perfil Público:** `/pages/acompanhante.php`

---

*Última atualização: [Data atual]*
*Versão: 1.0* 