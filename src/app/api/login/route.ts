import { createRouteHandlerClient } from '@supabase/auth-helpers-nextjs';
import { cookies } from 'next/headers';
import { NextRequest, NextResponse } from "next/server";

export async function POST(req: NextRequest) {
  try {
    console.log('🔄 Iniciando processo de login...');
    const { usuario, senha } = await req.json();
    const cookieStore = cookies();
    const supabase = createRouteHandlerClient({ cookies: () => cookieStore });

    // Tentar fazer login
    console.log('🔄 Tentando login com:', usuario);
    const { data: signInData, error: signInError } = await supabase.auth.signInWithPassword({
      email: usuario,
      password: senha
    });

    if (signInError) {
      // Se o erro for de usuário não existente, vamos criar
      if (signInError.message.includes('Invalid login credentials')) {
        console.log('🔄 Usuário não existe, criando...');
        const { data: signUpData, error: signUpError } = await supabase.auth.signUp({
          email: usuario,
          password: senha,
          options: {
            emailRedirectTo: `${req.nextUrl.origin}/auth/callback`,
            data: {
              role: 'admin'
            }
          }
        });

        if (signUpError) {
          console.error('❌ Erro ao criar usuário:', signUpError);
          return NextResponse.json({ error: "Erro ao criar usuário" }, { status: 500 });
        }

        if (signUpData.session) {
          console.log('✅ Usuário criado e logado com sucesso!');
          return NextResponse.json({ success: true });
        }

        console.log('✅ Usuário criado, aguardando confirmação de email...');
        return NextResponse.json({ 
          success: true,
          message: "Usuário criado! Por favor, confirme seu email."
        });
      }

      console.error('❌ Erro ao fazer login:', signInError);
      return NextResponse.json({ error: "Usuário ou senha inválidos!" }, { status: 401 });
    }

    console.log('✅ Login realizado com sucesso!');
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('❌ Erro no login:', error);
    return NextResponse.json({ error: "Erro interno do servidor" }, { status: 500 });
  }
} 