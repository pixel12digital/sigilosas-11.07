import Link from "next/link";

export default function Obrigado() {
  return (
    <div className="max-w-lg mx-auto mt-20 p-8 bg-white rounded-2xl shadow-lg border border-[#CFB78B] text-center">
      <h1 className="text-2xl font-bold text-[#4E3950] mb-4">Cadastro realizado com sucesso!</h1>
      <p className="text-lg text-[#4E3950] mb-8">Aguarde a aprovação do administrador. Você receberá um e-mail quando seu cadastro for aprovado.</p>
      <div className="flex flex-col gap-4">
        <a
          href="https://wa.me/SEUNUMERO?text=Olá,%20gostaria%20de%20falar%20com%20a%20equipe%20Sigilosas%20VIP."
          target="_blank"
          rel="noopener noreferrer"
          className="w-full py-3 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700 transition"
        >
          Falar com Sigilosas VIP (WhatsApp)
        </a>
        <Link
          href="/"
          className="w-full py-3 bg-[#4E3950] text-white rounded-lg font-semibold text-lg hover:bg-[#CFB78B] hover:text-[#4E3950] transition text-center"
        >
          Voltar ao site
        </Link>
      </div>
    </div>
  );
} 