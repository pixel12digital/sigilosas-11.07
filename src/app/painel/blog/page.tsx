'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface BlogPost {
  id: string;
  titulo: string;
  conteudo: string;
  autor: string;
  created_at: string;
  imagem_url?: string;
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
  const [imagemFile, setImagemFile] = useState<File | null>(null);
  const [imagemPreview, setImagemPreview] = useState<string | null>(null);
  const [aba, setAba] = useState<'artigos' | 'novo' | 'editar'>('artigos');
  const [editando, setEditando] = useState<BlogPost | null>(null);

  useEffect(() => {
    fetchPosts();
  }, []);

  const fetchPosts = async () => {
    try {
      const { data, error } = await supabase
        .from('blog_posts')
        .select('*')
        .order('created_at', { ascending: false });

      if (error) throw error;
      setPosts(data || []);
    } catch (error) {
      console.error('Erro ao buscar posts:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleImagemChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] || null;
    setImagemFile(file);
    if (file) {
      const reader = new FileReader();
      reader.onload = (ev) => setImagemPreview(ev.target?.result as string);
      reader.readAsDataURL(file);
    } else {
      setImagemPreview(null);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    let imagem_url = '';
    try {
      if (imagemFile) {
        const fileExt = imagemFile.name.split('.').pop();
        const fileName = `blog/${Date.now()}-${Math.random().toString(36).substring(2, 8)}.${fileExt}`;
        const { data: uploadData, error: uploadError } = await supabase.storage.from('blog').upload(fileName, imagemFile, { upsert: true });
        if (uploadError) throw uploadError;
        const { data: publicUrlData } = supabase.storage.from('blog').getPublicUrl(fileName);
        imagem_url = publicUrlData.publicUrl;
      }
      const { error } = await supabase
        .from('blog_posts')
        .insert({
          titulo: formData.titulo.trim(),
          conteudo: formData.conteudo.trim(),
          autor: formData.autor.trim(),
          imagem_url
        });
      if (error) throw error;
      setMessage('Post criado com sucesso!');
      setFormData({ titulo: '', conteudo: '', autor: '' });
      setImagemFile(null);
      setImagemPreview(null);
      fetchPosts();
      setTimeout(() => {
        setMessage('');
        setAba('artigos');
      }, 2000);
    } catch (error) {
      console.error('Erro ao criar post:', error);
      setMessage('Erro ao criar post. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: string) => {
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

  const handleEdit = (post: BlogPost) => {
    setEditando(post);
    setFormData({ titulo: post.titulo, conteudo: post.conteudo, autor: post.autor });
    setImagemPreview(post.imagem_url || null);
    setImagemFile(null);
    setAba('editar');
  };

  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    let imagem_url = editando?.imagem_url || '';
    try {
      if (imagemFile) {
        const fileExt = imagemFile.name.split('.').pop();
        const fileName = `blog/${Date.now()}-${Math.random().toString(36).substring(2, 8)}.${fileExt}`;
        const { data: uploadData, error: uploadError } = await supabase.storage.from('blog').upload(fileName, imagemFile, { upsert: true });
        if (uploadError) throw uploadError;
        const { data: publicUrlData } = supabase.storage.from('blog').getPublicUrl(fileName);
        imagem_url = publicUrlData.publicUrl;
      }
      const { error } = await supabase
        .from('blog_posts')
        .update({
          titulo: formData.titulo.trim(),
          conteudo: formData.conteudo.trim(),
          autor: formData.autor.trim(),
          imagem_url
        })
        .eq('id', editando?.id);
      if (error) throw error;
      setMessage('Post atualizado com sucesso!');
      setFormData({ titulo: '', conteudo: '', autor: '' });
      setImagemFile(null);
      setImagemPreview(null);
      setEditando(null);
      setAba('artigos');
      fetchPosts();
    } catch (error) {
      console.error('Erro ao atualizar post:', error);
      setMessage('Erro ao atualizar post. Tente novamente.');
    } finally {
      setLoading(false);
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
      <div className="flex justify-center gap-4 mb-6">
        <button
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${aba === 'artigos' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
          onClick={() => { setAba('artigos'); setEditando(null); setFormData({ titulo: '', conteudo: '', autor: '' }); setImagemFile(null); setImagemPreview(null); }}
        >
          Artigos
        </button>
        <button
          className={`px-4 py-2 rounded-md font-semibold transition-colors ${aba === 'novo' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
          onClick={() => { setAba('novo'); setEditando(null); setFormData({ titulo: '', conteudo: '', autor: '' }); setImagemFile(null); setImagemPreview(null); }}
        >
          Novo artigo
        </button>
      </div>
      {aba === 'artigos' && (
        <div className="space-y-4">
          {posts.length === 0 && <div className="text-center text-gray-500">Nenhum artigo cadastrado ainda.</div>}
          {posts.map((post) => (
            <div key={post.id} className="bg-white rounded-xl shadow-lg p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div className="flex-1">
                <div className="flex items-center gap-4 mb-2">
                  {post.imagem_url && (
                    <img src={post.imagem_url} alt="Imagem de destaque" className="w-24 h-16 object-cover rounded-md border" />
                  )}
                  <div>
                    <h3 className="font-semibold text-lg text-[#2E1530]">{post.titulo}</h3>
                    <div className="text-xs text-gray-600">Autor: {post.autor} | {new Date(post.created_at).toLocaleDateString('pt-BR')}</div>
                  </div>
                </div>
                <div className="text-gray-700 whitespace-pre-line line-clamp-2">{post.conteudo.length > 120 ? post.conteudo.substring(0, 120) + '...' : post.conteudo}</div>
              </div>
              <div className="flex flex-col gap-2 min-w-[120px] items-end">
                <button
                  onClick={() => handleEdit(post)}
                  className="text-blue-600 hover:text-blue-900 transition-colors text-sm"
                >
                  Editar
                </button>
                <button
                  onClick={() => handleDelete(post.id)}
                  className="text-red-600 hover:text-red-900 transition-colors text-sm"
                >
                  Excluir
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
      {(aba === 'novo' || aba === 'editar') && (
        <div className="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-7">
          <h2 className="text-xl font-bold text-[#2E1530] mb-4">{aba === 'novo' ? 'Criar Novo Artigo' : 'Editar Artigo'}</h2>
          <form onSubmit={aba === 'novo' ? handleSubmit : handleUpdate} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Título:</label>
              <input
                type="text"
                value={formData.titulo}
                onChange={(e) => setFormData({ ...formData, titulo: e.target.value })}
                required
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Autor:</label>
              <input
                type="text"
                value={formData.autor}
                onChange={(e) => setFormData({ ...formData, autor: e.target.value })}
                required
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Conteúdo:</label>
              <textarea
                value={formData.conteudo}
                onChange={(e) => setFormData({ ...formData, conteudo: e.target.value })}
                required
                rows={6}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Imagem de destaque:</label>
              <input
                type="file"
                accept="image/*"
                onChange={handleImagemChange}
                className="block w-full text-sm text-gray-600"
              />
              {imagemPreview && (
                <img src={imagemPreview} alt="Preview" className="mt-2 max-h-40 rounded-md border" />
              )}
              {aba === 'editar' && !imagemPreview && editando?.imagem_url && (
                <img src={editando.imagem_url} alt="Imagem atual" className="mt-2 max-h-40 rounded-md border" />
              )}
            </div>
            <button
              type="submit"
              disabled={loading}
              className={`w-full ${aba === 'novo' ? 'bg-blue-600' : 'bg-green-600'} text-white py-3 px-6 rounded-md font-semibold hover:opacity-90 transition-colors disabled:opacity-50`}
            >
              {loading ? (aba === 'novo' ? 'Criando...' : 'Salvando...') : (aba === 'novo' ? 'Criar Artigo' : 'Salvar Alterações')}
            </button>
          </form>
          {message && (
            <div className="mt-4 p-3 bg-green-100 text-green-700 rounded-md text-center font-semibold">
              {message}
            </div>
          )}
        </div>
      )}
    </div>
  );
} 