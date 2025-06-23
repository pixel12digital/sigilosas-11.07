'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const router = useRouter();
  const supabase = createClientComponentClient();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');

    try {
      console.log('🔄 Iniciando login com Supabase Auth...');
      const { data, error: signInError } = await supabase.auth.signInWithPassword({
        email,
        password
      });

      if (signInError) {
        console.error('❌ Erro no login:', signInError.message);
        if (signInError.message.includes('Invalid login credentials')) {
          setError('Email ou senha inválidos!');
        } else {
          setError('Erro ao fazer login. Tente novamente.');
        }
        return;
      }

      if (data?.session) {
        console.log('✅ Login bem-sucedido!');
        router.push('/painel');
        router.refresh();
      } else {
        setError('Ocorreu um erro desconhecido. Por favor, verifique suas credenciais.');
      }
    } catch (err) {
      console.error('❌ Erro inesperado:', err);
      setError('Erro inesperado. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-4">
      <div className="max-w-md w-full mx-auto">
        <div className="text-center mb-8">
            <Link href="/" className="inline-block">
                <img src="/assets/img/logo.png" alt="Sigilosas VIP" className="h-16 mx-auto" />
            </Link>
            <h2 className="mt-6 text-center text-3xl font-extrabold text-secondary">
            Acesse seu painel
            </h2>
            <p className="mt-2 text-center text-sm text-gray-600">
            Ou{' '}
            <Link href="/cadastro" className="font-medium text-primary hover:text-primary-hover">
                crie uma nova conta
            </Link>
            </p>
        </div>

        <form onSubmit={handleSubmit} className="bg-white shadow-lg rounded-lg p-8 space-y-6">
          {error && (
            <div className="w-full p-3 bg-red-100 border border-red-300 rounded-lg">
              <p className="text-red-700 text-center text-sm">{error}</p>
            </div>
          )}

          <div className="rounded-md shadow-sm -space-y-px">
            <div>
              <label htmlFor="email-address" className="sr-only">Email</label>
              <input
                id="email-address"
                name="email"
                type="email"
                autoComplete="email"
                required
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-secondary focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                placeholder="Seu e-mail"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />
            </div>
            <div>
              <label htmlFor="password-className="sr-only">Senha</label>
              <input
                id="password"
                name="password"
                type="password"
                autoComplete="current-password"
                required
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-secondary focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                placeholder="Sua senha"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
            </div>
          </div>

          <div>
            <button
              type="submit"
              disabled={loading}
              className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-hover disabled:opacity-50"
            >
              {loading ? 'Entrando...' : 'Entrar'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
} 