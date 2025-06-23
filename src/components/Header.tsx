'use client';

import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';

interface HeaderProps {
  config?: Record<string, string>;
}

export default function Header({ config }: HeaderProps) {
  const [menuOpen, setMenuOpen] = useState(false);

  const toggleMenu = () => {
    setMenuOpen(!menuOpen);
  };

  const closeMenu = () => {
    setMenuOpen(false);
  };

  return (
    <>
      <header className="bg-white shadow-md relative border-b border-[#CFB78B]">
        <div className="flex items-center justify-between gap-4 p-4 max-w-7xl mx-auto">
          <div className="flex items-center gap-4">
            <button 
              onClick={toggleMenu}
              className="bg-none border-none outline-none cursor-pointer flex items-center md:hidden"
              aria-label="Abrir menu"
            >
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect y="5" width="24" height="2.5" rx="1" fill="#CFB78B"/>
                <rect y="11" width="24" height="2.5" rx="1" fill="#CFB78B"/>
                <rect y="17" width="24" height="2.5" rx="1" fill="#CFB78B"/>
              </svg>
            </button>
            <Link href="/" className="flex items-center gap-2 ml-8">
              <Image 
                src={'/assets/img/logo.png'} 
                alt="SigilosasVip - Logo" 
                width={180} 
                height={48}
                className="h-12 w-auto"
              />
            </Link>
          </div>
          {/* Botão cadastro animado no topo direito */}
          <Link href="/cadastro" className="ml-auto flex items-center gap-2 group md:hidden" title="Cadastre-se">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="#D0BA90" strokeWidth="2" />
              <path d="M12 13c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1h16v-1c0-2.66-5.33-4-8-4z" fill="#D0BA90" />
            </svg>
            <span className="text-[#4E3950] font-semibold animate-pulse group-hover:text-[#D0BA90] transition-colors duration-300">Cadastre-se</span>
          </Link>
          
          <nav className="hidden md:flex items-center gap-6">
            <Link href="/" className="text-[#4E3950] hover:text-[#CFB78B] transition-colors">
              Início
            </Link>
            <Link href="/acompanhantes" className="text-[#4E3950] hover:text-[#CFB78B] transition-colors">
              Acompanhantes
            </Link>
            <Link href="/blog" className="text-[#4E3950] hover:text-[#CFB78B] transition-colors">
              Blog
            </Link>
            <Link href="/#sobre" className="text-[#4E3950] hover:text-[#CFB78B] transition-colors">
              Sobre
            </Link>
            <Link href="/cadastro" className="btn-secondary">
              Cadastre-se
            </Link>
          </nav>
        </div>
      </header>

      {/* Menu lateral */}
      <nav className={`fixed top-0 left-0 h-full w-80 bg-white shadow-2xl transform transition-transform duration-300 z-50 ${menuOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <button 
          onClick={closeMenu}
          className="absolute top-4 right-4 text-2xl text-gray-600 hover:text-gray-800"
          aria-label="Fechar menu"
        >
          &times;
        </button>
        
        <div className="p-6 border-b border-gray-200">
          <Image 
            src={'/assets/img/logo.png'} 
            alt="SigilosasVip" 
            width={160} 
            height={40}
            className="h-10 w-auto"
          />
        </div>
        
        <ul className="p-4">
          <li className="mb-2">
            <Link 
              href="/" 
              onClick={closeMenu}
              className="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <span className="text-lg">🏠</span>
              Início
            </Link>
          </li>
          <li className="mb-2">
            <Link 
              href="/acompanhantes" 
              onClick={closeMenu}
              className="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <span className="text-lg">🔍</span>
              Acompanhantes
            </Link>
          </li>
          <li className="mb-2">
            <Link 
              href="/blog" 
              onClick={closeMenu}
              className="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <span className="text-lg">📝</span>
              Blog
            </Link>
          </li>
          <li className="mb-2">
            <Link 
              href="/#sobre" 
              onClick={closeMenu}
              className="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <span className="text-lg">ℹ️</span>
              Sobre
            </Link>
          </li>
        </ul>
        
        <ul className="p-4 border-t border-gray-200">
          <li className="mb-2">
            <Link 
              href="/cadastro" 
              onClick={closeMenu}
              className="flex items-center gap-3 p-3 rounded-lg bg-[#4E3950] text-white hover:bg-[#B89C78] transition-colors"
            >
              <span className="text-lg">👤</span>
              Cadastre-se
            </Link>
          </li>
        </ul>
        
        <div className="absolute bottom-4 left-4 right-4 text-center text-sm text-gray-600">
          <div className="flex flex-wrap justify-center gap-4">
            <Link href="/termos" onClick={closeMenu}>Termos de Uso</Link>
            <Link href="/privacidade" onClick={closeMenu}>Privacidade</Link>
            <Link href="/contato" onClick={closeMenu}>Contato</Link>
          </div>
        </div>
      </nav>

      {/* Overlay para fechar menu */}
      {menuOpen && (
        <div 
          className="fixed inset-0 bg-black bg-opacity-50 z-40"
          onClick={closeMenu}
        />
      )}
    </>
  );
} 