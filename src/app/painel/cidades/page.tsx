'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface Cidade {
  id: number;
  nome: string;
  estado: string;
}

export default function CidadesPage() {
  const [cidades, setCidades] = useState<Cidade[]>([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [formData, setFormData] = useState({
    nome: '',
    estado: ''
  });
  const [editId, setEditId] = useState<number | null>(null);
  const [editData, setEditData] = useState<{ nome: string; estado: string }>({ nome: '', estado: '' });

  useEffect(() => {
    fetchCidades();
  }, []);

  const fetchCidades = async () => {
    try {
      const { data, error } = await supabase
        .from('cidades')
        .select('*')
        .order('nome');

      if (error) throw error;
      setCidades(data || []);
    } catch (error) {
      console.error('Erro ao buscar cidades:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    const nome = (formData.nome || '').trim();
    const estado = (formData.estado || '').trim();
    if (!nome || !estado) {
      setMessage('Preencha todos os campos.');
      setLoading(false);
      return;
    }
    try {
      const { error } = await supabase
        .from('cidades')
        .insert({
          nome,
          estado
        });
      if (error) throw error;
      setMessage('Cidade adicionada com sucesso!');
      setFormData({ nome: '', estado: '' });
      fetchCidades();
    } catch (error) {
      console.error('Erro ao adicionar cidade:', error);
      setMessage('Erro ao adicionar cidade. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Tem certeza que deseja excluir esta cidade?')) return;

    try {
      const { error } = await supabase
        .from('cidades')
        .delete()
        .eq('id', id);

      if (error) throw error;

      setMessage('Cidade excluída com sucesso!');
      fetchCidades();
    } catch (error) {
      console.error('Erro ao excluir cidade:', error);
      setMessage('Erro ao excluir cidade. Tente novamente.');
    }
  };

  const handleEdit = (cidade: Cidade) => {
    setEditId(cidade.id);
    setEditData({ nome: cidade.nome, estado: cidade.estado });
  };

  const handleSaveEdit = async (id: number) => {
    setLoading(true);
    const nome = (editData.nome || '').trim();
    const estado = (editData.estado || '').trim();
    if (!nome || !estado) {
      setMessage('Preencha todos os campos.');
      setLoading(false);
      return;
    }
    try {
      const cidadeOriginal = cidades.find(c => c.id === id);
      if (
        cidadeOriginal &&
        (cidadeOriginal.nome || '').trim() === nome &&
        (cidadeOriginal.estado || '').trim() === estado
      ) {
        setEditId(null);
        setEditData({ nome: '', estado: '' });
        setMessage('');
        setLoading(false);
        return;
      }
      const { data, error } = await supabase
        .from('cidades')
        .update({ nome, estado })
        .eq('id', id)
        .select();
      if (error) throw error;
      if (data && data.length > 0) {
        setMessage('Cidade editada com sucesso!');
      } else {
        setMessage('Nenhuma alteração realizada.');
      }
      setEditId(null);
      setEditData({ nome: '', estado: '' });
      fetchCidades();
    } catch (error: any) {
      setMessage('Erro ao editar cidade: ' + (error.message || JSON.stringify(error)));
    } finally {
      setLoading(false);
    }
  };

  const handleCancelEdit = () => {
    setEditId(null);
    setEditData({ nome: '', estado: '' });
    setMessage('');
  };

  if (loading && cidades.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando cidades...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Gerenciar Cidades</h1>
        <p className="text-gray-600">Adicione e gerencie as cidades disponíveis</p>
      </div>

      {/* Formulário para adicionar cidade */}
      <div className="max-w-md mx-auto bg-white rounded-xl shadow-lg p-7">
        <h2 className="text-xl font-bold text-[#2E1530] mb-4">Adicionar Nova Cidade</h2>
        
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Nome da Cidade:
            </label>
            <input
              type="text"
              value={formData.nome}
              onChange={(e) => setFormData({ ...formData, nome: e.target.value })}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Estado:
            </label>
            <input
              type="text"
              value={formData.estado}
              onChange={(e) => setFormData({ ...formData, estado: e.target.value })}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-6 rounded-md font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            {loading ? 'Adicionando...' : 'Adicionar Cidade'}
          </button>
        </form>

        {message && (
          <div className="mt-4 p-3 bg-green-100 text-green-700 rounded-md text-center font-semibold">
            {message}
          </div>
        )}
      </div>

      {/* Tabela de cidades */}
      <div className="bg-white rounded-xl shadow-lg overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  ID
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cidade
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {cidades.map((cidade) => (
                <tr key={cidade.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {cidade.id}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {editId === cidade.id ? (
                      <input
                        type="text"
                        value={editData.nome}
                        onChange={e => setEditData({ ...editData, nome: e.target.value })}
                        className="px-2 py-1 border rounded"
                      />
                    ) : cidade.nome}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {editId === cidade.id ? (
                      <input
                        type="text"
                        value={editData.estado}
                        onChange={e => setEditData({ ...editData, estado: e.target.value })}
                        className="px-2 py-1 border rounded"
                      />
                    ) : cidade.estado}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap flex gap-2 items-center">
                    {editId === cidade.id ? (
                      <>
                        <button
                          onClick={() => handleSaveEdit(cidade.id)}
                          className="text-green-600 hover:text-green-900 transition-colors font-bold"
                          title="Salvar edição"
                          disabled={loading}
                        >
                          💾
                        </button>
                        <button
                          onClick={handleCancelEdit}
                          className="text-gray-600 hover:text-gray-900 transition-colors font-bold"
                          title="Cancelar"
                          disabled={loading}
                        >
                          ✖
                        </button>
                      </>
                    ) : (
                      <>
                        <button
                          onClick={() => handleEdit(cidade)}
                          className="text-blue-600 hover:text-blue-900 transition-colors font-bold"
                          title="Editar cidade"
                        >
                          ✏️
                        </button>
                        <button
                          onClick={() => handleDelete(cidade.id)}
                          className="text-red-600 hover:text-red-900 transition-colors"
                          title="Excluir cidade"
                        >
                          🗑️
                        </button>
                      </>
                    )}
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