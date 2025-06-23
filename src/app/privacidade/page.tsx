import Header from '@/components/Header';
import Footer from '@/components/Footer';

export default function PrivacidadePage() {
  return (
    <div className="flex flex-col min-h-screen bg-gray-50">
      <Header />
      <main className="flex-1 max-w-3xl mx-auto py-12 px-4 w-full">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">Política de Privacidade</h1>
        <div className="text-gray-700 space-y-4">
          <p>
            Sua privacidade é prioridade no Sigilosas VIP. Coletamos apenas as informações necessárias para garantir segurança, personalização e o melhor funcionamento da plataforma.
          </p>
          <p>
            <strong>1. Dados Coletados:</strong> Informações de cadastro, navegação e preferências são utilizadas exclusivamente para melhorar sua experiência.
          </p>
          <p>
            <strong>2. Uso das Informações:</strong> Não compartilhamos seus dados com terceiros sem consentimento, exceto quando exigido por lei.
          </p>
          <p>
            <strong>3. Segurança:</strong> Utilizamos tecnologias modernas para proteger seus dados contra acessos não autorizados.
          </p>
          <p>
            <strong>4. Cookies:</strong> Utilizamos cookies para personalizar e facilitar sua navegação. Você pode gerenciar suas preferências no navegador.
          </p>
          <p>
            <strong>5. Direitos do Usuário:</strong> Você pode solicitar a atualização ou exclusão de seus dados a qualquer momento.
          </p>
          <p>
            Para mais informações ou solicitações, acesse nossa página de <a href="/contato" className="text-primary underline">Contato</a>.
          </p>
        </div>
      </main>
      <Footer />
    </div>
  );
} 