'use client';

import { useState, useEffect } from 'react';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { Database } from '@/lib/database.types';

// Tipos atualizados para corresponder ao esquema do banco de dados
type AcompanhanteStatus = 'aprovado' | 'pendente' | 'rejeitado' | 'bloqueado';

interface Acompanhante {
  id: string; // ID é UUID (string)
  nome: string;
  email: string | null;
  telefone: string | null;
  cidade: string | null;
  status: AcompanhanteStatus;
  created_at: string | null; // Corrigido de criado_em para created_at
  foto_perfil: string | null;
  fotos: {
    id: string;
    url: string;
    storage_path: string;
    tipo: string;
    principal: boolean;
  }[];
  videos_verificacao: {
    id: string;
    url: string;
    storage_path: string;
  }[];
  documentos_acompanhante: {
    id: string;
    url: string;
    storage_path: string;
    tipo: string;
  }[];
}

export default function AcompanhantesPage() {
  const [acompanhantes, setAcompanhantes] = useState<Acompanhante[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'todos' | AcompanhanteStatus>('todos');
  const router = useRouter();
  const supabase = createClientComponentClient<Database>();

  useEffect(() => {
    fetchAcompanhantes();
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
          status,
          created_at, 
          email,
          cidades ( nome ),
          fotos ( id, url, storage_path, tipo, principal ),
          videos_verificacao ( id, url, storage_path ),
          documentos_acompanhante ( id, url, storage_path, tipo )
        `)
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Erro ao buscar acompanhantes:', error);
        throw error;
      }

      // Mapeamento corrigido para lidar com a nova estrutura da consulta
      const acompanhantesMapeadas = (data || []).map(item => {
        // Acessa o nome da cidade através do objeto aninhado
        const cidadeNome = item.cidades?.nome || 'N/A';
        
        // Busca a foto de perfil
        const fotoPerfilObj = item.fotos?.find(foto => foto.tipo === 'perfil');
        const fotoPerfil = fotoPerfilObj?.url || null;
          
        return {
          id: item.id,
          nome: item.nome,
          telefone: item.telefone || '',
          cidade: cidadeNome,
          status: item.status as AcompanhanteStatus,
          created_at: item.created_at || '',
          email: item.email || '',
          foto_perfil: fotoPerfil,
          fotos: item.fotos || [],
          videos_verificacao: item.videos_verificacao || [],
          documentos_acompanhante: item.documentos_acompanhante || [],
        };
      });

      setAcompanhantes(acompanhantesMapeadas);

    } catch (error) {
      console.error('Erro ao processar acompanhantes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (id: string, novoStatus: string) => {
    try {
      
      const { error } = await supabase
        .from('acompanhantes')
        .update({ status: novoStatus })
        .eq('id', id);

      if (error) {
        console.error('Erro detalhado do Supabase:', error);
        throw error;
      }

      await fetchAcompanhantes(); // Re-fetch para atualizar a UI
      alert('Status atualizado com sucesso!');
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
      alert('Erro ao atualizar status. Por favor, verifique o console para mais detalhes.');
    }
  };

  const handleDelete = async (id: string) => {
    if (confirm('Tem certeza que deseja excluir este cadastro? Esta ação é PERMANENTE e removerá o usuário da autenticação.')) {
      try {
        const response = await fetch('/api/excluir-perfil', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ userId: id }),
        });

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.error || 'Erro desconhecido ao excluir perfil.');
        }

        await fetchAcompanhantes(); // Re-fetch para atualizar a UI
        alert('Perfil e usuário excluídos com sucesso!');
        
      } catch (error) {
        console.error('Erro ao excluir:', error);
        const errorMessage = error instanceof Error ? error.message : 'Por favor, tente novamente.';
        alert(`Erro ao excluir o cadastro: ${errorMessage}`);
      }
    }
  };

  const handleApprove = async (id: string) => {
    if (confirm('Tem certeza que deseja aprovar este perfil?')) {
      try {
        await handleStatusChange(id, 'aprovado');
      } catch (error) {
        console.error('Erro ao aprovar o perfil:', error);
        alert('Erro ao aprovar o perfil.');
      }
    }
  };

  const handleBlock = async (id: string) => {
    if (confirm('Tem certeza que deseja bloquear este perfil?')) {
      try {
        await handleStatusChange(id, 'bloqueado');
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

  // Função para obter URL pública da foto
  const getFotoUrl = (fotoPath: string) => {
    if (!fotoPath) return '/assets/img/placeholder.svg';
    const { data: { publicUrl } } = supabase.storage
      .from('media')
      .getPublicUrl(fotoPath.split('/').pop() || '');
    return publicUrl || '/assets/img/placeholder.svg';
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
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nome
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cidade
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Criado em
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredAcompanhantes.map((acompanhante) => (
                <tr key={acompanhante.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{acompanhante.nome}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{acompanhante.cidade}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(acompanhante.status)}`}>
                      {acompanhante.status === 'aprovado' ? 'Aprovado' : acompanhante.status === 'pendente' ? 'Pendente' : acompanhante.status === 'bloqueado' ? 'Bloqueado' : 'Rejeitado'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {acompanhante.created_at
                      ? new Date(acompanhante.created_at).toLocaleString('pt-BR', {
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
                      <Link
                        href={`/painel/acompanhantes/${acompanhante.id}`}
                        className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center"
                      >
                        Revisar
                      </Link>
                      <button
                        onClick={() => handleApprove(acompanhante.id)}
                        className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                      >
                        Aprovar
                      </button>
                      <button
                        onClick={() => handleBlock(acompanhante.id)}
                        className="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700"
                      >
                        Bloquear
                      </button>
                      <button
                        onClick={() => handleDelete(acompanhante.id)}
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