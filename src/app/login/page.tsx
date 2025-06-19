'use client';

import { useState } from 'react';
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
      const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password
      });

      if (error) {
        console.error('❌ Erro no login:', error.message);
        if (error.message.includes('Invalid login credentials')) {
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
      }
    } catch (error) {
      console.error('❌ Erro inesperado:', error);
      setError('Erro inesperado. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#f8f8f8] flex items-center justify-center p-4">
      <form onSubmit={handleSubmit} className="max-w-[380px] w-full bg-white rounded-[18px] shadow-[0_4px_32px_rgba(184,154,118,0.12)] p-8 md:p-10 flex flex-col items-center">
        <h2 className="text-center mb-7 text-[#301732] text-3xl font-bold tracking-wide">
          Acesso ao Painel
        </h2>
        
        {error && (
          <div className="w-full mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p className="text-red-600 text-center text-lg">
              {error}
            </p>
          </div>
        )}

        {success && (
          <div className="w-full mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p className="text-green-600 text-center text-lg">
              {success}
            </p>
          </div>
        )}
        
        <input
          type="email"
          name="email"
          placeholder="E-mail"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          autoFocus
          className="w-full mb-4 px-4 py-3 rounded-lg border-[1.5px] border-[#B89A76] text-lg bg-[#f9f9f9] text-[#301732] outline-none transition-colors focus:border-[#301732]"
        />
        
        <input
          type="password"
          name="password"
          placeholder="Senha"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          className="w-full mb-4 px-4 py-3 rounded-lg border-[1.5px] border-[#B89A76] text-lg bg-[#f9f9f9] text-[#301732] outline-none transition-colors focus:border-[#301732]"
        />
        
        <button
          type="submit"
          disabled={loading}
          className="w-full py-3 bg-[#B89A76] text-white border-none rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-[#301732] disabled:opacity-50 disabled:cursor-not-allowed mt-2"
        >
          {loading ? 'Entrando...' : 'Entrar'}
        </button>
      </form>
    </div>
  );
} 