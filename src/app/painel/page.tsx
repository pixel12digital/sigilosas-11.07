'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface DashboardStats {
  totalAcompanhantes: number;
  acompanhantesPendentes: number;
  totalAvaliacoes: number;
  avaliacoesPendentes: number;
  totalDenuncias: number;
  denunciasPendentes: number;
  totalCidades: number;
  totalBlogPosts: number;
}

export default function DashboardPage() {
  const [stats, setStats] = useState<DashboardStats>({
    totalAcompanhantes: 0,
    acompanhantesPendentes: 0,
    totalAvaliacoes: 0,
    avaliacoesPendentes: 0,
    totalDenuncias: 0,
    denunciasPendentes: 0,
    totalCidades: 0,
    totalBlogPosts: 0,
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardStats();
  }, []);

  const fetchDashboardStats = async () => {
    try {
      // Buscar estatísticas do Supabase
      const [
        { count: acompanhantes },
        { count: acompanhantesPendentes },
        { count: avaliacoes },
        { count: avaliacoesPendentes },
        { count: denuncias },
        { count: denunciasPendentes },
        { count: cidades },
        { count: blogPosts },
      ] = await Promise.all([
        supabase.from('acompanhantes').select('*', { count: 'exact', head: true }),
        supabase.from('acompanhantes').select('*', { count: 'exact', head: true }).eq('status', 'pendente'),
        supabase.from('avaliacoes').select('*', { count: 'exact', head: true }),
        supabase.from('avaliacoes').select('*', { count: 'exact', head: true }).eq('status', 'pendente'),
        supabase.from('denuncias').select('*', { count: 'exact', head: true }),
        supabase.from('denuncias').select('*', { count: 'exact', head: true }).eq('status', 'pendente'),
        supabase.from('cidades').select('*', { count: 'exact', head: true }),
        supabase.from('blog_posts').select('*', { count: 'exact', head: true }),
      ]);

      setStats({
        totalAcompanhantes: acompanhantes || 0,
        acompanhantesPendentes: acompanhantesPendentes || 0,
        totalAvaliacoes: avaliacoes || 0,
        avaliacoesPendentes: avaliacoesPendentes || 0,
        totalDenuncias: denuncias || 0,
        denunciasPendentes: denunciasPendentes || 0,
        totalCidades: cidades || 0,
        totalBlogPosts: blogPosts || 0,
      });
    } catch (error) {
      console.error('Erro ao buscar estatísticas:', error);
    } finally {
      setLoading(false);
    }
  };

  const StatCard = ({ title, value, icon, color, href }: {
    title: string;
    value: number;
    icon: string;
    color: string;
    href?: string;
  }) => {
    const CardContent = (
      <div className={`bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow ${href ? 'cursor-pointer' : ''}`}>
        <div className="flex items-center justify-between">
          <div>
            <p className="text-gray-600 text-sm font-medium">{title}</p>
            <p className={`text-3xl font-bold ${color}`}>{value}</p>
          </div>
          <div className={`text-4xl ${color}`}>{icon}</div>
        </div>
      </div>
    );

    if (href) {
      return <Link href={href}>{CardContent}</Link>;
    }

    return CardContent;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center mb-8">
        <h1 className="text-4xl font-bold text-[#2E1530] mb-2">Dashboard</h1>
        <p className="text-gray-600">Bem-vindo ao painel administrativo</p>
      </div>

      {/* Cards de estatísticas */}
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 max-w-7xl mx-auto px-2">
        <StatCard
          title="Total de Acompanhantes"
          value={stats.totalAcompanhantes}
          icon="👥"
          color="text-blue-600"
          href="/painel/acompanhantes"
        />
        <StatCard
          title="Acompanhantes Pendentes"
          value={stats.acompanhantesPendentes}
          icon="⏳"
          color="text-orange-600"
          href="/painel/acompanhantes"
        />
        <StatCard
          title="Total de Avaliações"
          value={stats.totalAvaliacoes}
          icon="⭐"
          color="text-yellow-600"
          href="/painel/avaliacoes"
        />
        <StatCard
          title="Avaliações Pendentes"
          value={stats.avaliacoesPendentes}
          icon="⏳"
          color="text-orange-600"
          href="/painel/avaliacoes"
        />
        <StatCard
          title="Total de Denúncias"
          value={stats.totalDenuncias}
          icon="🚩"
          color="text-red-600"
          href="/painel/denuncias"
        />
        <StatCard
          title="Denúncias Pendentes"
          value={stats.denunciasPendentes}
          icon="⏳"
          color="text-orange-600"
          href="/painel/denuncias"
        />
        <StatCard
          title="Total de Cidades"
          value={stats.totalCidades}
          icon="🏙️"
          color="text-green-600"
          href="/painel/cidades"
        />
        <StatCard
          title="Posts do Blog"
          value={stats.totalBlogPosts}
          icon="📝"
          color="text-purple-600"
          href="/painel/blog"
        />
      </div>

      {/* Ações rápidas */}
      <div className="bg-white rounded-xl shadow-lg p-6 max-w-7xl mx-auto px-2">
        <h2 className="text-2xl font-bold text-[#2E1530] mb-6">Ações Rápidas</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            href="/painel/acompanhantes"
            className="flex items-center gap-3 p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
          >
            <span className="text-2xl">👥</span>
            <div>
              <p className="font-semibold text-[#2E1530]">Gerenciar Acompanhantes</p>
              <p className="text-sm text-gray-600">Aprovar e editar perfis</p>
            </div>
          </Link>
          
          <Link
            href="/painel/avaliacoes"
            className="flex items-center gap-3 p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors"
          >
            <span className="text-2xl">⭐</span>
            <div>
              <p className="font-semibold text-[#2E1530]">Gerenciar Avaliações</p>
              <p className="text-sm text-gray-600">Moderar avaliações</p>
            </div>
          </Link>
          
          <Link
            href="/painel/blog"
            className="flex items-center gap-3 p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
          >
            <span className="text-2xl">📝</span>
            <div>
              <p className="font-semibold text-[#2E1530]">Gerenciar Blog</p>
              <p className="text-sm text-gray-600">Criar e editar posts</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  );
} 