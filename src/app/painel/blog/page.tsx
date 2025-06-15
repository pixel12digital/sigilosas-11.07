'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface BlogPost {
  id: number;
  titulo: string;
  conteudo: string;
  autor: string;
  status: 'rascunho' | 'publicado' | 'arquivado';
  criado_em: string;
}

export default function BlogPage() {
  const [posts, setPosts] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [formData, setFormData] = useState({
    titulo: '',
    conteudo: '',
    autor: ''
  });

  useEffect(() => {
    fetchPosts();
  }, []);

  const fetchPosts = async () => {
    try {
      const { data, error } = await supabase
        .from('blog_posts')
        .select('*')
        .order('criado_em', { ascending: false });

      if (error) throw error;
      setPosts(data || []);
    } catch (error) {
      console.error('Erro ao buscar posts:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      const { error } = await supabase
        .from('blog_posts')
        .insert({
          titulo: formData.titulo.trim(),
          conteudo: formData.conteudo.trim(),
          autor: formData.autor.trim(),
          status: 'rascunho',
          criado_em: new Date().toISOString()
        });

      if (error) throw error;

      setMessage('Post criado com sucesso!');
      setFormData({ titulo: '', conteudo: '', autor: '' });
      fetchPosts();
    } catch (error) {
      console.error('Erro ao criar post:', error);
      setMessage('Erro ao criar post. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (id: number, novoStatus: string) => {
    try {
      const { error } = await supabase
        .from('blog_posts')
        .update({ status: novoStatus })
        .eq('id', id);

      if (error) throw error;
      fetchPosts();
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Tem certeza que deseja excluir este post?')) return;

    try {
      const { error } = await supabase
        .from('blog_posts')
        .delete()
        .eq('id', id);

      if (error) throw error;

      setMessage('Post excluído com sucesso!');
      fetchPosts();
    } catch (error) {
      console.error('Erro ao excluir post:', error);
      setMessage('Erro ao excluir post. Tente novamente.');
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'publicado': return 'bg-green-100 text-green-800';
      case 'rascunho': return 'bg-yellow-100 text-yellow-800';
      case 'arquivado': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading && posts.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando posts...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Gerenciar Blog</h1>
        <p className="text-gray-600">Crie e gerencie os posts do blog</p>
      </div>

      {/* Formulário para criar post */}
      <div className="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-7">
        <h2 className="text-xl font-bold text-[#2E1530] mb-4">Criar Novo Post</h2>
        
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Título:
            </label>
            <input
              type="text"
              value={formData.titulo}
              onChange={(e) => setFormData({ ...formData, titulo: e.target.value })}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Autor:
            </label>
            <input
              type="text"
              value={formData.autor}
              onChange={(e) => setFormData({ ...formData, autor: e.target.value })}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Conteúdo:
            </label>
            <textarea
              value={formData.conteudo}
              onChange={(e) => setFormData({ ...formData, conteudo: e.target.value })}
              required
              rows={6}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-6 rounded-md font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            {loading ? 'Criando...' : 'Criar Post'}
          </button>
        </form>

        {message && (
          <div className="mt-4 p-3 bg-green-100 text-green-700 rounded-md text-center font-semibold">
            {message}
          </div>
        )}
      </div>

      {/* Lista de posts */}
      <div className="space-y-4">
        {posts.map((post) => (
          <div key={post.id} className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div className="flex-1">
                <div className="flex items-center gap-4 mb-2">
                  <h3 className="font-semibold text-lg text-[#2E1530]">
                    {post.titulo}
                  </h3>
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(post.status)}`}>
                    {post.status === 'publicado' ? 'Publicado' : 
                     post.status === 'rascunho' ? 'Rascunho' : 'Arquivado'}
                  </span>
                </div>
                
                <div className="flex items-center gap-4 mb-2 text-sm text-gray-600">
                  <span>Autor: {post.autor}</span>
                  <span>Data: {new Date(post.criado_em).toLocaleDateString('pt-BR')}</span>
                </div>
                
                <p className="text-gray-700 bg-gray-50 p-3 rounded-lg line-clamp-3">
                  {post.conteudo.length > 200 
                    ? `${post.conteudo.substring(0, 200)}...` 
                    : post.conteudo}
                </p>
              </div>
              
              <div className="flex flex-col gap-2 min-w-[200px]">
                <select
                  value={post.status}
                  onChange={(e) => handleStatusChange(post.id, e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                  <option value="rascunho">Rascunho</option>
                  <option value="publicado">Publicado</option>
                  <option value="arquivado">Arquivado</option>
                </select>
                
                <div className="flex gap-2">
                  <button
                    onClick={() => window.open(`/blog/${post.id}`, '_blank')}
                    className="text-blue-600 hover:text-blue-900 transition-colors text-sm"
                  >
                    👁️ Ver post
                  </button>
                  <button
                    onClick={() => handleDelete(post.id)}
                    className="text-red-600 hover:text-red-900 transition-colors text-sm"
                    title="Excluir post"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {posts.length === 0 && (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">Nenhum post encontrado.</p>
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