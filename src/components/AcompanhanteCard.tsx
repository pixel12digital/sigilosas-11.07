'use client';

import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import { ShieldCheckIcon, SparklesIcon, FireIcon, CheckBadgeIcon } from '@heroicons/react/24/solid';
import { Database } from '@/lib/database.types';

type Acompanhante = Database['public']['Tables']['acompanhantes']['Row'] & {
  fotos: Pick<Database['public']['Tables']['fotos']['Row'], 'url' | 'storage_path' | 'tipo' | 'principal'>[];
  cidades: Pick<Database['public']['Tables']['cidades']['Row'], 'nome' | 'estado'> | null;
};

interface AcompanhanteCardProps {
  acompanhante: Acompanhante;
  cidadeNome?: string;
}

// Componentes de Ícones
const AgeIcon = () => <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>;
const PhotoIcon = () => <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>;
const PinIcon = () => <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>;
const PhoneIcon = () => <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>;

// Componente Principal do Card
export default function AcompanhanteCard({ acompanhante, cidadeNome }: AcompanhanteCardProps) {
  const [imageError, setImageError] = useState(false);
  const supabase = createClientComponentClient<Database>();

  const getPublicUrl = (path: string) => {
    if (!path) return '/assets/img/placeholder.svg';
    const { data } = supabase.storage.from('media').getPublicUrl(path);
    return data.publicUrl;
  };

  const galleryPhotos = acompanhante.fotos?.filter(foto => foto.tipo === 'galeria') || [];
  const galleryPhotosCount = galleryPhotos.length;

  const principalPhoto = acompanhante.fotos?.find(foto => foto.principal);
  
  let fotoCapa = '/assets/img/placeholder.svg';
  if (principalPhoto) {
    fotoCapa = getPublicUrl(principalPhoto.storage_path);
  } else if (galleryPhotos.length > 0) {
    fotoCapa = getPublicUrl(galleryPhotos[0].storage_path);
  }

  const formatarValor = (valor?: number | null) => {
    if (!valor) return 'Sob consulta';
    return `R$ ${valor.toFixed(2).replace('.', ',')}`;
  };

  return (
    <div className="bg-white rounded-xl shadow-md overflow-hidden transition-shadow hover:shadow-lg flex flex-col h-full">
      {/* Carrossel de Imagens */}
      <div className="relative h-64 w-full">
         <Image
          src={imageError ? '/assets/img/placeholder.svg' : fotoCapa}
          alt={`Foto de ${acompanhante.nome}`}
          fill
          className="object-cover"
          sizes="(max-width: 640px) 100vw, (max-width: 768px) 50vw, 33vw"
          onError={() => setImageError(true)}
        />
      </div>

      {/* Conteúdo do Card */}
      <div className="p-4 flex-grow flex flex-col">
        <h3 className="font-bold text-xl text-gray-800">{acompanhante.nome}</h3>
        {acompanhante.valor_padrao ? (
          <div className="mt-2">
            <span className="text-base font-bold text-black">
              R$ {Number(acompanhante.valor_padrao).toFixed(2)} / hora
            </span>
          </div>
        ) : (
          <div className="mt-2">
            <span className="text-base font-bold text-gray-600">
              A combinar
            </span>
          </div>
        )}
        <p className="text-sm text-gray-500 mb-4">{acompanhante.etnia || "Morena perfeita"}</p>

        <div className="grid grid-cols-2 gap-4 flex-grow">
          {/* Coluna da Esquerda */}
          <div className="space-y-2 text-sm text-gray-700">
            <div className="flex items-center gap-2">
              <AgeIcon />
              <span>{acompanhante.idade} anos</span>
            </div>
            <div className="flex items-center gap-2">
              <PhotoIcon />
              <span>{galleryPhotosCount} fotos</span>
            </div>
            <div className="flex items-center gap-2">
              <PinIcon />
              <span>{acompanhante.bairro || cidadeNome}</span>
            </div>
            <div className="flex items-center space-x-2">
              {acompanhante.verificado && <CheckBadgeIcon className="w-5 h-5 text-blue-500" title="Verificado" />}
              {acompanhante.silicone && <SparklesIcon className="w-5 h-5 text-pink-500" title="Silicone" />}
              {acompanhante.tatuagens && <FireIcon className="w-5 h-5 text-red-500" title="Tatuagens" />}
              {acompanhante.piercings && <ShieldCheckIcon className="w-5 h-5 text-gray-500" title="Piercings" />}
            </div>
          </div>

          {/* Coluna da Direita */}
          <div className="text-sm text-gray-600">
            <p className="line-clamp-6">{acompanhante.descricao}</p>
          </div>
        </div>

        {/* Botão Ver Perfil */}
        <div className="mt-4">
          <Link href={`/acompanhantes/${acompanhante.id}`} className="block w-full text-center py-3 px-4 bg-primary text-white rounded-lg font-semibold transition-colors hover:bg-primary-hover">
            <div className="flex items-center justify-center gap-2">
              <PhoneIcon />
              <span>Ver Perfil Completo</span>
            </div>
          </Link>
        </div>
      </div>
    </div>
  );
} 