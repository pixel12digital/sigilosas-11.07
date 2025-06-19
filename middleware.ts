import { createMiddlewareClient } from '@supabase/auth-helpers-nextjs';
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export async function middleware(request: NextRequest) {
  const res = NextResponse.next();
  const supabase = createMiddlewareClient({ req: request, res });

  // Verificar se o usuário está autenticado
  const { data: { session }, error: sessionError } = await supabase.auth.getSession();

  // Se não estiver autenticado, redirecionar para o login
  if (!session) {
    const redirectUrl = request.nextUrl.clone();
    redirectUrl.pathname = '/login';
    redirectUrl.searchParams.set('redirectTo', request.nextUrl.pathname);
    return NextResponse.redirect(redirectUrl);
  }

  // Se estiver acessando uma rota do painel, verificar se é admin
  if (request.nextUrl.pathname.startsWith('/painel') || request.nextUrl.pathname.startsWith('/api/')) {
    const { data: adminData, error: adminError } = await supabase
      .from('admin')
      .select('id')
      .eq('usuario', session.user.email)
      .eq('ativo', true)
      .single();

    if (adminError || !adminData) {
      // Se não for admin, redirecionar para a página inicial
      const redirectUrl = request.nextUrl.clone();
      redirectUrl.pathname = '/';
      return NextResponse.redirect(redirectUrl);
    }
  }

  return res;
}

// Configurar quais rotas devem passar pelo middleware
export const config = {
  matcher: [
    '/painel/:path*',
    '/api/:path*'
  ]
};