'use client';

import { useState, useEffect } from 'react';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import Image from 'next/image';
import Link from 'next/link';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import LoadingSpinner from '@/components/LoadingSpinner';
import { Database } from '@/lib/database.types';

import {
  CheckBadgeIcon,
  MapPinIcon,
  CurrencyDollarIcon,
  ClockIcon,
  TagIcon,
  UserIcon,
  PhoneIcon,
  ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';

type Acompanhante = Database['public']['Tables']['acompanhantes']['Row'] & {
  fotos: Database['public']['Tables']['fotos']['Row'][];
  cidades: Database['public']['Tables']['cidades']['Row'] | null;
};

type Foto = Database['public']['Tables']['fotos']['Row'];

export default function AcompanhanteProfile({ params }: { params: { id: string } }) {
  const { id } = params;
  
  const [acompanhante, setAcompanhante] = useState<Acompanhante | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedImage, setSelectedImage] = useState<string | null>(null);

  const supabase = createClientComponentClient<Database>();

  useEffect(() => {
    const fetchAcompanhante = async () => {
      if (!id) return;
      
      setLoading(true);
      setError(null);
      
      try {
        const { data, error } = await supabase
          .from('acompanhantes')
          .select('*, fotos(*), cidades(*)')
          .eq('id', id)
          .eq('status', 'aprovado')
          .single();

        if (error || !data) {
          throw new Error('Perfil não encontrado ou não disponível.');
        }

        setAcompanhante(data as Acompanhante);
        
        const galleryPhotos = data.fotos?.filter((f: Foto) => f.tipo === 'galeria');
        const principalPhoto = data.fotos?.find((f: Foto) => f.principal);
        
        if (principalPhoto) {
          setSelectedImage(principalPhoto.storage_path);
        } else if (galleryPhotos && galleryPhotos.length > 0) {
          setSelectedImage(galleryPhotos[0].storage_path);
        }

      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchAcompanhante();
  }, [id, supabase]);

  const getPublicUrl = (path: string | null) => {
    if (!path) return '/assets/img/placeholder.svg';
    const { data } = supabase.storage.from('media').getPublicUrl(path);
    return data.publicUrl;
  };
  
  if (loading) {
    return (
      <div className="flex flex-col min-h-screen">
        <Header />
        <main className="flex-grow flex items-center justify-center">
          <LoadingSpinner />
        </main>
        <Footer />
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col min-h-screen">
        <Header />
        <main className="flex-grow flex flex-col items-center justify-center text-center px-4">
            <ExclamationTriangleIcon className="w-16 h-16 text-red-500 mb-4" />
            <h1 className="text-2xl font-bold text-gray-800">Erro ao Carregar Perfil</h1>
            <p className="text-gray-600 mt-2">{error}</p>
            <Link href="/" className="mt-6 px-6 py-2 bg-primary text-white rounded-lg font-semibold hover:bg-primary-hover">
                Voltar para a página inicial
            </Link>
        </main>
        <Footer />
      </div>
    );
  }

  if (!acompanhante) return null;

  const galleryPhotos = acompanhante.fotos?.filter((f: Foto) => f.tipo === 'galeria') || [];

  return (
    <div className="bg-gray-50">
      <Header />
      <main className="container mx-auto py-8 px-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Coluna da Galeria */}
          <div className="md:col-span-1">
            <div className="mb-4">
              <Image
                src={getPublicUrl(selectedImage)}
                alt={`Foto principal de ${acompanhante.nome}`}
                width={700}
                height={900}
                className="w-full h-auto object-cover rounded-lg shadow-lg"
              />
            </div>
            <div className="grid grid-cols-4 gap-2">
              {galleryPhotos.map((foto) => (
                <button key={foto.id} onClick={() => setSelectedImage(foto.storage_path)} className="focus:outline-none focus:ring-2 focus:ring-primary rounded-lg">
                  <Image
                    src={getPublicUrl(foto.storage_path)}
                    alt={`Thumbnail de ${acompanhante.nome}`}
                    width={150}
                    height={200}
                    className={`w-full h-auto object-cover rounded-md cursor-pointer ${selectedImage === foto.storage_path ? 'border-2 border-primary' : ''}`}
                  />
                </button>
              ))}
            </div>
          </div>

          {/* Coluna de Informações */}
          <div className="md:col-span-2">
            <div className="bg-white p-6 rounded-lg shadow-lg">
                <h1 className="text-4xl font-bold text-gray-800">{acompanhante.nome}</h1>
                <div className="flex items-center gap-4 text-gray-600 mt-2">
                    <div className="flex items-center gap-2">
                        <UserIcon className="w-5 h-5" />
                        <span>{acompanhante.idade} anos</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <MapPinIcon className="w-5 h-5" />
                        <span>{acompanhante.cidades?.nome} - {acompanhante.cidades?.estado}</span>
                    </div>
                    {acompanhante.verificado && <CheckBadgeIcon className="w-6 h-6 text-blue-500" title="Perfil Verificado"/>}
                </div>
                
                <p className="text-gray-700 my-4">{acompanhante.sobre_mim}</p>

                <a
                  href={`https://wa.me/55${acompanhante.whatsapp}?text=Olá, te encontrei no SigilosasVIP!`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full flex items-center justify-center gap-3 py-3 px-4 bg-green-500 text-white rounded-lg font-semibold text-lg hover:bg-green-600 transition-colors my-4"
                >
                    <PhoneIcon className="w-6 h-6" />
                    Entrar em Contato por WhatsApp
                </a>

                {acompanhante.descricao && (
                  <div className="mt-6 border-t pt-6">
                    <h3 className="text-xl font-bold text-gray-800 mb-2">Descrição Completa</h3>
                    <p className="text-gray-700 whitespace-pre-wrap">{acompanhante.descricao}</p>
                  </div>
                )}

                <h2 className="text-2xl font-bold text-gray-800 border-t pt-6 mt-6">Detalhes</h2>
                <dl className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    {/* Informações Pessoais */}
                    {acompanhante.idade && <div><dt className="font-semibold text-gray-600">Idade</dt><dd className="text-gray-800">{acompanhante.idade} anos</dd></div>}
                    {acompanhante.genero && <div><dt className="font-semibold text-gray-600">Gênero</dt><dd className="text-gray-800">{acompanhante.genero}</dd></div>}
                    {acompanhante.etnia && <div><dt className="font-semibold text-gray-600">Etnia</dt><dd className="text-gray-800">{acompanhante.etnia}</dd></div>}
                    {acompanhante.idiomas && acompanhante.idiomas.length > 0 && <div><dt className="font-semibold text-gray-600">Idiomas</dt><dd className="text-gray-800">{Array.isArray(acompanhante.idiomas) ? acompanhante.idiomas.join(', ') : acompanhante.idiomas}</dd></div>}

                    <div className="md:col-span-2"><hr/></div>
                    
                    {/* Aparência */}
                    {acompanhante.altura && <div><dt className="font-semibold text-gray-600">Altura</dt><dd className="text-gray-800">{acompanhante.altura} m</dd></div>}
                    {acompanhante.peso && <div><dt className="font-semibold text-gray-600">Peso</dt><dd className="text-gray-800">{acompanhante.peso} kg</dd></div>}
                    {acompanhante.manequim && <div><dt className="font-semibold text-gray-600">Manequim</dt><dd className="text-gray-800">{acompanhante.manequim}</dd></div>}
                    {acompanhante.tamanho_pe && <div><dt className="font-semibold text-gray-600">Tamanho do Pé</dt><dd className="text-gray-800">{acompanhante.tamanho_pe}</dd></div>}
                    {acompanhante.busto && <div><dt className="font-semibold text-gray-600">Busto</dt><dd className="text-gray-800">{acompanhante.busto} cm</dd></div>}
                    {acompanhante.cintura && <div><dt className="font-semibold text-gray-600">Cintura</dt><dd className="text-gray-800">{acompanhante.cintura} cm</dd></div>}
                    {acompanhante.quadril && <div><dt className="font-semibold text-gray-600">Quadril</dt><dd className="text-gray-800">{acompanhante.quadril} cm</dd></div>}
                    {acompanhante.cor_olhos && <div><dt className="font-semibold text-gray-600">Cor dos Olhos</dt><dd className="text-gray-800">{acompanhante.cor_olhos}</dd></div>}
                    {acompanhante.cor_cabelo && <div><dt className="font-semibold text-gray-600">Cor do Cabelo</dt><dd className="text-gray-800">{acompanhante.cor_cabelo}</dd></div>}
                    {acompanhante.estilo_cabelo && <div><dt className="font-semibold text-gray-600">Estilo do Cabelo</dt><dd className="text-gray-800">{acompanhante.estilo_cabelo}</dd></div>}
                    {acompanhante.tamanho_cabelo && <div><dt className="font-semibold text-gray-600">Tamanho do Cabelo</dt><dd className="text-gray-800">{acompanhante.tamanho_cabelo}</dd></div>}

                    <div className="md:col-span-2"><hr/></div>

                    {/* Preferências e Atributos */}
                    {acompanhante.preferencia_sexual && <div><dt className="font-semibold text-gray-600">Preferência Sexual</dt><dd className="text-gray-800">{acompanhante.preferencia_sexual}</dd></div>}
                    {acompanhante.genitalia && <div><dt className="font-semibold text-gray-600">Genitália</dt><dd className="text-gray-800">{acompanhante.genitalia}</dd></div>}
                    {acompanhante.silicone !== null && <div><dt className="font-semibold text-gray-600">Silicone</dt><dd className="text-gray-800">{acompanhante.silicone ? 'Sim' : 'Não'}</dd></div>}
                    {acompanhante.tatuagens !== null && <div><dt className="font-semibold text-gray-600">Tatuagens</dt><dd className="text-gray-800">{acompanhante.tatuagens ? 'Sim' : 'Não'}</dd></div>}
                    {acompanhante.piercings !== null && <div><dt className="font-semibold text-gray-600">Piercings</dt><dd className="text-gray-800">{acompanhante.piercings ? 'Sim' : 'Não'}</dd></div>}
                    {acompanhante.fumante !== null && <div><dt className="font-semibold text-gray-600">Fumante</dt><dd className="text-gray-800">{acompanhante.fumante ? 'Sim' : 'Não'}</dd></div>}
                    
                    <div className="md:col-span-2"><hr/></div>
                    
                    {/* Informações de Atendimento */}
                    {acompanhante.local_atendimento && acompanhante.local_atendimento.length > 0 && <div>
                        <dt className="font-semibold text-gray-600">Local de Atendimento</dt>
                        <dd className="text-gray-800">{Array.isArray(acompanhante.local_atendimento) ? acompanhante.local_atendimento.join(', ') : acompanhante.local_atendimento}</dd>
                    </div>}
                     {acompanhante.bairro && <div>
                        <dt className="font-semibold text-gray-600">Bairro</dt>
                        <dd className="text-gray-800">{acompanhante.bairro}</dd>
                    </div>}
                    {acompanhante.especialidades && acompanhante.especialidades.length > 0 && <div className="md:col-span-2">
                        <dt className="font-semibold text-gray-600">Especialidades</dt>
                        <dd className="text-gray-800">{Array.isArray(acompanhante.especialidades) ? acompanhante.especialidades.join(', ') : acompanhante.especialidades}</dd>
                    </div>}
                     {acompanhante.formas_pagamento && acompanhante.formas_pagamento.length > 0 && <div className="md:col-span-2">
                        <dt className="font-semibold text-gray-600">Formas de Pagamento</dt>
                        <dd className="text-gray-800">{Array.isArray(acompanhante.formas_pagamento) ? acompanhante.formas_pagamento.join(', ') : acompanhante.formas_pagamento}</dd>
                    </div>}
                    {acompanhante.valor_padrao && <div className="md:col-span-2">
                        <dt className="font-semibold text-gray-600">Valores</dt>
                        <dd className="text-gray-800">A partir de R$ {acompanhante.valor_padrao}</dd>
                        {acompanhante.valor_promocional && <dd className="text-green-600">Valor Promocional: R$ {acompanhante.valor_promocional}</dd>}
                    </div>}
                    
                    {(acompanhante.instagram || acompanhante.twitter || acompanhante.tiktok || acompanhante.site) && <div className="md:col-span-2"><hr/></div>}

                    {/* Redes Sociais */}
                    {(acompanhante.instagram || acompanhante.twitter || acompanhante.tiktok || acompanhante.site) && <div className="md:col-span-2">
                        <dt className="font-semibold text-gray-600">Redes Sociais</dt>
                        <dd className="flex items-center space-x-4 mt-1">
                            {acompanhante.instagram && <a href={`https://instagram.com/${acompanhante.instagram}`} target="_blank" rel="noopener noreferrer" className="text-primary hover:underline">Instagram</a>}
                            {acompanhante.twitter && <a href={`https://twitter.com/${acompanhante.twitter}`} target="_blank" rel="noopener noreferrer" className="text-primary hover:underline">Twitter</a>}
                            {acompanhante.tiktok && <a href={`https://tiktok.com/@${acompanhante.tiktok}`} target="_blank" rel="noopener noreferrer" className="text-primary hover:underline">TikTok</a>}
                            {acompanhante.site && <a href={acompanhante.site} target="_blank" rel="noopener noreferrer" className="text-primary hover:underline">Site Pessoal</a>}
                        </dd>
                    </div>}
                </dl>
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
} 