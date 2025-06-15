'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface Estatisticas {
  totalAcompanhantes: number;
  acompanhantesAtivos: number;
  acompanhantesPendentes: number;
  totalAvaliacoes: number;
  avaliacoesAprovadas: number;
  avaliacoesPendentes: number;
  totalDenuncias: number;
  denunciasPendentes: number;
  totalCidades: number;
  totalBlogPosts: number;
  postsPublicados: number;
  mediaAvaliacoes: number;
}

export default function EstatisticasPage() {
  const [stats, setStats] = useState<Estatisticas>({
    totalAcompanhantes: 0,
    acompanhantesAtivos: 0,
    acompanhantesPendentes: 0,
    totalAvaliacoes: 0,
    avaliacoesAprovadas: 0,
    avaliacoesPendentes: 0,
    totalDenuncias: 0,
    denunciasPendentes: 0,
    totalCidades: 0,
    totalBlogPosts: 0,
    postsPublicados: 0,
    mediaAvaliacoes: 0,
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchEstatisticas();
  }, []);

  const fetchEstatisticas = async () => {
    try {
      // Buscar todas as estatísticas
      const [
        { count: acompanhantes },
        { count: acompanhantesAtivos },
        { count: acompanhantesPendentes },
        { count: avaliacoes },
        { count: avaliacoesAprovadas },
        { count: avaliacoesPendentes },
        { count: denuncias },
        { count: denunciasPendentes },
        { count: cidades },
        { count: blogPosts },
        { count: postsPublicados },
        { data: avaliacoesData },
      ] = await Promise.all([
        supabase.from('acompanhantes').select('*', { count: 'exact', head: true }),
        supabase.from('acompanhantes').select('*', { count: 'exact', head: true }).eq('status', 'ativo'),
        supabase.from('acompanhantes').select('*', { count: 'exact', head: true }).eq('status', 'pendente'),
        supabase.from('avaliacoes').select('*', { count: 'exact', head: true }),
        supabase.from('avaliacoes').select('*', { count: 'exact', head: true }).eq('status', 'aprovado'),
        supabase.from('avaliacoes').select('*', { count: 'exact', head: true }).eq('status', 'pendente'),
        supabase.from('denuncias').select('*', { count: 'exact', head: true }),
        supabase.from('denuncias').select('*', { count: 'exact', head: true }).eq('status', 'pendente'),
        supabase.from('cidades').select('*', { count: 'exact', head: true }),
        supabase.from('blog_posts').select('*', { count: 'exact', head: true }),
        supabase.from('blog_posts').select('*', { count: 'exact', head: true }).eq('status', 'publicado'),
        supabase.from('avaliacoes').select('nota').eq('status', 'aprovado'),
      ]);

      // Calcular média das avaliações
      const mediaAvaliacoes = avaliacoesData && avaliacoesData.length > 0
        ? avaliacoesData.reduce((acc, av) => acc + av.nota, 0) / avaliacoesData.length
        : 0;

      setStats({
        totalAcompanhantes: acompanhantes || 0,
        acompanhantesAtivos: acompanhantesAtivos || 0,
        acompanhantesPendentes: acompanhantesPendentes || 0,
        totalAvaliacoes: avaliacoes || 0,
        avaliacoesAprovadas: avaliacoesAprovadas || 0,
        avaliacoesPendentes: avaliacoesPendentes || 0,
        totalDenuncias: denuncias || 0,
        denunciasPendentes: denunciasPendentes || 0,
        totalCidades: cidades || 0,
        totalBlogPosts: blogPosts || 0,
        postsPublicados: postsPublicados || 0,
        mediaAvaliacoes: Math.round(mediaAvaliacoes * 10) / 10,
      });
    } catch (error) {
      console.error('Erro ao buscar estatísticas:', error);
    } finally {
      setLoading(false);
    }
  };

  const StatCard = ({ title, value, icon, color, subtitle }: {
    title: string;
    value: number | string;
    icon: string;
    color: string;
    subtitle?: string;
  }) => (
    <div className="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-gray-600 text-sm font-medium">{title}</p>
          <p className={`text-3xl font-bold ${color}`}>{value}</p>
          {subtitle && <p className="text-gray-500 text-xs mt-1">{subtitle}</p>}
        </div>
        <div className={`text-4xl ${color}`}>{icon}</div>
      </div>
    </div>
  );

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando estatísticas...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Estatísticas do Sistema</h1>
        <p className="text-gray-600">Visão geral dos dados e métricas</p>
      </div>

      {/* Cards de estatísticas */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <StatCard
          title="Total de Acompanhantes"
          value={stats.totalAcompanhantes}
          icon="👥"
          color="text-blue-600"
        />
        <StatCard
          title="Acompanhantes Ativos"
          value={stats.acompanhantesAtivos}
          icon="✅"
          color="text-green-600"
          subtitle={`${stats.totalAcompanhantes > 0 ? Math.round((stats.acompanhantesAtivos / stats.totalAcompanhantes) * 100) : 0}% do total`}
        />
        <StatCard
          title="Acompanhantes Pendentes"
          value={stats.acompanhantesPendentes}
          icon="⏳"
          color="text-yellow-600"
        />
        <StatCard
          title="Total de Avaliações"
          value={stats.totalAvaliacoes}
          icon="⭐"
          color="text-yellow-600"
        />
        <StatCard
          title="Avaliações Aprovadas"
          value={stats.avaliacoesAprovadas}
          icon="✅"
          color="text-green-600"
          subtitle={`${stats.totalAvaliacoes > 0 ? Math.round((stats.avaliacoesAprovadas / stats.totalAvaliacoes) * 100) : 0}% do total`}
        />
        <StatCard
          title="Avaliações Pendentes"
          value={stats.avaliacoesPendentes}
          icon="⏳"
          color="text-orange-600"
        />
        <StatCard
          title="Média das Avaliações"
          value={`${stats.mediaAvaliacoes}/5`}
          icon="📊"
          color="text-purple-600"
        />
        <StatCard
          title="Total de Denúncias"
          value={stats.totalDenuncias}
          icon="🚩"
          color="text-red-600"
        />
        <StatCard
          title="Denúncias Pendentes"
          value={stats.denunciasPendentes}
          icon="⚠️"
          color="text-orange-600"
        />
        <StatCard
          title="Total de Cidades"
          value={stats.totalCidades}
          icon="🏙️"
          color="text-green-600"
        />
        <StatCard
          title="Posts do Blog"
          value={stats.totalBlogPosts}
          icon="📝"
          color="text-purple-600"
        />
        <StatCard
          title="Posts Publicados"
          value={stats.postsPublicados}
          icon="📢"
          color="text-blue-600"
          subtitle={`${stats.totalBlogPosts > 0 ? Math.round((stats.postsPublicados / stats.totalBlogPosts) * 100) : 0}% do total`}
        />
      </div>

      {/* Resumo */}
      <div className="bg-white rounded-xl shadow-lg p-6">
        <h2 className="text-2xl font-bold text-[#2E1530] mb-6">Resumo Geral</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div className="text-center p-4 bg-blue-50 rounded-lg">
            <h3 className="font-semibold text-blue-800 mb-2">Taxa de Aprovação</h3>
            <p className="text-2xl font-bold text-blue-600">
              {stats.totalAcompanhantes > 0 
                ? Math.round((stats.acompanhantesAtivos / stats.totalAcompanhantes) * 100) 
                : 0}%
            </p>
            <p className="text-sm text-blue-600">Acompanhantes ativos</p>
          </div>
          
          <div className="text-center p-4 bg-green-50 rounded-lg">
            <h3 className="font-semibold text-green-800 mb-2">Satisfação</h3>
            <p className="text-2xl font-bold text-green-600">
              {stats.mediaAvaliacoes}/5
            </p>
            <p className="text-sm text-green-600">Média das avaliações</p>
          </div>
          
          <div className="text-center p-4 bg-yellow-50 rounded-lg">
            <h3 className="font-semibold text-yellow-800 mb-2">Conteúdo Ativo</h3>
            <p className="text-2xl font-bold text-yellow-600">
              {stats.totalBlogPosts > 0 
                ? Math.round((stats.postsPublicados / stats.totalBlogPosts) * 100) 
                : 0}%
            </p>
            <p className="text-sm text-yellow-600">Posts publicados</p>
          </div>
        </div>
      </div>

      {/* Ações rápidas */}
      <div className="bg-white rounded-xl shadow-lg p-6">
        <h2 className="text-2xl font-bold text-[#2E1530] mb-6">Ações Rápidas</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Link
            href="/painel/acompanhantes"
            className="flex items-center gap-3 p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
          >
            <span className="text-2xl">👥</span>
            <div>
              <p className="font-semibold text-[#2E1530]">Gerenciar Acompanhantes</p>
              <p className="text-sm text-gray-600">{stats.acompanhantesPendentes} pendentes</p>
            </div>
          </Link>
          
          <Link
            href="/painel/avaliacoes"
            className="flex items-center gap-3 p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors"
          >
            <span className="text-2xl">⭐</span>
            <div>
              <p className="font-semibold text-[#2E1530]">Moderar Avaliações</p>
              <p className="text-sm text-gray-600">{stats.avaliacoesPendentes} pendentes</p>
            </div>
          </Link>
          
          <Link
            href="/painel/denuncias"
            className="flex items-center gap-3 p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
          >
            <span className="text-2xl">🚩</span>
            <div>
              <p className="font-semibold text-[#2E1530]">Analisar Denúncias</p>
              <p className="text-sm text-gray-600">{stats.denunciasPendentes} pendentes</p>
            </div>
          </Link>
          
          <Link
            href="/painel/blog"
            className="flex items-center gap-3 p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
          >
            <span className="text-2xl">📝</span>
            <div>
              <p className="font-semibold text-[#2E1530]">Gerenciar Blog</p>
              <p className="text-sm text-gray-600">{stats.postsPublicados} publicados</p>
            </div>
          </Link>
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