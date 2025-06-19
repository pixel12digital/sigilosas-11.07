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

    const { data: estados, error } = await supabase
      .from('estados')
      .select('*')
      .order('nome');

    if (error) throw error;

    return NextResponse.json(estados);
  } catch (error) {
    console.error('Erro ao buscar estados:', error);
    return NextResponse.json({ error: 'Erro ao buscar estados' }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const supabase = createRouteHandlerClient({ cookies });
    const { nome } = await request.json();

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

    // Inserir o estado
    const { data: estado, error: estadoError } = await supabase
      .from('estados')
      .insert([{ nome }])
      .select()
      .single();

    if (estadoError) {
      if (estadoError.code === '23505') { // Código de erro de unique violation
        return NextResponse.json(
          { error: 'Este estado já existe' },
          { status: 400 }
        );
      }
      throw estadoError;
    }

    return NextResponse.json(estado);
  } catch (error) {
    console.error('Erro ao adicionar estado:', error);
    return NextResponse.json(
      { error: 'Erro ao adicionar estado' },
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
        { error: 'ID do estado não fornecido' },
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

    // Verificar se existem cidades usando este estado
    const { count: cidadesCount, error: countError } = await supabase
      .from('cidades')
      .select('*', { count: 'exact', head: true })
      .eq('estado_id', id);

    if (countError) throw countError;

    if (cidadesCount && cidadesCount > 0) {
      return NextResponse.json(
        { error: 'Não é possível excluir este estado pois existem cidades cadastradas nele' },
        { status: 400 }
      );
    }

    // Deletar o estado
    const { error: deleteError } = await supabase
      .from('estados')
      .delete()
      .eq('id', id);

    if (deleteError) throw deleteError;

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Erro ao deletar estado:', error);
    return NextResponse.json(
      { error: 'Erro ao deletar estado' },
      { status: 500 }
    );
  }
} 