'use client';

import { useState, useEffect } from 'react';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { Database } from '@/lib/database.types';

interface Acompanhante {
  id: number;
  nome: string;
  email: string | null;
  telefone: string | null;
  cidade: string | null;
  status: 'aprovado' | 'pendente' | 'rejeitado' | 'bloqueado';
  criado_em: string | null;
  foto: string | null;
}

export default function AcompanhantesPage() {
  const [acompanhantes, setAcompanhantes] = useState<Acompanhante[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'todos' | 'aprovado' | 'pendente' | 'rejeitado' | 'bloqueado'>('todos');
  const router = useRouter();
  const supabase = createClientComponentClient<Database>();
  const [cidades, setCidades] = useState<{[key: number]: string}>({});

  const fetchCidades = async () => {
    try {
      const { data, error } = await supabase
        .from('cidades')
        .select('id, nome');

      if (error) throw error;

      const cidadesMap = (data || []).reduce((acc: {[key: number]: string}, cidade) => {
        acc[cidade.id] = cidade.nome;
        return acc;
      }, {});

      setCidades(cidadesMap);
    } catch (error) {
      console.error('Erro ao buscar cidades:', error);
    }
  };

  useEffect(() => {
    fetchAcompanhantes();
    fetchCidades();
  }, []);

  const fetchAcompanhantes = async () => {
    try {
      setLoading(true);
      
      const { data, error } = await supabase
        .from('acompanhantes')
        .select(`
          id,
          nome,
          telefone,
          cidade_id,
          status,
          criado_em,
          email,
          fotos (
            id,
            url,
            capa
          )
        `)
        .order('criado_em', { ascending: false });

      console.log('Resposta do Supabase:', { data, error });

      if (error) {
        console.error('Erro ao buscar acompanhantes:', error);
        throw error;
      }

      // Mapear os dados para o formato esperado pela interface
      setAcompanhantes((data || []).map(item => ({
        id: item.id,
        nome: item.nome,
        telefone: item.telefone || '',
        cidade: cidades[item.cidade_id || 0] || '',
        status: item.status,
        criado_em: item.criado_em || '',
        email: item.email || '',
        foto: item.fotos?.find(f => f.capa)?.url || item.fotos?.[0]?.url || ''
      })));
    } catch (error) {
      console.error('Erro ao buscar acompanhantes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (id: number, novoStatus: string) => {
    try {
      console.log('Tentando atualizar status:', { id, novoStatus });
      
      const { error } = await supabase
        .from('acompanhantes')
        .update({ status: novoStatus })
        .eq('id', id);

      if (error) {
        console.error('Erro detalhado do Supabase:', error);
        throw error;
      }

      console.log('Status atualizado com sucesso');
      fetchAcompanhantes();
      alert('Status atualizado com sucesso!');
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
      alert('Erro ao atualizar status. Por favor, verifique o console para mais detalhes.');
    }
  };

  const handleDelete = async (id: string) => {
    if (confirm('Tem certeza que deseja excluir este cadastro? Esta ação não pode ser desfeita.')) {
      try {
        // Excluir arquivos do storage
        const storagePromises = [
          supabase.storage.from('perfil').remove([`${id}/*`]),
          supabase.storage.from('documentos').remove([`${id}/*`]),
          supabase.storage.from('videos-verificacao').remove([`${id}/*`]),
          supabase.storage.from('galeria').remove([`${id}/*`])
        ];
        
        await Promise.all(storagePromises);

        // Excluir registros das tabelas
        const deletePromises = [
          supabase.from('fotos_galeria').delete().eq('acompanhante_id', id),
          supabase.from('documentos').delete().eq('acompanhante_id', id),
          supabase.from('videos_verificacao').delete().eq('acompanhante_id', id)
        ];

        await Promise.all(deletePromises);

        // Por último, excluir o registro principal
        const { error } = await supabase.from('acompanhantes').delete().eq('id', id);
        
        if (error) throw error;

        // Atualizar a lista sem recarregar a página
        fetchAcompanhantes();
        alert('Cadastro excluído com sucesso!');
      } catch (error) {
        console.error('Erro ao excluir:', error);
        alert('Erro ao excluir o cadastro. Por favor, tente novamente.');
      }
    }
  };

  const handleBlock = async (id: string) => {
    if (confirm('Tem certeza que deseja bloquear este perfil?')) {
      try {
        await handleStatusChange(Number(id), 'bloqueado');
      } catch (error) {
        console.error('Erro ao bloquear o perfil:', error);
        alert('Erro ao bloquear o perfil.');
      }
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
      case 'bloqueado': return 'bg-gray-300 text-gray-800';
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
        <button
          onClick={() => setFilter('bloqueado')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'bloqueado' 
              ? 'bg-gray-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Bloqueados ({acompanhantes.filter(a => a.status === 'bloqueado').length})
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
                      {acompanhante.status === 'aprovado' ? 'Aprovado' : acompanhante.status === 'pendente' ? 'Pendente' : acompanhante.status === 'bloqueado' ? 'Bloqueado' : 'Rejeitado'}
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
                    <div className="flex gap-2">
                      <button
                        onClick={() => router.push(`/painel/acompanhantes/${acompanhante.id}`)}
                        className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                      >
                        Revisar
                      </button>
                      <button
                        onClick={() => handleBlock(acompanhante.id.toString())}
                        className="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700"
                      >
                        Bloquear
                      </button>
                      <button
                        onClick={() => handleDelete(acompanhante.id.toString())}
                        className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                      >
                        Excluir
                      </button>
                    </div>
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