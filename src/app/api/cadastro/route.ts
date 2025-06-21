import { supabaseAdmin } from '@/lib/supabase';
import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  // Garantir que o cliente admin do Supabase está disponível
  if (!supabaseAdmin) {
    console.error('Erro: Variáveis de ambiente do Supabase (admin) não estão configuradas.');
    return NextResponse.json({
      success: false,
      error: 'Erro de configuração no servidor. O administrador foi notificado.',
    }, { status: 500 });
  }
  
  try {
    const body = await request.json();

    // Extrair todos os campos do corpo da requisição
    const {
      email,
      senha,
      nome,
      telefone,
      idade,
      cidade_id,
      descricao,
      foto,
      galeria_fotos,
      video_url,
      documentos,
      // Campos de texto e numéricos
      genero,
      genitalia,
      preferencia_sexual,
      peso,
      altura,
      etnia,
      cor_olhos,
      estilo_cabelo,
      tamanho_cabelo,
      tamanho_pe,
      idiomas,
      endereco,
      clientes_em_conjunto,
      atende,
      horario_expediente,
      formas_pagamento,
      // Campos booleanos
      fumante,
      silicone,
      tatuagens,
      piercings,
    } = body;

    // Validação básica para garantir que os campos essenciais não são nulos.
    if (!email || !senha || !nome || !telefone || !idade || !genero || !cidade_id) {
      return NextResponse.json({
        success: false, 
        error: 'Campos essenciais (e-mail, senha, nome, telefone, idade, gênero, cidade) são obrigatórios.' 
      }, { status: 400 });
    }

    // Validação de idade
    if (idade < 18) {
      return NextResponse.json({
        success: false,
        error: 'A idade mínima é 18 anos.'
      }, { status: 400 });
    }

    // --- CONVERSÃO DE GÊNERO ---
    // Converte o gênero do formulário para o valor esperado pelo ENUM do banco de dados.
    const generoFormatado = genero ? String(genero).toLowerCase() : null;
    const generosValidos = ['feminino', 'masculino', 'trans', 'outro'];

    if (generoFormatado && !generosValidos.includes(generoFormatado)) {
      return NextResponse.json({
          success: false,
          error: `Valor de gênero inválido: ${genero}`
      }, { status: 400 });
    }

    // O Supabase espera que o telefone esteja no formato E.164 (ex: +5511999999999)
    // Aqui, apenas garantimos que não haja caracteres não numéricos.
    const telefoneFormatado = telefone.replace(/\D/g, '');

    // Usando o cliente admin para criar o usuário
    const { data, error } = await supabaseAdmin.auth.admin.createUser({
      email,
      password: senha,
      phone: telefoneFormatado,
      email_confirm: true, // Opcional: marque o e-mail como confirmado imediatamente
      user_metadata: {
        nome,
        idade: parseInt(idade, 10),
        genero: genero, // Manter o gênero original aqui
        cidade_id,
        descricao: descricao || '',
        foto: foto || null,
        galeria_fotos: galeria_fotos || [],
        video_url: video_url || null,
        documentos: documentos || [],
      },
    });

    if (error) {
      console.error('Erro ao criar usuário com Supabase Admin:', error);
      // Retorna o erro específico do Supabase para depuração no cliente.
      return NextResponse.json({
        success: false, 
        error: {
            message: error.message || "Erro ao criar usuário.",
            details: error.message,
            code: error.code || 'UNKNOWN',
            status: error.status || 500
        }
      }, { status: error.status || 500 });
    }

    if (!data.user) {
        console.error('CRÍTICO: Usuário não foi retornado após criação bem-sucedida.');
        return NextResponse.json({
            success: false,
            error: { message: 'Falha crítica ao obter dados do usuário após o cadastro.' }
        }, { status: 500 });
    }

    const userId = data.user.id;

    // --- NOVA LÓGICA COM FUNÇÃO TRANSACIONAL ---
    // Todos os dados do perfil e mídias são passados para uma única função no banco de dados,
    // garantindo que todas as inserções ocorram de forma atômica (ou tudo ou nada).
    const { error: perfilError } = await supabaseAdmin.rpc(
      'cadastrar_perfil_completo_de_acompanhante',
      {
        p_user_id: userId,
        p_email: email,
        p_nome: nome,
        p_telefone: telefoneFormatado,
        p_idade: parseInt(idade, 10),
        p_genero: generoFormatado,
        p_cidade_id: cidade_id,
        p_descricao: descricao || null,
        p_genitalia: genitalia || null,
        p_preferencia_sexual: preferencia_sexual || null,
        p_peso: peso ? parseFloat(peso) : null,
        p_altura: altura ? parseFloat(altura) : null,
        p_etnia: etnia || null,
        p_cor_dos_olhos: cor_olhos || null,
        p_estilo_cabelo: estilo_cabelo || null,
        p_tamanho_cabelo: tamanho_cabelo || null,
        p_tamanho_pe: tamanho_pe || null,
        p_fumante: !!fumante,
        p_silicone: !!silicone,
        p_tatuagens: !!tatuagens,
        p_piercings: !!piercings,
        p_idiomas: idiomas || null,
        p_endereco: endereco || null,
        p_atende: atende || null,
        p_horario_expediente: horario_expediente || null,
        p_formas_pagamento: formas_pagamento || null,
        p_clientes_em_conjunto: clientes_em_conjunto || null,
        p_foto_url: foto || null,
        p_galeria_fotos_urls: galeria_fotos || [],
        p_video_url: video_url || null,
        p_documentos: documentos || []
      }
    );

    if (perfilError) {
      // Se a função do DB falhar, a transação é revertida automaticamente.
      // A única ação necessária aqui é deletar o usuário órfão da autenticação.
      await supabaseAdmin.auth.admin.deleteUser(userId);
      console.error('Erro ao chamar RPC para criar perfil. Usuário órfão deletado.', perfilError);
      return NextResponse.json({
        success: false,
        error: { message: 'Não foi possível criar o perfil. O cadastro foi cancelado.', details: perfilError.message }
      }, { status: 500 });
    }

    return NextResponse.json({ 
      success: true, 
      data: {
        user: data.user,
        message: 'Cadastro realizado com sucesso! Sua conta foi criada e está pendente de aprovação.'
      }
    });

  } catch (error: any) {
    console.error('Erro GERAL na rota de cadastro:', error);
    const errorMessage = error instanceof Error ? error.message : "Um erro inesperado ocorreu.";
      return NextResponse.json({
      success: false, 
      error: {
        message: 'Ocorreu um erro interno no servidor.',
        details: errorMessage
      }
    }, { status: 500 });
  }
} 