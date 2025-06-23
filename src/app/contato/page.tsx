import Header from '@/components/Header';
import Footer from '@/components/Footer';

export default function ContatoPage() {
  return (
    <div className="flex flex-col min-h-screen bg-gray-50">
      <Header />
      <main className="flex-1 max-w-3xl mx-auto py-12 px-4 w-full">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">Contato</h1>
        <p className="mb-6 text-gray-700">Preencha o formulário abaixo para entrar em contato com a equipe Sigilosas VIP.</p>
        <form className="space-y-4 max-w-xl">
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