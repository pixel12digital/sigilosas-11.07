import { createRouteHandlerClient } from '@supabase/auth-helpers-nextjs';
import { cookies } from 'next/headers';
import { NextResponse } from 'next/server';

export async function GET() {
  try {
    const supabase = createRouteHandlerClient({ cookies });

    // Verificar se o usuário está autenticado
    const { data: { user }, error: authError } = await supabase.auth.getUser();
    if (authError || !user) {
      return NextResponse.json(
        { error: 'Não autorizado' },
        { status: 401 }
      );
    }

    // Verificar se o usuário é admin
    const { data: adminData, error: adminError } = await supabase
      .from('admin')
      .select('id')
      .eq('usuario', user.user_metadata?.username || user.email)
      .single();

    if (adminError || !adminData) {
      return NextResponse.json(
        { error: 'Não autorizado - usuário não é administrador' },
        { status: 401 }
      );
    }

    const { data: cidades, error } = await supabase
      .from('vw_cidades_estados')
      .select('*')
      .order('cidade_estado');

    if (error) throw error;

    return NextResponse.json(cidades);
  } catch (error) {
    console.error('Erro ao buscar cidades:', error);
    return NextResponse.json({ error: 'Erro ao buscar cidades' }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const supabase = createRouteHandlerClient({ cookies });
    const { nome, estado } = await request.json();

    // Verificar se o usuário está autenticado
    const { data: { user }, error: authError } = await supabase.auth.getUser();
    if (authError || !user) {
      return NextResponse.json(
        { error: 'Não autorizado' },
        { status: 401 }
      );
    }

    // Verificar se o usuário é admin
    const { data: adminData, error: adminError } = await supabase
      .from('admin')
      .select('id')
      .eq('usuario', user.user_metadata?.username || user.email)
      .single();

    if (adminError || !adminData) {
      return NextResponse.json(
        { error: 'Não autorizado - usuário não é administrador' },
        { status: 401 }
      );
    }

    // Buscar o ID do estado
    const { data: estadoData, error: estadoError } = await supabase
      .from('estados')
      .select('id')
      .eq('nome', estado)
      .single();

    if (estadoError || !estadoData) {
      return NextResponse.json(
        { error: 'Estado não encontrado' },
        { status: 400 }
      );
    }

    // Inserir a cidade
    const { data: cidade, error: cidadeError } = await supabase
      .from('cidades')
      .insert([
        {
          nome,
          estado_id: estadoData.id
        }
      ])
      .select()
      .single();

    if (cidadeError) {
      if (cidadeError.code === '23505') { // Código de erro de unique violation
        return NextResponse.json(
          { error: 'Esta cidade já existe neste estado' },
          { status: 400 }
        );
      }
      throw cidadeError;
    }

    return NextResponse.json(cidade);
  } catch (error) {
    console.error('Erro ao adicionar cidade:', error);
    return NextResponse.json(
      { error: 'Erro ao adicionar cidade' },
      { status: 500 }
    );
  }
}

export async function DELETE(request: Request) {
  try {
    const supabase = createRouteHandlerClient({ cookies });
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');

    if (!id) {
      return NextResponse.json(
        { error: 'ID da cidade não fornecido' },
        { status: 400 }
      );
    }

    // Verificar se o usuário está autenticado
    const { data: { user }, error: authError } = await supabase.auth.getUser();
    if (authError || !user) {
      return NextResponse.json(
        { error: 'Não autorizado' },
        { status: 401 }
      );
    }

    // Verificar se o usuário é admin
    const { data: adminData, error: adminError } = await supabase
      .from('admin')
      .select('id')
      .eq('usuario', user.user_metadata?.username || user.email)
      .single();

    if (adminError || !adminData) {
      return NextResponse.json(
        { error: 'Não autorizado - usuário não é administrador' },
        { status: 401 }
      );
    }

    // Verificar se existem acompanhantes usando esta cidade
    const { count: acompanhantesCount, error: countError } = await supabase
      .from('acompanhantes')
      .select('*', { count: 'exact', head: true })
      .eq('cidade_id', id);

    if (countError) throw countError;

    if (acompanhantesCount && acompanhantesCount > 0) {
      return NextResponse.json(
        { error: 'Não é possível excluir esta cidade pois existem acompanhantes cadastrados nela' },
        { status: 400 }
      );
    }

    // Deletar a cidade
    const { error: deleteError } = await supabase
      .from('cidades')
      .delete()
      .eq('id', id);

    if (deleteError) throw deleteError;

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Erro ao deletar cidade:', error);
    return NextResponse.json(
      { error: 'Erro ao deletar cidade' },
      { status: 500 }
    );
  }
} 