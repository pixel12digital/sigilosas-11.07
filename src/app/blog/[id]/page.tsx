"use client";
import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { supabase } from '@/lib/supabase';
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

export default function BlogArticlePage() {
  const { id } = useParams<{ id: string }>();
  const [post, setPost] = useState<BlogPost | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchPost = async () => {
      const { data, error } = await supabase
        .from('blog_posts')
        .select('*')
        .eq('id', id)
        .single();
      if (!error) setPost(data);
      setLoading(false);
    };
    if (id) fetchPost();
  }, [id]);

  return (
    <div className="flex flex-col min-h-screen bg-gray-50">
      <Header />
      <main className="flex-1 max-w-3xl mx-auto py-10 px-4 w-full">
        {loading ? (
          <div className="flex justify-center items-center min-h-[200px]">
            <span className="text-lg text-gray-500">Carregando artigo...</span>
          </div>
        ) : !post ? (
          <div className="text-center text-gray-500">Artigo não encontrado.</div>
        ) : (
          <article className="bg-white rounded-xl shadow-lg p-6">
            {post.imagem_url && (
              <img src={post.imagem_url} alt={post.titulo} className="w-full h-64 object-cover rounded-md mb-6" />
            )}
            <h1 className="text-3xl font-bold text-[#4E3950] mb-2">{post.titulo}</h1>
            <div className="flex items-center gap-2 text-xs text-gray-500 mb-6">
              <span>{post.autor}</span>
              <span>•</span>
              <span>{new Date(post.created_at).toLocaleDateString('pt-BR')}</span>
            </div>
            <div className="text-gray-800 text-lg whitespace-pre-line leading-relaxed">{post.conteudo}</div>
            <div className="mt-10 p-6 bg-[#FFF7E6] rounded-lg text-center border border-[#E6B980]">
              <h2 className="text-2xl font-bold text-[#4E3950] mb-2">Ainda não faz parte do SigilosasVIP?</h2>
              <p className="mb-4 text-[#4E3950]">Cadastre-se agora e tenha acesso a mais dicas, oportunidades e visibilidade exclusiva para acompanhantes!</p>
              <a
                href="/cadastro"
                className="inline-block px-6 py-3 bg-[#E6B980] text-[#4E3950] font-semibold rounded-md shadow hover:bg-[#FFD9A0] transition-colors"
              >
                Quero me cadastrar
              </a>
            </div>
          </article>
        )}
      </main>
      <Footer />
    </div>
  );
} 