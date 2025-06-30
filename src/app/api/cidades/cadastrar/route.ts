import { createRouteHandlerClient } from '@supabase/auth-helpers-nextjs';
import { createClient } from '@supabase/supabase-js';
import { cookies } from 'next/headers';
import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  try {
    // Criar cliente com service role key
    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL!,
      process.env.SUPABASE_SERVICE_ROLE_KEY!
    );

    // Obter dados da requisição
    const { cidade, estado } = await request.json();

    if (!cidade || !estado) {
      return NextResponse.json({ 
        success: false, 
        error: 'Cidade e estado são obrigatórios' 
      }, { status: 400 });
    }

    // Buscar o id do estado pela sigla (UF)
    const { data: estadoData, error: estadoError } = await supabase
      .from('estados')
      .select('id')
      .eq('uf', estado)
      .single();

    if (estadoError || !estadoData) {
      return NextResponse.json({
        success: false,
        error: 'Estado não encontrado'
      }, { status: 400 });
    }

    // Verificar se a cidade já existe
    const { data: cidadeExistente, error: errorBusca } = await supabase
      .from('cidades')
      .select('id')
      .eq('nome', cidade)
      .eq('estado', estado)
      .limit(1);

    if (errorBusca) throw errorBusca;

    if (cidadeExistente && cidadeExistente.length > 0) {
      return NextResponse.json({ 
        success: false, 
        error: 'Esta cidade já está cadastrada neste estado' 
      }, { status: 400 });
    }

    // Criar registro para a cidade, incluindo estado_id
    const { error } = await supabase
      .from('cidades')
      .insert([{
        nome: cidade,
        estado: estado,
        estado_id: estadoData.id
      }]);

    if (error) throw error;

    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error('Erro ao cadastrar cidade:', error);
    return NextResponse.json({ 
      success: false, 
      error: error.message || 'Erro ao cadastrar cidade' 
    }, { status: 500 });
  }
}