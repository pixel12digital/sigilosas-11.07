'use client';

import { useState, useEffect } from 'react';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import type { User } from '@supabase/supabase-js';
import Link from 'next/link';

type AppUser = User & {
  is_admin: boolean;
};

export default function UsuariosPage() {
  const [users, setUsers] = useState<AppUser[]>([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });

  const supabase = createClientComponentClient();

  const fetchUsers = async () => {
    setLoading(true);
    try {
      // Buscar todos os IDs de administradores
      const { data: admins, error: adminsError } = await supabase.from('admins').select('id');
      if (adminsError) throw adminsError;
      const adminIds = new Set(admins.map(a => a.id));

      // Usar a Edge Function para buscar todos os usuários
      const { data: usersData, error: usersError } = await supabase.functions.invoke('get-all-users');
      if (usersError) throw new Error(`Erro ao buscar usuários: ${usersError.message}`);

      // Filtrar apenas os administradores
      const combinedUsers: AppUser[] = usersData.users
        .filter((user: User) => adminIds.has(user.id))
        .map((user: User) => ({
          ...user,
          is_admin: true,
        }));

      setUsers(combinedUsers);
    } catch (err: any) {
      setError(err.message || 'Ocorreu um erro desconhecido ao carregar os usuários.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setMessage('');
    setError('');

    try {
        const { data, error } = await supabase.auth.admin.createUser({
            email: formData.email,
            password: formData.password,
            email_confirm: true,
        });
        if (error) throw error;
        // Já adiciona como admin
        const newUserId = data.user?.id;
        if (newUserId) {
          const { error: adminError } = await supabase.from('admins').insert({ id: newUserId });
          if (adminError) throw adminError;
        }
        setMessage('Administrador adicionado com sucesso!');
        setFormData({ email: '', password: '' });
        fetchUsers(); // Recarrega a lista
    } catch (err: any) {
        setError(err.message || 'Erro ao adicionar administrador.');
    } finally {
        setLoading(false);
    }
  };
  
  const toggleAdmin = async (user: AppUser) => {
    try {
        if (user.is_admin) {
            // Rebaixar para usuário normal
            const { error } = await supabase.from('admins').delete().eq('id', user.id);
            if (error) throw error;
            setMessage(`O usuário ${user.email} não é mais um administrador.`);
        } else {
            // Promover para admin
            const { error } = await supabase.from('admins').insert({ id: user.id });
            if (error) throw error;
            setMessage(`O usuário ${user.email} agora é um administrador.`);
        }
        fetchUsers(); // Recarrega a lista
    } catch(err: any) {
        setError(err.message || 'Ocorreu um erro ao alterar o status do usuário.');
    }
  };

  const handleDelete = async (userId: string) => {
    if (!confirm('Tem certeza que deseja excluir este usuário? Esta ação é irreversível.')) return;

    try {
        const { error } = await supabase.auth.admin.deleteUser(userId);
        if (error) throw error;
        
        setMessage('Usuário excluído com sucesso!');
        fetchUsers(); // Recarrega a lista
    } catch (err: any) {
        setError(err.message || 'Erro ao excluir usuário.');
    }
  };

  if (loading && users.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <p className="text-[#2E1530] text-lg">Carregando usuários...</p>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Gerenciar Usuários</h1>
        <p className="text-gray-600">Adicione e gerencie administradores do painel</p>
      </div>

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
              value={formData.password}
              onChange={(e) => setFormData({ ...formData, password: e.target.value })}
              required
              placeholder="Mínimo 6 caracteres"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-6 rounded-md font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            {loading ? 'Adicionando...' : 'Adicionar Usuário'}
          </button>
        </form>

        {message && <p className="mt-4 p-3 bg-green-100 text-green-700 rounded-md text-center">{message}</p>}
        {error && <p className="mt-4 p-3 bg-red-100 text-red-700 rounded-md text-center">{error}</p>}
      </div>

      <div className="bg-white rounded-xl shadow-lg overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {users.map((user) => (
                <tr key={user.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{user.email}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    {user.is_admin ? (
                        <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Administrador
                        </span>
                    ) : (
                        <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            Usuário
                        </span>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {new Date(user.created_at).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                    <button onClick={() => toggleAdmin(user)} className={`py-1 px-3 rounded-md text-xs ${user.is_admin ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white'}`}>
                      {user.is_admin ? 'Rebaixar' : 'Promover'}
                    </button>
                    <button onClick={() => handleDelete(user.id)} className="py-1 px-3 rounded-md text-xs bg-red-600 hover:bg-red-700 text-white">
                      Excluir
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
       <div className="text-center mt-6">
        <Link href="/painel" className="inline-block bg-gray-700 text-white py-2 px-6 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
          <span role="img" aria-label="Voltar">🏠</span> Voltar ao Dashboard
        </Link>
      </div>
    </div>
  );
} 