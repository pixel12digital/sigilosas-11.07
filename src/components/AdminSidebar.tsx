'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';

interface AdminSidebarProps {
  active?: string;
}

export default function AdminSidebar({ active }: AdminSidebarProps) {
  const pathname = usePathname();

  const menuItems = [
    { href: '/painel', icon: '🏠', label: 'Dashboard', active: pathname === '/painel' },
    { href: '/painel/acompanhantes', icon: '👥', label: 'Acompanhantes', active: pathname === '/painel/acompanhantes' },
    { href: '/painel/avaliacoes', icon: '⭐', label: 'Avaliações', active: pathname === '/painel/avaliacoes' },
    { href: '/painel/denuncias', icon: '🚩', label: 'Denúncias', active: pathname === '/painel/denuncias' },
    { href: '/painel/estatisticas', icon: '📊', label: 'Estatísticas', active: pathname === '/painel/estatisticas' },
    { href: '/painel/cidades', icon: '🏙️', label: 'Cidades', active: pathname === '/painel/cidades' },
    { href: '/painel/servicos', icon: '💼', label: 'Serviços', active: pathname === '/painel/servicos' },
    { href: '/painel/configuracoes', icon: '⚙️', label: 'Configurações', active: pathname === '/painel/configuracoes' },
    { href: '/painel/blog', icon: '📝', label: 'Blog', active: pathname === '/painel/blog' },
    { href: '/painel/usuarios', icon: '👤', label: 'Usuários', active: pathname === '/painel/usuarios' },
  ];

  return (
    <aside className="fixed top-0 left-0 h-screen bg-[#4E3950] text-[#CFB78B] flex flex-col items-stretch shadow-[2px_0_16px_rgba(44,21,48,0.08)] z-[1001] transition-all duration-200 w-16 lg:w-56">
      <div className="py-8 flex items-center justify-center text-xl font-bold tracking-wide text-[#CFB78B] bg-[#3a2340] lg:text-base">
        <span className="lg:hidden">SV</span>
        <span className="hidden lg:block">SigilosasVIP</span>
      </div>
      <nav className="flex-1 flex flex-col gap-1.5 py-4">
        {menuItems.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            className={`flex items-center gap-3 px-0 lg:px-6 py-2.5 rounded-lg mb-1 font-medium transition-all duration-200 text-base hover:bg-[#CFB78B] hover:text-[#4E3950] ${
              item.active ? 'bg-[#CFB78B] text-[#4E3950]' : 'text-[#CFB78B]'
            }`}
          >
            <span className="text-xl w-16 text-center">{item.icon}</span>
            <span className="hidden lg:inline whitespace-nowrap">{item.label}</span>
          </Link>
        ))}
      </nav>
      <div className="py-4.5 text-center border-t border-[#CFB78B]">
        <Link
          href="/"
          className="flex items-center justify-center gap-2 text-[#4E3950] bg-[#CFB78B] rounded-md py-2 px-0 lg:px-6 no-underline font-semibold transition-all duration-200 hover:bg-white hover:text-[#CFB78B]"
        >
          <span className="text-lg w-16 text-center">🌐</span>
          <span className="hidden lg:inline ml-2">Ver Site</span>
        </Link>
      </div>
      <div className="py-4.5 text-center border-t border-[#CFB78B]">
        <Link
          href="/login?logout=1"
          className="flex items-center justify-center gap-2 text-[#4E3950] bg-[#CFB78B] rounded-md py-2 px-0 lg:px-6 no-underline font-semibold transition-all duration-200 hover:bg-white hover:text-[#CFB78B]"
        >
          <span className="text-lg w-16 text-center">🚪</span>
          <span className="hidden lg:inline ml-2">Sair</span>
        </Link>
      </div>
    </aside>
  );
} 