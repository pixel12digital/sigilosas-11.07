# Planejamento e Checklist - Fluxo de Cadastro e Aprovação de Acompanhantes

## 1. Cadastro Público de Acompanhante
- [ ] Formulário público de cadastro (nome, email, senha, telefone, etc)
- [ ] Salvar usuário como "acompanhante" e status "pendente"
- [ ] Prevenção de duplicidade de email
- [ ] Redirecionar para painel restrito após cadastro
- [ ] Mensagem de boas-vindas e instrução para completar perfil

## 2. Painel da Acompanhante (Usuária)
- [ ] Login restrito (apenas para cadastradas)
- [ ] Permitir edição/complementação do perfil (dados pessoais, fotos, vídeos, documentos)
- [ ] Exibir status atual do perfil (pendente, aprovado, bloqueado)
- [ ] Bloquear acesso a recursos sensíveis se status for "bloqueado"
- [ ] Upload de fotos, vídeos e documentos
- [ ] Visualização do próprio perfil
- [ ] Notificação visual de "aguardando aprovação" se pendente

## 3. Moderação/Admin
- [ ] Listagem de perfis pendentes para aprovação
- [ ] Visualização detalhada do perfil da acompanhante
- [ ] Aprovar, bloquear ou solicitar ajustes no perfil
- [ ] Notificar acompanhante sobre aprovação/bloqueio (opcional)
- [ ] Logs de ações administrativas (opcional)

## 4. Exibição Pública
- [ ] Apenas acompanhantes com status "aprovado" aparecem nas buscas/resultados
- [ ] Filtros de busca funcionam corretamente
- [ ] Dados sensíveis não expostos publicamente

## 5. Segurança e Fluxo
- [ ] Senhas protegidas (hash)
- [ ] Validação de sessão/autorização em todas as rotas restritas
- [ ] Proteção contra acesso indevido a perfis de outros usuários
- [ ] Validação de arquivos de upload (tipo, tamanho, vírus)

## 6. Experiência do Usuário
- [ ] Mensagens claras em cada etapa (cadastro, pendente, aprovado, bloqueado)
- [ ] Feedback visual após ações (cadastro, upload, edição)
- [ ] Responsividade e acessibilidade

---

## Progresso
- [ ] Checklist revisado e aprovado
- [ ] Varredura inicial do projeto realizada
- [ ] Início da implementação

---

*Este arquivo será atualizado conforme o progresso e servirá como controle para não deixar nenhum ponto desapercebido.* 