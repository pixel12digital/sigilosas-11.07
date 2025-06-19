import { createRouteHandlerClient } from '@supabase/auth-helpers-nextjs'
import { cookies } from 'next/headers'
import { NextResponse } from 'next/server'
import { validarTelefone, formatarTelefone, validarEmail, sanitizeString, validatePhoto } from '@/lib/validation'

// Constantes de validação
const MAX_LENGTH = {
  nome: 100,
  descricao: 2000,
  endereco: 200,
  idiomas: 200,
  horario_expediente: 200,
  formas_pagamento: 200,
  genitalia_outro: 100,
  preferencia_sexual_outro: 100,
  cor_olhos: 50,
  estilo_cabelo: 50,
  tamanho_cabelo: 50
}

export async function POST(request: Request) {
  const supabase = createRouteHandlerClient({ cookies })
  let authData: any = null;
  let transaction: any = null;
  
  try {
    // Garantir que a requisição é JSON
    const contentType = request.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      return NextResponse.json({
        sucesso: false,
        erro: 'Content-Type deve ser application/json'
      }, { 
        status: 400,
        headers: {
          'Content-Type': 'application/json'
        }
      });
    }

    const formData = await request.json()
    console.log('=== INÍCIO DO PROCESSO DE CADASTRO ===')
    console.log('1. Dados recebidos do formulário:', {
      ...formData,
      senha: '[REDACTED]'
    })

    // Validações básicas
    const validationErrors = [];

    // Validar campos obrigatórios
    const requiredFields = {
      nome: 'Nome',
      email: 'E-mail',
      telefone: 'Telefone',
      cidade_id: 'Cidade',
      senha: 'Senha',
      genero: 'Gênero',
      idade: 'Idade'
    };

    Object.entries(requiredFields).forEach(([field, label]) => {
      if (!formData[field]) {
        validationErrors.push(`${label} é obrigatório`);
      }
    });

    // Validar tamanhos máximos
    Object.entries(MAX_LENGTH).forEach(([field, maxLength]) => {
      if (formData[field] && formData[field].length > maxLength) {
        validationErrors.push(`${field} deve ter no máximo ${maxLength} caracteres`);
      }
    });

    // Validar formato do telefone
    const telefoneError = validarTelefone(formData.telefone);
    if (telefoneError) {
      validationErrors.push(telefoneError);
    }

    // Validar idade mínima
    if (parseInt(formData.idade) < 18) {
      validationErrors.push('Idade mínima de 18 anos é obrigatória');
    }

    // Validar formato do email
    if (!validarEmail(formData.email)) {
      validationErrors.push('Email inválido');
    }

    // Validar fotos
    if (formData.foto && !validatePhoto(formData.foto)) {
      validationErrors.push('Foto principal inválida');
    }

    if (formData.galeria_fotos?.length > 0) {
      formData.galeria_fotos.forEach((foto: string, index: number) => {
        if (!validatePhoto(foto)) {
          validationErrors.push(`Foto ${index + 1} da galeria é inválida`);
        }
      });
    }

    if (validationErrors.length > 0) {
      return NextResponse.json({
        sucesso: false,
        erro: validationErrors.join('; ')
      }, { status: 400 });
    }

    // Verificar duplicatas
    try {
      const { data: existingUser, error: existingUserError } = await supabase
        .from('acompanhantes')
        .select('id')
        .eq('email', formData.email)
        .single();

      if (existingUserError && existingUserError.code !== 'PGRST116') {
        console.error('Erro ao verificar email duplicado:', existingUserError);
        throw new Error('Erro ao verificar disponibilidade do email');
      }

      if (existingUser) {
        return NextResponse.json({
          sucesso: false,
          erro: 'Este email já está cadastrado'
        }, { status: 400 });
      }

      const { data: existingPhone, error: existingPhoneError } = await supabase
        .from('acompanhantes')
        .select('id')
        .eq('telefone', formData.telefone)
        .single();

      if (existingPhoneError && existingPhoneError.code !== 'PGRST116') {
        console.error('Erro ao verificar telefone duplicado:', existingPhoneError);
        throw new Error('Erro ao verificar disponibilidade do telefone');
      }

      if (existingPhone) {
        return NextResponse.json({
          sucesso: false,
          erro: 'Este telefone já está cadastrado'
        }, { status: 400 });
      }
    } catch (error) {
      console.error('Erro ao verificar duplicatas:', error);
      throw new Error('Erro ao verificar dados duplicados');
    }

    // Criar usuário no auth com retry
    let authError;
    let attempts = 0;
    const maxAttempts = 3;
    const baseWaitTime = 10000; // 10 segundos base

    while (attempts < maxAttempts) {
      try {
        // Calcula o tempo de espera com backoff exponencial
        const waitTime = attempts === 0 ? 0 : baseWaitTime * Math.pow(2, attempts - 1);
        if (waitTime > 0) {
          console.log(`Aguardando ${waitTime/1000} segundos antes da tentativa ${attempts + 1}...`);
          await new Promise(resolve => setTimeout(resolve, waitTime));
        }

        const result = await supabase.auth.signUp({
          email: formData.email,
          password: formData.senha,
        });

        if (!result.error) {
          authData = result.data;
          break;
        }

        authError = result.error;
        
        if (!authError.message.toLowerCase().includes('rate limit')) {
          console.error('Erro não relacionado a rate limit:', authError);
          throw authError;
        }

        attempts++;
        console.log(`Tentativa ${attempts} falhou com rate limit`);
        
        if (attempts >= maxAttempts) {
          // Se atingiu o número máximo de tentativas, retorna 429 com headers
          const retryAfter = 60; // 1 minuto de espera após tentar todas as vezes
          return NextResponse.json({
            sucesso: false,
            erro: 'Sistema temporariamente indisponível. Por favor, tente novamente em alguns minutos.',
            retryAfter
          }, { 
            status: 429,
            headers: {
              'Content-Type': 'application/json',
              'Retry-After': retryAfter.toString(),
              'X-RateLimit-Limit': maxAttempts.toString(),
              'X-RateLimit-Remaining': '0',
              'X-RateLimit-Reset': (Math.floor(Date.now() / 1000) + retryAfter).toString()
            }
          });
        }
      } catch (error) {
        console.error(`Erro na tentativa ${attempts + 1}:`, error);
        throw error;
      }
    }

    if (authError) {
      if (authError.message.toLowerCase().includes('rate limit')) {
        return NextResponse.json({
          sucesso: false,
          erro: 'Sistema temporariamente indisponível. Por favor, tente novamente em alguns minutos.'
        }, { status: 429 });
      }
      throw authError;
    }

    if (!authData?.user?.id) {
      console.error('Usuário criado sem ID:', authData);
      throw new Error('Erro ao criar usuário: ID não gerado');
    }

    try {
      // Iniciar transação
      transaction = await supabase.rpc('begin_transaction');

      // Sanitizar e preparar dados
      const acompanhanteData = {
        nome: sanitizeString(formData.nome),
        idade: parseInt(formData.idade),
        genero: formData.genero,
        genitalia: sanitizeString(formData.genitalia),
        genitalia_outro: sanitizeString(formData.genitalia_outro),
        preferencia_sexual: sanitizeString(formData.preferencia_sexual),
        preferencia_sexual_outro: sanitizeString(formData.preferencia_sexual_outro),
        peso: sanitizeString(formData.peso),
        altura: sanitizeString(formData.altura),
        etnia: sanitizeString(formData.etnia),
        cor_olhos: sanitizeString(formData.cor_olhos),
        estilo_cabelo: sanitizeString(formData.estilo_cabelo),
        tamanho_cabelo: sanitizeString(formData.tamanho_cabelo),
        tamanho_pe: sanitizeString(formData.tamanho_pe),
        silicone: Boolean(formData.silicone),
        tatuagens: Boolean(formData.tatuagens),
        piercings: Boolean(formData.piercings),
        fumante: sanitizeString(formData.fumante),
        idiomas: sanitizeString(formData.idiomas),
        endereco: sanitizeString(formData.endereco),
        cidade_id: formData.cidade_id,
        horario_expediente: sanitizeString(formData.horario_expediente),
        formas_pagamento: sanitizeString(formData.formas_pagamento),
        data_criacao: new Date().toISOString(),
        descricao: sanitizeString(formData.descricao),
        email: formData.email.toLowerCase(),
        telefone: formData.telefone,
        status: 'pendente',
        user_id: authData.user.id
      };

      // Inserir acompanhante
      const { data: acompanhante, error: acompanhanteError } = await supabase
        .from('acompanhantes')
        .insert([acompanhanteData])
        .select()
        .single();

      if (acompanhanteError) {
        console.error('Erro ao inserir acompanhante:', acompanhanteError);
        throw new Error('Erro ao cadastrar acompanhante no banco de dados');
      }

      // Inserir foto principal
      if (formData.foto) {
        const { error: fotoError } = await supabase
          .from('fotos')
          .insert({
            acompanhante_id: acompanhante.id,
            url: formData.foto,
            capa: true
          });

        if (fotoError) {
          console.error('Erro ao inserir foto principal:', fotoError);
          throw new Error('Erro ao salvar foto principal');
        }
      }

      // Inserir fotos da galeria
      if (formData.galeria_fotos?.length > 0) {
        const { error: galeriaError } = await supabase
          .from('fotos')
          .insert(
            formData.galeria_fotos.map((url: string) => ({
              acompanhante_id: acompanhante.id,
              url,
              capa: false
            }))
          );

        if (galeriaError) {
          console.error('Erro ao inserir fotos da galeria:', galeriaError);
          throw new Error('Erro ao salvar fotos da galeria');
        }
      }

      // Commit da transação
      await supabase.rpc('commit_transaction');

      console.log('=== FIM DO PROCESSO DE CADASTRO ===');

      return NextResponse.json({
        sucesso: true,
        acompanhante
      }, {
        headers: {
          'Content-Type': 'application/json'
        }
      });

    } catch (error) {
      console.error('Erro após criar usuário:', error);
      
      // Rollback da transação se existir
      if (transaction) {
        try {
          await supabase.rpc('rollback_transaction');
        } catch (rollbackError) {
          console.error('Erro ao fazer rollback:', rollbackError);
        }
      }

      // Se algo der errado após criar o usuário, tentar deletá-lo
      if (authData?.user?.id) {
        try {
          await supabase.auth.admin.deleteUser(authData.user.id);
          console.log('Usuário deletado após erro:', authData.user.id);
        } catch (deleteError) {
          console.error('Erro ao deletar usuário após erro:', deleteError);
        }
      }
      throw error;
    }

  } catch (error) {
    console.error('ERRO GERAL no processo de cadastro:', {
      message: error instanceof Error ? error.message : 'Erro desconhecido',
      stack: error instanceof Error ? error.stack : undefined
    });

    // Rollback da transação se existir
    if (transaction) {
      try {
        await supabase.rpc('rollback_transaction');
      } catch (rollbackError) {
        console.error('Erro ao fazer rollback:', rollbackError);
      }
    }

    // Se algo der errado e temos um usuário criado, tentar deletá-lo
    if (authData?.user?.id) {
      try {
        await supabase.auth.admin.deleteUser(authData.user.id);
        console.log('Usuário deletado após erro geral:', authData.user.id);
      } catch (deleteError) {
        console.error('Erro ao deletar usuário após erro geral:', deleteError);
      }
    }

    // Se for um erro conhecido, retornar a mensagem específica
    if (error instanceof Error) {
      return NextResponse.json({
        sucesso: false,
        erro: error.message
      }, { 
        status: 400,
        headers: {
          'Content-Type': 'application/json'
        }
      });
    }

    // Para erros desconhecidos, retornar mensagem genérica
    return NextResponse.json({
      sucesso: false,
      erro: 'Erro interno do servidor. Por favor, tente novamente em alguns minutos.'
    }, { 
      status: 500,
      headers: {
        'Content-Type': 'application/json'
      }
    });
  }
} 