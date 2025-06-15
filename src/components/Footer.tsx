export default function Footer() {
  return (
    <footer className="bg-[#F8F6F9] py-8 mt-10 border-t border-[#CFB78B]">
      <div className="max-w-7xl mx-auto text-center">
        <p className="text-[#4E3950] font-semibold text-lg mb-3">
          &copy; {new Date().getFullYear()} SigilosasVip. Todos os direitos reservados.
        </p>
        <nav className="mb-2">
          <a href="/sobre" className="text-[#4E3950] mx-2 hover:text-[#CFB78B] transition-colors">
            Sobre
          </a>
          <span className="text-[#CFB78B]">|</span>
          <a href="/blog" className="text-[#4E3950] mx-2 hover:text-[#CFB78B] transition-colors">
            Blog
          </a>
          <span className="text-[#CFB78B]">|</span>
          <a href="/termos" className="text-[#4E3950] mx-2 hover:text-[#CFB78B] transition-colors">
            Termos de Uso
          </a>
          <span className="text-[#CFB78B]">|</span>
          <a href="/privacidade" className="text-[#4E3950] mx-2 hover:text-[#CFB78B] transition-colors">
            Privacidade
          </a>
          <span className="text-[#CFB78B]">|</span>
          <a href="/contato" className="text-[#4E3950] mx-2 hover:text-[#CFB78B] transition-colors">
            Contato
          </a>
        </nav>
      </div>
    </footer>
  );
} 