import Header from '@/components/Header';
import Footer from '@/components/Footer';

export default function TermosPage() {
  return (
    <div className="flex flex-col min-h-screen bg-gray-50">
      <Header />
      <main className="flex-1 max-w-3xl mx-auto py-12 px-4 w-full">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">Termos de Uso</h1>
        <div className="text-gray-700 space-y-4">
          <p>
            Bem-vindo ao Sigilosas VIP! Ao acessar e utilizar nossa plataforma, você concorda com os termos e condições abaixo. Recomendamos a leitura atenta deste documento para garantir uma experiência segura, ética e transparente para todos.
          </p>
          <p>
            <strong>1. Objetivo:</strong> O Sigilosas VIP é uma plataforma que conecta acompanhantes e clientes de forma sigilosa, respeitosa e profissional. Não toleramos qualquer tipo de discriminação, assédio ou atividade ilegal.
          </p>
          <p>
            <strong>2. Cadastro e Responsabilidade:</strong> Ao se cadastrar, você se compromete a fornecer informações verdadeiras e manter sua conta segura. O uso indevido da plataforma pode resultar em suspensão ou exclusão do perfil.
          </p>
          <p>
            <strong>3. Privacidade:</strong> Respeitamos sua privacidade. Suas informações são protegidas conforme nossa Política de Privacidade.
          </p>
          <p>
            <strong>4. Conteúdo:</strong> É proibido publicar conteúdo ofensivo, ilegal ou que viole direitos de terceiros. Reservamo-nos o direito de remover qualquer conteúdo inadequado.
          </p>
          <p>
            <strong>5. Alterações:</strong> Os termos podem ser atualizados a qualquer momento. Recomendamos revisitar esta página periodicamente.
          </p>
          <p>
            Em caso de dúvidas, entre em contato conosco pela página de <a href="/contato" className="text-primary underline">Contato</a>.
          </p>
        </div>
      </main>
      <Footer />
    </div>
  );
} 