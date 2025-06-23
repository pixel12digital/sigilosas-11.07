'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface Denuncia {
  id: number;
  acompanhante_id: number;
  nome_acompanhante: string;
  denunciante_nome: string;
  denunciante_email: string;
  motivo: string;
  descricao: string;
  status: 'pendente' | 'analisando' | 'resolvido' | 'descartado';
  criado_em: string;
}

export default function DenunciasPage() {
  const [denuncias, setDenuncias] = useState<Denuncia[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'todos' | 'pendente' | 'analisando' | 'resolvido' | 'descartado'>('todos');

  useEffect(() => {
    fetchDenuncias();
  }, []);

  const fetchDenuncias = async () => {
    try {
      const { data, error } = await supabase
        .from('denuncias')
        .select(`
          id,
          acompanhante_id,
          nome_exibicao,
          motivo,
          descricao,
          status,
          created_at,
          nome_acompanhante,
          denunciante_email
        `)
        .order('created_at', { ascending: false });

      if (error) throw error;

      const denunciasFormatadas = data?.map(denuncia => ({
        ...denuncia,
        denunciante_nome: denuncia.nome_exibicao,
        criado_em: denuncia.created_at,
        nome_acompanhante: denuncia.nome_acompanhante ?? '',
        denunciante_email: denuncia.denunciante_email ?? '',
      })) || [];

      setDenuncias(denunciasFormatadas);
    } catch (error) {
      console.error('Erro ao buscar denúncias:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (id: number, novoStatus: string) => {
    try {
      const { error } = await supabase
        .from('denuncias')
        .update({ status: novoStatus })
        .eq('id', id);

      if (error) throw error;
      fetchDenuncias();
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
    }
  };

  const filteredDenuncias = denuncias.filter(denuncia => {
    if (filter === 'todos') return true;
    return denuncia.status === filter;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pendente': return 'bg-yellow-100 text-yellow-800';
      case 'analisando': return 'bg-blue-100 text-blue-800';
      case 'resolvido': return 'bg-green-100 text-green-800';
      case 'descartado': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando denúncias...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Gerenciar Denúncias</h1>
        <p className="text-gray-600">Analise e gerencie as denúncias recebidas</p>
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
          Todos ({denuncias.length})
        </button>
        <button
          onClick={() => setFilter('pendente')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'pendente' 
              ? 'bg-yellow-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Pendentes ({denuncias.filter(d => d.status === 'pendente').length})
        </button>
        <button
          onClick={() => setFilter('analisando')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'analisando' 
              ? 'bg-blue-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Analisando ({denuncias.filter(d => d.status === 'analisando').length})
        </button>
        <button
          onClick={() => setFilter('resolvido')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'resolvido' 
              ? 'bg-green-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Resolvidas ({denuncias.filter(d => d.status === 'resolvido').length})
        </button>
        <button
          onClick={() => setFilter('descartado')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'descartado' 
              ? 'bg-gray-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Descartadas ({denuncias.filter(d => d.status === 'descartado').length})
        </button>
      </div>

      {/* Lista de denúncias */}
      <div className="space-y-4">
        {filteredDenuncias.map((denuncia) => (
          <div key={denuncia.id} className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div className="flex-1">
                <div className="flex items-center gap-4 mb-2">
                  <h3 className="font-semibold text-lg text-[#2E1530]">
                    Denúncia contra {denuncia.nome_acompanhante}
                  </h3>
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(denuncia.status)}`}>
                    {denuncia.status === 'pendente' ? 'Pendente' : 
                     denuncia.status === 'analisando' ? 'Analisando' :
                     denuncia.status === 'resolvido' ? 'Resolvido' : 'Descartado'}
                  </span>
                </div>
                
                <div className="flex items-center gap-4 mb-2 text-sm text-gray-600">
                  <span>Denunciante: {denuncia.denunciante_nome}</span>
                  <span>Data: {new Date(denuncia.criado_em).toLocaleDateString('pt-BR')}</span>
                </div>
                
                <div className="mb-2">
                  <span className="font-medium text-gray-700">Motivo: </span>
                  <span className="text-gray-600">{denuncia.motivo}</span>
                </div>
                
                <p className="text-gray-700 bg-gray-50 p-3 rounded-lg">
                  "{denuncia.descricao}"
                </p>
              </div>
              
              <div className="flex flex-col gap-2 min-w-[200px]">
                <select
                  value={denuncia.status}
                  onChange={(e) => handleStatusChange(denuncia.id, e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                  <option value="pendente">Pendente</option>
                  <option value="analisando">Analisando</option>
                  <option value="resolvido">Resolvido</option>
                  <option value="descartado">Descartado</option>
                </select>
                
                <button
                  onClick={() => window.open(`/acompanhantes/${denuncia.acompanhante_id}`, '_blank')}
                  className="text-blue-600 hover:text-blue-900 transition-colors text-sm"
                >
                  👁️ Ver perfil da acompanhante
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>

      {filteredDenuncias.length === 0 && (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">Nenhuma denúncia encontrada com o filtro selecionado.</p>
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