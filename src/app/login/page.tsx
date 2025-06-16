'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

export default function LoginPage() {
  const [usuario, setUsuario] = useState('');
  const [senha, setSenha] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const res = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario, senha }),
      });

      const result = await res.json();

      if (!res.ok) {
        setError(result.error || 'Usuário ou senha inválidos!');
        return;
      }

      // Redirecionar para o painel
      router.push('/painel');
    } catch (error) {
      setError('Erro ao fazer login. Tente novamente.');
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
          <p className="text-red-600 text-center mb-4 text-lg">
            {error}
          </p>
        )}
        
        <input
          type="text"
          name="usuario"
          placeholder="Usuário"
          value={usuario}
          onChange={(e) => setUsuario(e.target.value)}
          required
          autoFocus
          className="w-full mb-4 px-4 py-3 rounded-lg border-[1.5px] border-[#B89A76] text-lg bg-[#f9f9f9] text-[#301732] outline-none transition-colors focus:border-[#301732]"
        />
        
        <input
          type="password"
          name="senha"
          placeholder="Senha"
          value={senha}
          onChange={(e) => setSenha(e.target.value)}
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