import { createRouteHandlerClient } from '@supabase/auth-helpers-nextjs';
import { cookies } from 'next/headers';
import { NextResponse } from 'next/server';
import { createClient } from '@supabase/supabase-js';

export const POST = async (request: Request) => {
  const { userId } = await request.json();

  if (!userId) {
    return NextResponse.json({ error: 'O ID do usuário é obrigatório' }, { status: 400 });
  }

  const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

  if (!supabaseUrl || !supabaseServiceKey) {
    return NextResponse.json({ error: 'Variáveis de ambiente do Supabase não configuradas' }, { status: 500 });
  }
  
  // Usar a chave de admin para ter permissões elevadas
  const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

  try {
    console.log(`Iniciando exclusão do perfil para o usuário: ${userId}`);

    // Etapa 1: Excluir registros de tabelas relacionadas (fotos, documentos, etc.)
    // O ideal é ter 'ON DELETE CASCADE' no banco, mas vamos garantir por aqui.
    const relatedTables = ['fotos', 'documentos_acompanhante', 'videos_verificacao'];
    for (const table of relatedTables) {
      const { error: deleteError } = await supabaseAdmin.from(table).delete().eq('acompanhante_id', userId);
      if (deleteError) {
        console.error(`Erro ao deletar da tabela ${table}:`, deleteError);
        // Decide se quer parar ou continuar. Por enquanto, vamos logar e continuar.
      } else {
        console.log(`Registros da tabela ${table} para o usuário ${userId} foram deletados.`);
      }
    }

    // Etapa 2: Excluir o perfil da tabela 'acompanhantes'
    const { error: profileError } = await supabaseAdmin.from('acompanhantes').delete().eq('id', userId);
    if (profileError) {
      console.error('Erro ao deletar o perfil da tabela acompanhantes:', profileError);
      throw new Error(`Falha ao deletar perfil: ${profileError.message}`);
    }
    console.log(`Perfil do usuário ${userId} deletado da tabela 'acompanhantes'.`);


    // Etapa 3: Excluir o usuário da autenticação (auth.users)
    const { data, error: authError } = await supabaseAdmin.auth.admin.deleteUser(userId);
    if (authError) {
      console.error('Erro ao deletar o usuário da autenticação:', authError);
      throw new Error(`Falha ao deletar usuário da autenticação: ${authError.message}`);
    }
    console.log(`Usuário ${userId} deletado da autenticação com sucesso.`);

    return NextResponse.json({ message: 'Perfil e usuário excluídos com sucesso' });

  } catch (error) {
    console.error('Erro geral no processo de exclusão:', error);
    const errorMessage = error instanceof Error ? error.message : 'Ocorreu um erro desconhecido';
    return NextResponse.json({ error: `Erro no processo de exclusão: ${errorMessage}` }, { status: 500 });
  }
}; 