import { useEffect, useState } from 'react';

export default function PopupAviso18() {
  const [show, setShow] = useState(false);

  useEffect(() => {
    if (typeof window !== 'undefined') {
      const aceitou = localStorage.getItem('sigilosas_aceitou_aviso18');
      if (!aceitou) setShow(true);
    }
  }, []);

  const handleConcordo = () => {
    localStorage.setItem('sigilosas_aceitou_aviso18', 'true');
    setShow(false);
  };

  if (!show) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
      <div className="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center relative animate-fade-in">
        <div className="text-3xl font-extrabold text-purple-700 mb-2">18<span className="text-black">+</span></div>
        <div className="text-lg font-bold text-gray-800 mb-1">CONTEÚDO ADULTO</div>
        <hr className="my-2 border-gray-200" />
        <div className="text-gray-700 text-sm mb-3">
          Entendo que o site <span className="font-semibold text-purple-700">Sigilosas VIP</span> apresenta <span className="font-semibold">conteúdo explícito</span> destinado a <span className="font-semibold">adultos</span>.<br/>
          <a href="/termos" className="underline text-purple-700 hover:text-purple-900" target="_blank" rel="noopener noreferrer">Termos de uso</a>
        </div>
        <div className="text-gray-600 text-xs mb-3">
          <span className="font-semibold">AVISO DE COOKIES</span><br/>
          Usamos cookies e tecnologias semelhantes para melhorar sua experiência em nosso site.
        </div>
        <div className="text-gray-500 text-xs mb-4">
          A profissão de acompanhante é legalizada no Brasil e deve ser respeitada. <a href="/sobre" className="underline text-purple-700 hover:text-purple-900">Saiba mais</a>
        </div>
        <button
          onClick={handleConcordo}
          className="w-full bg-purple-700 hover:bg-purple-800 text-white font-bold py-2 px-4 rounded transition-colors text-lg mt-2"
        >
          Concordo
        </button>
      </div>
    </div>
  );
} 