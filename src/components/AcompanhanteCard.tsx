'use client';

import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import type { Acompanhante } from '@/lib/supabase';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';

interface AcompanhanteCardProps {
  acompanhante: Acompanhante & {
    cidades?: { nome: string };
    fotos?: { url: string; capa: boolean }[];
  };
}

export default function AcompanhanteCard({ acompanhante }: AcompanhanteCardProps) {
  const [isFavorite, setIsFavorite] = useState(false);
  const [imageError, setImageError] = useState(false);
  const supabase = createClientComponentClient();

  // Adicionar logs para debug
  console.log('Dados do acompanhante:', {
    id: acompanhante.id,
    nome: acompanhante.nome,
    foto: acompanhante.foto,
    fotos: acompanhante.fotos
  });

  const fotoCapa = acompanhante.fotos?.find(foto => foto.capa)?.url || 
                   acompanhante.fotos?.[0]?.url || 
                   acompanhante.foto ||
                   '/assets/img/placeholder.jpg';
  
  console.log('Foto selecionada:', fotoCapa);

  // Função para obter URL pública da foto
  const getFotoUrl = (fotoPath: string) => {
    if (!fotoPath) return '';
    const { data: { publicUrl } } = supabase.storage
      .from('perfil')
      .getPublicUrl(fotoPath.split('/').pop() || '');
    return publicUrl;
  };

  const handleFavorite = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsFavorite(!isFavorite);
    // TODO: Implementar favoritos no Supabase
  };

  const formatarValor = (valor?: number) => {
    if (!valor) return 'Sob consulta';
    return `R$ ${valor.toFixed(2)}`;
  };

  const formatarIdade = (idade?: number) => {
    if (!idade) return '';
    return `${idade} anos`;
  };

  return (
    <Link href={`/acompanhantes/${acompanhante.id}`} className="block">
      <div className="card hover:shadow-xl transition-all duration-300 group">
        {/* Imagem */}
        <div className="relative mb-4 overflow-hidden rounded-lg">
          <Image
            src={imageError ? '/assets/img/placeholder.jpg' : getFotoUrl(fotoCapa) || '/assets/img/placeholder.png'}
            alt={`Foto de ${acompanhante.nome}`}
            width={300}
            height={400}
            className="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300"
            onError={(e) => {
              console.error('Erro ao carregar imagem:', {
                src: e.currentTarget.src,
                error: e
              });
              setImageError(true);
            }}
          />
          
          {/* Badges */}
          <div className="absolute top-2 left-2 flex gap-2">
            {acompanhante.destaque && (
              <span className="bg-[#CA5272] text-white text-xs px-2 py-1 rounded-full">
                Destaque
              </span>
            )}
            {acompanhante.verificado && (
              <span className="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                ✓ Verificada
              </span>
            )}
          </div>

          {/* Botão favorito */}
          <button
            onClick={handleFavorite}
            className="absolute top-2 right-2 p-2 bg-white bg-opacity-80 rounded-full hover:bg-opacity-100 transition-all"
            aria-label={isFavorite ? 'Remover dos favoritos' : 'Adicionar aos favoritos'}
          >
            <svg 
              width="20" 
              height="20" 
              fill={isFavorite ? '#CA5272' : 'none'} 
              stroke="#CA5272" 
              strokeWidth="2" 
              viewBox="0 0 24 24"
            >
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </button>
        </div>

        {/* Informações */}
        <div className="space-y-2">
          <h3 className="text-xl font-semibold text-[#2E1530] group-hover:text-[#CA5272] transition-colors">
            {acompanhante.nome}
          </h3>
          
          <div className="flex items-center gap-2 text-sm text-gray-600">
            {(Array.isArray(acompanhante.cidades) ? acompanhante.cidades[0]?.nome : acompanhante.cidades?.nome) && (
              <>
                <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>{Array.isArray(acompanhante.cidades) ? acompanhante.cidades[0]?.nome : acompanhante.cidades?.nome}</span>
              </>
            )}
            {acompanhante.bairro && (
              <>
                <span>•</span>
                <span>{acompanhante.bairro}</span>
              </>
            )}
          </div>

          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              {formatarIdade(acompanhante.idade) && (
                <span className="text-sm text-gray-600">
                  {formatarIdade(acompanhante.idade)}
                </span>
              )}
              {acompanhante.etnia && (
                <>
                  <span>•</span>
                  <span className="text-sm text-gray-600">{acompanhante.etnia}</span>
                </>
              )}
            </div>
            
            <div className="text-right">
              <div className="text-lg font-bold text-[#CA5272]">
                {formatarValor(acompanhante.valor)}
              </div>
              {acompanhante.aceita_pix && (
                <div className="text-xs text-green-600">Aceita PIX</div>
              )}
            </div>
          </div>

          {/* Características */}
          <div className="flex flex-wrap gap-1 pt-2">
            {acompanhante.silicone && (
              <span className="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                Silicone
              </span>
            )}
            {acompanhante.tatuagens && (
              <span className="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">
                Tatuagens
              </span>
            )}
            {acompanhante.piercings && (
              <span className="text-xs bg-pink-100 text-pink-800 px-2 py-1 rounded">
                Piercings
              </span>
            )}
            {acompanhante.atende_casal && (
              <span className="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                Atende casal
              </span>
            )}
            {acompanhante.local_proprio && (
              <span className="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                Local próprio
              </span>
            )}
          </div>

          {/* Estatísticas */}
          <div className="flex items-center justify-between pt-2 border-t border-gray-100">
            <div className="flex items-center gap-4 text-sm text-gray-600">
              <span>❤️ {acompanhante.favoritos}</span>
              <span>👁️ {acompanhante.seguidores}</span>
            </div>
            
            <div className="flex items-center gap-1">
              {[1, 2, 3, 4, 5].map((star) => (
                <svg 
                  key={star}
                  width="16" 
                  height="16" 
                  fill={star <= 4 ? '#FFD700' : 'none'} 
                  stroke="#FFD700" 
                  strokeWidth="2" 
                  viewBox="0 0 24 24"
                >
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              ))}
            </div>
          </div>
        </div>

        <div className="flex items-center justify-end mt-2">
          <Link href={`/acompanhantes/${acompanhante.id}`} target="_blank" rel="noopener noreferrer" className="text-xs text-blue-600 underline flex items-center gap-1">
            <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M14 3h7v7"/><path d="M5 19l14-14"/></svg>
            Ver perfil público
          </Link>
        </div>
      </div>
    </Link>
  );
} 