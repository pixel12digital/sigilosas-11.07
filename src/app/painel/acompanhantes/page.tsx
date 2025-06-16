'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

interface Acompanhante {
  id: number;
  nome: string;
  email: string;
  telefone: string;
  cidade: string;
  status: 'aprovado' | 'pendente' | 'rejeitado';
  criado_em: string;
  cidades?: { nome: string };
}

export default function AcompanhantesPage() {
  const [acompanhantes, setAcompanhantes] = useState<Acompanhante[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'todos' | 'aprovado' | 'pendente' | 'rejeitado'>('todos');
  const router = useRouter();

  useEffect(() => {
    fetchAcompanhantes();
  }, []);

  const fetchAcompanhantes = async () => {
    try {
      const { data, error } = await supabase
        .from('acompanhantes')
        .select('id, nome, telefone, cidade_id, status, criado_em')
        .order('criado_em', { ascending: false });

      console.log('Acompanhantes do banco:', data, error);

      if (error) throw error;
      setAcompanhantes((data || []).map((item: any) => ({
        id: item.id,
        nome: item.nome,
        telefone: item.telefone,
        cidade: item.cidade_id,
        status: item.status,
        criado_em: item.criado_em || "",
      })));
    } catch (error) {
      console.error('Erro ao buscar acompanhantes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (id: number, novoStatus: string) => {
    try {
      const { error } = await supabase
        .from('acompanhantes')
        .update({ status: novoStatus })
        .eq('id', id);

      if (error) throw error;
      fetchAcompanhantes();
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
    }
  };

  const filteredAcompanhantes = acompanhantes.filter(acompanhante => {
    if (filter === 'todos') return true;
    return acompanhante.status === filter;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'aprovado': return 'bg-green-100 text-green-800';
      case 'pendente': return 'bg-yellow-100 text-yellow-800';
      case 'rejeitado': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando acompanhantes...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Gerenciar Acompanhantes</h1>
        <p className="text-gray-600">Aprove e gerencie os perfis das acompanhantes</p>
      </div>

      {/* Filtros */}
      <div className="flex flex-wrap gap-4 justify-center">
        <button
          onClick={() => setFilter('todos')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'todos' 
              ? 'bg-blue-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Todos ({acompanhantes.length})
        </button>
        <button
          onClick={() => setFilter('aprovado')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'aprovado' 
              ? 'bg-green-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Aprovados ({acompanhantes.filter(a => a.status === 'aprovado').length})
        </button>
        <button
          onClick={() => setFilter('pendente')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'pendente' 
              ? 'bg-yellow-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Pendentes ({acompanhantes.filter(a => a.status === 'pendente').length})
        </button>
        <button
          onClick={() => setFilter('rejeitado')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'rejeitado' 
              ? 'bg-red-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Rejeitados ({acompanhantes.filter(a => a.status === 'rejeitado').length})
        </button>
      </div>

      {/* Tabela de acompanhantes */}
      <div className="bg-white rounded-xl shadow-lg overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredAcompanhantes.map((acompanhante) => (
                <tr key={acompanhante.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{acompanhante.id}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{acompanhante.nome}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{acompanhante.telefone}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{acompanhante.cidade}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(acompanhante.status)}`}>
                      {acompanhante.status === 'aprovado' ? 'Aprovado' : acompanhante.status === 'pendente' ? 'Pendente' : 'Rejeitado'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {acompanhante.criado_em
                      ? new Date(acompanhante.criado_em).toLocaleString('pt-BR', {
                          day: '2-digit',
                          month: '2-digit',
                          year: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit'
                        })
                      : ''}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <button
                      onClick={() => router.push(`/painel/acompanhantes/${acompanhante.id}`)}
                      className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                    >
                      Revisar
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {filteredAcompanhantes.length === 0 && (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">Nenhuma acompanhante encontrada com o filtro selecionado.</p>
        </div>
      )}

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