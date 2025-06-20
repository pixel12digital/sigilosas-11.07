import { supabaseAdmin } from '@/lib/supabase';
import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  if (!supabaseAdmin) {
    return NextResponse.json({
      success: false,
      error: { message: 'Erro de configuração no servidor.' },
    }, { status: 500 });
  }
  
  try {
    const body = await request.json();

    const {
      nome,
      email,
      senha,
      telefone,
      idade,
      genero,
      cidade_id,
      descricao,
      foto,
      galeria_fotos
    } = body;

    if (!email || !senha || !nome) {
      return NextResponse.json({ 
        success: false, 
        error: { message: 'Campos essenciais (e-mail, senha, nome) são obrigatórios.' }
      }, { status: 400 });
    }

    const { data, error } = await supabaseAdmin.rpc('handle_new_user_signup', {
      nome,
      email,
      senha,
      telefone,
      idade: parseInt(idade, 10),
      genero,
      cidade_id,
      descricao,
      foto,
      galeria_fotos
    });

    if (error) {
      const simplifiedError = {
        message: error.message,
        details: error.details,
        hint: error.hint,
        code: error.code,
      };
      return NextResponse.json({ success: false, error: simplifiedError }, { status: 500 });
    }

    return NextResponse.json({ success: true, data });
  } catch (error: any) {
    return NextResponse.json({ 
      success: false, 
      error: { message: 'Ocorreu um erro interno no servidor.' }
    }, { status: 500 });
  }
}