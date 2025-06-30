export default function Footer() {
  return (
    <footer className="bg-gray-100 py-8 mt-10 border-t border-accent">
      <div className="max-w-7xl mx-auto text-center">
        <p className="text-secondary font-semibold text-lg mb-3">
          &copy; {new Date().getFullYear()} SigilosasVip. Todos os direitos reservados.
        </p>
        <nav className="mb-2">
          <a href="/#sobre" className="text-secondary mx-2 hover:text-accent transition-colors">
            Sobre
          </a>
          <span className="text-accent">|</span>
          <a href="/blog" className="text-secondary mx-2 hover:text-accent transition-colors">
            Blog
          </a>
          <span className="text-accent">|</span>
          <a href="/termos" className="text-secondary mx-2 hover:text-accent transition-colors">
            Termos de Uso
          </a>
          <span className="text-accent">|</span>
          <a href="/privacidade" className="text-secondary mx-2 hover:text-accent transition-colors">
            Privacidade
          </a>
          <span className="text-accent">|</span>
          <a href="/contato" className="text-secondary mx-2 hover:text-accent transition-colors">
            Contato
          </a>
        </nav>
        <div className="flex flex-col items-center gap-1 mt-2 text-sm text-secondary">
          <div>
            <a href="mailto:contato@sigilosasvip.com.br" className="hover:text-accent font-medium flex items-center gap-1" title="Enviar e-mail">
              <svg xmlns="http://www.w3.org/2000/svg" className="inline w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 12l-4-4-4 4m8 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6" /></svg>
              contato@sigilosasvip.com.br
            </a>
          </div>
          <div>
            <a href="https://wa.me/5547996829294" target="_blank" rel="noopener noreferrer" className="hover:text-accent font-medium flex items-center gap-1" title="Falar no WhatsApp">
              <svg xmlns="http://www.w3.org/2000/svg" className="inline w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.25 12c0 5.385 4.365 9.75 9.75 9.75 1.7 0 3.3-.425 4.7-1.225l3.025.8a1.125 1.125 0 0 0 1.375-1.375l-.8-3.025A9.708 9.708 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12Z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.625 10.125c.375 1.125 1.5 2.25 2.625 2.625m0 0c.375.125.75.25 1.125.25.375 0 .75-.125 1.125-.25m-2.25 0c.375.125.75.25 1.125.25.375 0 .75-.125 1.125-.25" /></svg>
              WhatsApp: (47) 99682-9294
            </a>
          </div>
        </div>
      </div>
    </footer>
  );
} 