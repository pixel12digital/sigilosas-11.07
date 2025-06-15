'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface Usuario {
  id: number;
  email: string;
  tipo: 'admin' | 'editora';
  acompanhante_id?: number;
  nome_acompanhante?: string;
  criado_em: string;
}

interface Acompanhante {
  id: number;
  nome: string;
}

export default function UsuariosPage() {
  const [usuarios, setUsuarios] = useState<Usuario[]>([]);
  const [acompanhantes, setAcompanhantes] = useState<Acompanhante[]>([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [formData, setFormData] = useState({
    email: '',
    senha: '',
    tipo: 'admin' as 'admin' | 'editora',
    acompanhante_id: ''
  });

  useEffect(() => {
    fetchUsuarios();
    fetchAcompanhantes();
  }, []);

  const fetchUsuarios = async () => {
    try {
      const { data, error } = await supabase
        .from('usuarios')
        .select(`
          id,
          email,
          tipo,
          acompanhante_id,
          criado_em,
          acompanhantes!inner(nome)
        `)
        .order('id', { ascending: false });

      if (error) throw error;

      const usuariosFormatados = data?.map(usuario => ({
        ...usuario,
        nome_acompanhante: usuario.acompanhantes?.nome
      })) || [];

      setUsuarios(usuariosFormatados);
    } catch (error) {
      console.error('Erro ao buscar usuários:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchAcompanhantes = async () => {
    try {
      const { data, error } = await supabase
        .from('acompanhantes')
        .select('id, nome')
        .order('nome');

      if (error) throw error;
      setAcompanhantes(data || []);
    } catch (error) {
      console.error('Erro ao buscar acompanhantes:', error);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      const { error } = await supabase
        .from('usuarios')
        .insert({
          email: formData.email.trim(),
          senha: formData.senha, // Em produção, deve ser hasheada
          tipo: formData.tipo,
          acompanhante_id: formData.tipo === 'editora' && formData.acompanhante_id 
            ? parseInt(formData.acompanhante_id) 
            : null,
          criado_em: new Date().toISOString()
        });

      if (error) throw error;

      setMessage('Usuário adicionado com sucesso!');
      setFormData({ email: '', senha: '', tipo: 'admin', acompanhante_id: '' });
      fetchUsuarios();
    } catch (error) {
      console.error('Erro ao adicionar usuário:', error);
      setMessage('Erro ao adicionar usuário. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Tem certeza que deseja excluir este usuário?')) return;

    try {
      const { error } = await supabase
        .from('usuarios')
        .delete()
        .eq('id', id);

      if (error) throw error;

      setMessage('Usuário excluído com sucesso!');
      fetchUsuarios();
    } catch (error) {
      console.error('Erro ao excluir usuário:', error);
      setMessage('Erro ao excluir usuário. Tente novamente.');
    }
  };

  if (loading && usuarios.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando usuários...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Gerenciar Usuários</h1>
        <p className="text-gray-600">Adicione e gerencie usuários do sistema</p>
      </div>

      {/* Formulário para adicionar usuário */}
      <div className="max-w-md mx-auto bg-white rounded-xl shadow-lg p-7">
        <h2 className="text-xl font-bold text-[#2E1530] mb-4">Adicionar Novo Usuário</h2>
        
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Email:
            </label>
            <input
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Senha:
            </label>
            <input
              type="password"
              value={formData.senha}
              onChange={(e) => setFormData({ ...formData, senha: e.target.value })}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Tipo:
            </label>
            <select
              value={formData.tipo}
              onChange={(e) => setFormData({ ...formData, tipo: e.target.value as 'admin' | 'editora' })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="admin">Administrador</option>
              <option value="editora">Editora/Acompanhante</option>
            </select>
          </div>

          {formData.tipo === 'editora' && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Acompanhante:
              </label>
              <select
                value={formData.acompanhante_id}
                onChange={(e) => setFormData({ ...formData, acompanhante_id: e.target.value })}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Selecione uma acompanhante</option>
                {acompanhantes.map((acompanhante) => (
                  <option key={acompanhante.id} value={acompanhante.id}>
                    {acompanhante.nome}
                  </option>
                ))}
              </select>
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-6 rounded-md font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            {loading ? 'Adicionando...' : 'Adicionar Usuário'}
          </button>
        </form>

        {message && (
          <div className="mt-4 p-3 bg-green-100 text-green-700 rounded-md text-center font-semibold">
            {message}
          </div>
        )}
      </div>

      {/* Tabela de usuários */}
      <div className="bg-white rounded-xl shadow-lg overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  ID
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Email
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acompanhante
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Criado em
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {usuarios.map((usuario) => (
                <tr key={usuario.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {usuario.id}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {usuario.email}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      usuario.tipo === 'admin' 
                        ? 'bg-blue-100 text-blue-800' 
                        : 'bg-yellow-100 text-yellow-800'
                    }`}>
                      {usuario.tipo === 'admin' ? 'Administrador' : 'Editora'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {usuario.nome_acompanhante || '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {new Date(usuario.criado_em).toLocaleDateString('pt-BR', {
                      day: '2-digit',
                      month: '2-digit',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      onClick={() => handleDelete(usuario.id)}
                      className="text-red-600 hover:text-red-900 transition-colors"
                      title="Excluir usuário"
                    >
                      🗑️
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Botão voltar */}
      <div className="text-center">
        <Link
          href="/painel"
          className="inline-flex items-center gap-2 bg-gray-600 text-white px-6 py-3 rounded-md font-semibold hover:bg-gray-700 transition-colors"
        >
          🏠 Voltar ao Dashboard
        </Link>
      </div>
    </div>
  );
} 