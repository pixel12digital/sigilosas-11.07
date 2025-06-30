'use client';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { useRef } from 'react';

export default function ContatoPage() {
  const formRef = useRef<HTMLFormElement>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const form = formRef.current;
    if (!form) return;
    const nome = (form.elements.namedItem('nome') as HTMLInputElement)?.value || '';
    const email = (form.elements.namedItem('email') as HTMLInputElement)?.value || '';
    const mensagem = (form.elements.namedItem('mensagem') as HTMLTextAreaElement)?.value || '';
    const texto =
      `Olá, recebi um contato pelo site Sigilosas VIP:%0A` +
      `Nome: ${nome}%0A` +
      `Email: ${email}%0A` +
      `Mensagem: ${mensagem}`;
    const url = `https://wa.me/5547996829294?text=${texto}`;
    window.open(url, '_blank');
  };

  return (
    <div className="flex flex-col min-h-screen bg-gray-50">
      <Header />
      <main className="flex-1 max-w-3xl mx-auto py-12 px-4 w-full">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">Contato</h1>
        <p className="mb-6 text-gray-700">Preencha o formulário abaixo para entrar em contato com a equipe Sigilosas VIP.</p>
        <form className="space-y-4 max-w-xl" ref={formRef} onSubmit={handleSubmit}>
          <div>
            <label className="block text-gray-700 mb-1" htmlFor="nome">Nome</label>
            <input type="text" id="nome" name="nome" className="w-full border border-gray-300 rounded px-3 py-2" required />
          </div>
          <div>
            <label className="block text-gray-700 mb-1" htmlFor="email">Email</label>
            <input type="email" id="email" name="email" className="w-full border border-gray-300 rounded px-3 py-2" required />
          </div>
          <div>
            <label className="block text-gray-700 mb-1" htmlFor="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" rows={5} className="w-full border border-gray-300 rounded px-3 py-2" required />
          </div>
          <button type="submit" className="bg-primary text-white px-6 py-2 rounded font-semibold hover:bg-primary-hover transition-colors">Enviar</button>
        </form>
      </main>
      <Footer />
    </div>
  );
} 