import { supabaseAdmin } from '@/lib/supabase';
import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  // ADICIONA UMA VERIFICAÇÃO PARA O CLIENTE ADMIN DO SUPABASE
  if (!supabaseAdmin) {
    console.error('Erro: Variáveis de ambiente do Supabase (admin) não estão configuradas.');
    return NextResponse.json({
      success: false,
      error: 'Erro de configuração no servidor. O administrador foi notificado.',
    }, { status: 500 });
  }
  
  try {
    const body = await request.json();

    // Extrai todos os campos esperados pela função SQL.
    // É importante garantir que todos os campos obrigatórios estejam presentes.
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
      // Adicione aqui outros campos que você incluiu na função SQL
    } = body;

    // Validação básica para garantir que os campos essenciais não são nulos.
    if (!email || !senha || !nome) {
      return NextResponse.json({
        success: false, 
        error: 'Campos essenciais (e-mail, senha, nome) são obrigatórios.' 
      }, { status: 400 });
    }

    // Chama a função RPC (Remote Procedure Call) no Supabase.
    const { data, error } = await supabaseAdmin.rpc('handle_new_user_signup', {
      nome,
      email,
      senha,
      telefone,
      idade: parseInt(idade, 10), // Garante que a idade é um inteiro
      genero,
      cidade_id, // Passado como string (será convertido para uuid no DB)
      descricao,
      foto,
      galeria_fotos
      // Passe aqui os outros campos
    });

    if (error) {
      // Se houver um erro na execução da função, ele será capturado aqui.
      console.error('Erro ao chamar a função RPC:', error);
      
      // CRIA UM OBJETO DE ERRO SIMPLES PARA GARANTIR QUE SEJA ENVIADO AO NAVEGADOR
      const simplifiedError = {
        message: error.message,
        details: error.details,
        hint: error.hint,
        code: error.code,
      };

      return NextResponse.json({
        success: false, 
        error: simplifiedError // Retorna o objeto de erro detalhado
      }, { status: 500 });
    }

    // Se a função for executada com sucesso, retorna os dados (o ID do novo usuário).
    return NextResponse.json({ success: true, data });

  } catch (error: any) {
    console.error('Erro na rota de cadastro:', error);
      return NextResponse.json({
      success: false, 
      error: 'Ocorreu um erro interno no servidor.' 
    }, { status: 500 });
  }
} 