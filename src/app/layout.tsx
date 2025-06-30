import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import FloatAdminButton from '@/components/FloatAdminButton';

const inter = Inter({ subsets: ['latin'] });

export const metadata: Metadata = {
  title: 'Sigilosas VIP - Encontre acompanhantes',
  description: 'Encontre acompanhantes de alto nível, com filtros avançados, avaliações, fotos e total discrição. Cadastre-se grátis!',
  keywords: 'acompanhantes, massagem, serviços, discrição',
  authors: [{ name: 'Sigilosas VIP' }],
  openGraph: {
    title: 'Sigilosas VIP - Encontre acompanhantes de alto nível',
    description: 'Encontre acompanhantes de alto nível, com filtros avançados, avaliações, fotos e total discrição. Cadastre-se grátis!',
    type: 'website',
    locale: 'pt_BR',
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="pt-BR">
      <body className={inter.className}>
        {children}
        <FloatAdminButton />
      </body>
    </html>
  );
} 