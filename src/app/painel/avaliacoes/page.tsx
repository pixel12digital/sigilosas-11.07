'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface Avaliacao {
  id: number;
  acompanhante_id: number;
  nome_acompanhante: string;
  cliente_nome: string;
  nota: number;
  comentario: string;
  status: 'pendente' | 'aprovado' | 'rejeitado';
  criado_em: string;
}

export default function AvaliacoesPage() {
  const [avaliacoes, setAvaliacoes] = useState<Avaliacao[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'todos' | 'pendente' | 'aprovado' | 'rejeitado'>('todos');

  useEffect(() => {
    fetchAvaliacoes();
  }, []);

  const fetchAvaliacoes = async () => {
    try {
      const { data, error } = await supabase
        .from('avaliacoes')
        .select(`
          id,
          acompanhante_id,
          cliente_nome,
          nota,
          comentario,
          status,
          criado_em,
          acompanhantes!inner(nome)
        `)
        .order('criado_em', { ascending: false });

      if (error) throw error;

      const avaliacoesFormatadas = data?.map(avaliacao => ({
        ...avaliacao,
        nome_acompanhante: avaliacao.acompanhantes?.nome
      })) || [];

      setAvaliacoes(avaliacoesFormatadas);
    } catch (error) {
      console.error('Erro ao buscar avaliações:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (id: number, novoStatus: string) => {
    try {
      const { error } = await supabase
        .from('avaliacoes')
        .update({ status: novoStatus })
        .eq('id', id);

      if (error) throw error;
      fetchAvaliacoes();
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
    }
  };

  const filteredAvaliacoes = avaliacoes.filter(avaliacao => {
    if (filter === 'todos') return true;
    return avaliacao.status === filter;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'aprovado': return 'bg-green-100 text-green-800';
      case 'pendente': return 'bg-yellow-100 text-yellow-800';
      case 'rejeitado': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const renderStars = (nota: number) => {
    return '⭐'.repeat(nota) + '☆'.repeat(5 - nota);
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando avaliações...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Gerenciar Avaliações</h1>
        <p className="text-gray-600">Modere as avaliações dos clientes</p>
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
          Todos ({avaliacoes.length})
        </button>
        <button
          onClick={() => setFilter('pendente')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'pendente' 
              ? 'bg-yellow-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Pendentes ({avaliacoes.filter(a => a.status === 'pendente').length})
        </button>
        <button
          onClick={() => setFilter('aprovado')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'aprovado' 
              ? 'bg-green-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Aprovadas ({avaliacoes.filter(a => a.status === 'aprovado').length})
        </button>
        <button
          onClick={() => setFilter('rejeitado')}
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${
            filter === 'rejeitado' 
              ? 'bg-red-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Rejeitadas ({avaliacoes.filter(a => a.status === 'rejeitado').length})
        </button>
      </div>

      {/* Lista de avaliações */}
      <div className="space-y-4">
        {filteredAvaliacoes.map((avaliacao) => (
          <div key={avaliacao.id} className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div className="flex-1">
                <div className="flex items-center gap-4 mb-2">
                  <h3 className="font-semibold text-lg text-[#2E1530]">
                    {avaliacao.nome_acompanhante}
                  </h3>
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(avaliacao.status)}`}>
                    {avaliacao.status === 'aprovado' ? 'Aprovado' : 
                     avaliacao.status === 'pendente' ? 'Pendente' : 'Rejeitado'}
                  </span>
                </div>
                
                <div className="flex items-center gap-4 mb-2 text-sm text-gray-600">
                  <span>Cliente: {avaliacao.cliente_nome}</span>
                  <span>Nota: {renderStars(avaliacao.nota)}</span>
                  <span>Data: {new Date(avaliacao.criado_em).toLocaleDateString('pt-BR')}</span>
                </div>
                
                <p className="text-gray-700 bg-gray-50 p-3 rounded-lg">
                  "{avaliacao.comentario}"
                </p>
              </div>
              
              <div className="flex flex-col gap-2 min-w-[200px]">
                <select
                  value={avaliacao.status}
                  onChange={(e) => handleStatusChange(avaliacao.id, e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                  <option value="pendente">Pendente</option>
                  <option value="aprovado">Aprovado</option>
                  <option value="rejeitado">Rejeitado</option>
                </select>
                
                <button
                  onClick={() => window.open(`/acompanhante/${avaliacao.acompanhante_id}`, '_blank')}
                  className="text-blue-600 hover:text-blue-900 transition-colors text-sm"
                >
                  👁️ Ver perfil da acompanhante
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>

      {filteredAvaliacoes.length === 0 && (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">Nenhuma avaliação encontrada com o filtro selecionado.</p>
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