'use client';
import { useEffect, useState } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';
import Header from '@/components/Header';
import Footer from '@/components/Footer';

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

  useEffect(() => {
    const fetchPosts = async () => {
      const { data, error } = await supabase
        .from('blog_posts')
        .select('*')
        .order('created_at', { ascending: false });
      if (!error) setPosts(data || []);
      setLoading(false);
    };
    fetchPosts();
  }, []);

  return (
    <div className="flex flex-col min-h-screen bg-gray-50">
      <Header />
      <main className="flex-1 max-w-5xl mx-auto py-10 px-4 w-full">
        <h1 className="text-4xl font-bold text-center text-[#2E1530] mb-10">Dicas & Inspiração para Acompanhantes</h1>
        {loading ? (
          <div className="flex justify-center items-center min-h-[200px]">
            <span className="text-lg text-gray-500">Carregando artigos...</span>
          </div>
        ) : posts.length === 0 ? (
          <div className="text-center text-gray-500">Nenhum artigo publicado ainda.</div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            {posts.map(post => (
              <div key={post.id} className="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-shadow p-5 flex flex-col">
                {post.imagem_url && (
                  <img src={post.imagem_url} alt={post.titulo} className="w-full h-48 object-cover rounded-md mb-4" />
                )}
                <h2 className="text-xl font-bold text-[#4E3950] mb-2 line-clamp-2">{post.titulo}</h2>
                <div className="flex items-center gap-2 text-xs text-gray-500 mb-2">
                  <span>{post.autor}</span>
                  <span>•</span>
                  <span>{new Date(post.created_at).toLocaleDateString('pt-BR')}</span>
                </div>
                <p className="text-gray-700 mb-4 line-clamp-3">{post.conteudo.length > 180 ? post.conteudo.substring(0, 180) + '...' : post.conteudo}</p>
                <Link href={`/blog/${post.id}`} className="inline-block mt-auto px-4 py-2 bg-[#4E3950] text-white rounded-md font-semibold hover:bg-[#2E1530] transition-colors">
                  Ler artigo
                </Link>
              </div>
            ))}
          </div>
        )}
      </main>
      <Footer />
    </div>
  );
} 