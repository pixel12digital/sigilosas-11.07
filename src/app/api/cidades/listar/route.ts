import { createRouteHandlerClient } from '@supabase/auth-helpers-nextjs';
import { createClient } from '@supabase/supabase-js';
import { cookies } from 'next/headers';
import { NextResponse } from 'next/server';

export async function GET() {
  try {
    // Criar cliente com service role key
    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL!,
      process.env.SUPABASE_SERVICE_ROLE_KEY!
    );

    // Buscar todas as cidades
    const { data: cidades, error } = await supabase
      .from('cidades')
      .select('*')
      .order('estado', { ascending: true })
      .order('nome', { ascending: true });

    if (error) throw error;

    return NextResponse.json({ 
      success: true, 
      cidades: cidades || [] 
    });
  } catch (error: any) {
    console.error('Erro ao listar cidades:', error);
    return NextResponse.json({ 
      success: false, 
      error: error.message || 'Erro ao listar cidades' 
    }, { status: 500 });
  }
} 