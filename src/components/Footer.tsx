export default function Footer() {
  return (
    <footer className="bg-gray-100 py-8 mt-10 border-t border-accent">
      <div className="max-w-7xl mx-auto text-center">
        <p className="text-secondary font-semibold text-lg mb-3">
          &copy; {new Date().getFullYear()} SigilosasVip. Todos os direitos reservados.
        </p>
        <nav className="mb-2">
          <a href="/sobre" className="text-secondary mx-2 hover:text-accent transition-colors">
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
      </div>
    </footer>
  );
} 