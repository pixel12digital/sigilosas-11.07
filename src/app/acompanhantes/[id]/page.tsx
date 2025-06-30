'use client';

import { useState, useEffect } from 'react';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import Image from 'next/image';
import Link from 'next/link';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import LoadingSpinner from '@/components/LoadingSpinner';
import { Database } from '@/lib/database.types';
import { StarIcon } from '@heroicons/react/24/solid';

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
  tipo_atendimento?: string;
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
  const [avaliacoes, setAvaliacoes] = useState<any[]>([]);
  const [form, setForm] = useState({ nome: '', nota: 5, comentario: '' });
  const [enviando, setEnviando] = useState(false);
  const [msg, setMsg] = useState('');
  const [showDenuncia, setShowDenuncia] = useState(false);
  const [denuncia, setDenuncia] = useState({ nome: '', motivo: '', descricao: '' });
  const [msgDenuncia, setMsgDenuncia] = useState('');
  const [enviandoDenuncia, setEnviandoDenuncia] = useState(false);

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

  // Buscar avaliações aprovadas
  useEffect(() => {
    const fetchAvaliacoes = async () => {
      const { data, error } = await supabase
        .from('avaliacoes')
        .select('*')
        .eq('acompanhante_id', id)
        .eq('status', 'aprovado')
        .order('created_at', { ascending: false });
      if (!error) setAvaliacoes(data || []);
    };
    fetchAvaliacoes();
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

                <div className="flex flex-col items-start gap-2 my-4">
                  {acompanhante.telefone && (
                    <a
                      href={`https://wa.me/${acompanhante.telefone.replace(/\D/g, '')}?text=${encodeURIComponent('Olá, vi seu perfil no Sigilosas VIP e gostaria de conversar!')}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center justify-center bg-green-500 hover:bg-green-600 text-white font-medium rounded px-4 py-2 text-sm transition"
                      style={{ minWidth: 0 }}
                    >
                      <svg className="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M20.52 3.48A12.07 12.07 0 0012 0C5.37 0 0 5.37 0 12a11.93 11.93 0 001.64 6.06L0 24l6.18-1.62A11.93 11.93 0 0012 24c6.63 0 12-5.37 12-12 0-3.19-1.24-6.19-3.48-8.52zM12 22a9.93 9.93 0 01-5.3-1.53l-.38-.23-3.67.97.98-3.58-.25-.37A9.93 9.93 0 012 12c0-5.52 4.48-10 10-10s10 4.48 10 10-4.48 10-10 10zm5.2-7.8c-.28-.14-1.65-.81-1.9-.9-.25-.09-.43-.14-.61.14-.18.28-.28.7.9-.86 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.39-1.65-1.55-1.93-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.51.14-.17.18-.29.28-.48.09-.19.05-.36-.02-.5-.07-.14-.61-1.47-.84-2.01-.22-.53-.45-.46-.61-.47-.16-.01-.36-.01-.56-.01-.19 0-.5.07-.76.34-.26.27-1 1-.97 2.43.03 1.43 1.03 2.81 1.18 3.01.15.2 2.03 3.1 4.93 4.23.69.3 1.23.48 1.65.61.69.22 1.32.19 1.81.12.55-.08 1.65-.67 1.89-1.32.23-.65.23-1.2.16-1.32-.07-.12-.25-.19-.53-.33z"/></svg>
                      WhatsApp
                    </a>
                  )}
                  {acompanhante.valor_padrao && (
                    <span className="text-2xl font-bold text-green-700 bg-yellow-100 px-4 py-2 rounded shadow inline-block">
                      Valor do Atendimento: R$ {Number(acompanhante.valor_padrao).toFixed(2)} / hora
                    </span>
                  )}
                </div>

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
                    {acompanhante.tipo_atendimento && (
                      <div>
                        <dt className="font-semibold text-gray-600">Tipo de Atendimento</dt>
                        <dd className="text-gray-800">
                          {acompanhante.tipo_atendimento === 'presencial' && <span title="Presencial">🏢 Presencial</span>}
                          {acompanhante.tipo_atendimento === 'online' && <span title="Online">💻 Online</span>}
                          {acompanhante.tipo_atendimento === 'ambos' && <span title="Ambos">🌐 Ambos</span>}
                        </dd>
                      </div>
                    )}
                    {acompanhante.valor_padrao && (
                      <div>
                        <dt className="font-semibold text-gray-600">Valor do Atendimento</dt>
                        <dd className="text-gray-800">R$ {Number(acompanhante.valor_padrao).toFixed(2)}</dd>
                      </div>
                    )}
                    {acompanhante.valor_observacao && (
                      <div>
                        <dt className="font-semibold text-gray-600">Observações sobre o valor</dt>
                        <dd className="text-gray-800">{acompanhante.valor_observacao}</dd>
                      </div>
                    )}

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

                    {acompanhante.telefone && (
                      <div>
                        <dt className="font-semibold text-gray-600">Telefone (WhatsApp)</dt>
                        <dd className="text-gray-800">{acompanhante.telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')}</dd>
                      </div>
                    )}
                </dl>
            </div>

            {/* Avaliações */}
            <div className="bg-white p-6 rounded-lg shadow-lg mt-8">
              <h2 className="text-2xl font-bold text-gray-800 mb-4">Avaliações</h2>
              {/* Botão discreto para denúncia */}
              <div className="flex justify-end mb-2">
                <button
                  className="text-xs text-gray-400 underline hover:text-red-500 transition-colors"
                  onClick={() => setShowDenuncia(v => !v)}
                  type="button"
                >
                  {showDenuncia ? 'Fechar denúncia' : 'Denunciar perfil'}
                </button>
              </div>
              {showDenuncia && (
                <form
                  onSubmit={async (e) => {
                    e.preventDefault();
                    setEnviandoDenuncia(true);
                    setMsgDenuncia('');
                    const { error } = await supabase.from('denuncias').insert({
                      acompanhante_id: id,
                      nome_exibicao: denuncia.nome || 'Anônimo',
                      motivo: denuncia.motivo,
                      descricao: denuncia.descricao,
                      status: 'pendente',
                    });
                    if (error) {
                      setMsgDenuncia('Erro ao enviar denúncia. Tente novamente.');
                    } else {
                      setMsgDenuncia('Denúncia enviada para análise!');
                      setDenuncia({ nome: '', motivo: '', descricao: '' });
                      setShowDenuncia(false);
                    }
                    setEnviandoDenuncia(false);
                  }}
                  className="mb-4 p-4 border rounded-lg bg-gray-50"
                >
                  <div className="mb-2">
                    <label className="block text-xs font-medium text-gray-600 mb-1">Nome (opcional)</label>
                    <input
                      type="text"
                      value={denuncia.nome}
                      onChange={e => setDenuncia({ ...denuncia, nome: e.target.value })}
                      className="w-full px-2 py-1 border border-gray-300 rounded-md text-sm"
                      placeholder="Seu nome ou deixe em branco para anônimo"
                    />
                  </div>
                  <div className="mb-2">
                    <label className="block text-xs font-medium text-gray-600 mb-1">Motivo *</label>
                    <input
                      type="text"
                      value={denuncia.motivo}
                      onChange={e => setDenuncia({ ...denuncia, motivo: e.target.value })}
                      className="w-full px-2 py-1 border border-gray-300 rounded-md text-sm"
                      placeholder="Motivo da denúncia (ex: comportamento, fotos falsas, etc)"
                      required
                    />
                  </div>
                  <div className="mb-2">
                    <label className="block text-xs font-medium text-gray-600 mb-1">Descrição *</label>
                    <textarea
                      value={denuncia.descricao}
                      onChange={e => setDenuncia({ ...denuncia, descricao: e.target.value })}
                      className="w-full px-2 py-1 border border-gray-300 rounded-md text-sm"
                      placeholder="Descreva o ocorrido"
                      required
                    />
                  </div>
                  <button
                    type="submit"
                    disabled={enviandoDenuncia}
                    className="bg-red-500 text-white px-4 py-1 rounded-md text-sm font-semibold hover:bg-red-600 transition-colors disabled:opacity-50"
                  >
                    {enviandoDenuncia ? 'Enviando...' : 'Enviar denúncia'}
                  </button>
                  {msgDenuncia && <div className="mt-2 text-center text-sm text-primary">{msgDenuncia}</div>}
                </form>
              )}
              {avaliacoes.length === 0 && (
                <p className="text-gray-500 mb-4">Nenhuma avaliação aprovada ainda.</p>
              )}
              <ul className="space-y-4 mb-8">
                {avaliacoes.map((a) => (
                  <li key={a.id} className="border-b pb-2">
                    <div className="flex items-center gap-2 mb-1">
                      {[...Array(5)].map((_, i) => (
                        <StarIcon key={i} className={`w-5 h-5 ${i < a.nota ? 'text-yellow-400' : 'text-gray-300'}`} />
                      ))}
                      <span className="ml-2 font-semibold text-gray-700">{a.nome_exibicao || 'Anônimo'}</span>
                      <span className="ml-2 text-xs text-gray-400">{new Date(a.created_at).toLocaleDateString()}</span>
                    </div>
                    <div className="text-gray-700">{a.comentario}</div>
                  </li>
                ))}
              </ul>
              <h3 className="text-lg font-bold mb-2">Deixe sua avaliação</h3>
              <form
                onSubmit={async (e) => {
                  e.preventDefault();
                  setEnviando(true);
                  setMsg('');
                  const { error } = await supabase.from('avaliacoes').insert({
                    acompanhante_id: id,
                    nome_exibicao: form.nome || 'Anônimo',
                    nota: form.nota,
                    comentario: form.comentario,
                    status: 'pendente',
                  });
                  if (error) {
                    setMsg('Erro ao enviar avaliação. Tente novamente.');
                  } else {
                    setMsg('Avaliação enviada para moderação!');
                    setForm({ nome: '', nota: 5, comentario: '' });
                  }
                  setEnviando(false);
                }}
                className="space-y-4"
              >
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nome (opcional)</label>
                  <input
                    type="text"
                    value={form.nome}
                    onChange={e => setForm({ ...form, nome: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Seu nome ou deixe em branco para anônimo"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                  <div className="flex items-center gap-1">
                    {[1,2,3,4,5].map(n => (
                      <button
                        type="button"
                        key={n}
                        onClick={() => setForm({ ...form, nota: n })}
                        className={n <= form.nota ? 'text-yellow-400' : 'text-gray-300'}
                      >
                        <StarIcon className="w-7 h-7" />
                      </button>
                    ))}
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Comentário</label>
                  <textarea
                    value={form.comentario}
                    onChange={e => setForm({ ...form, comentario: e.target.value })}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Escreva sua avaliação..."
                  />
                </div>
                <button
                  type="submit"
                  disabled={enviando}
                  className="bg-primary text-white px-6 py-2 rounded-md font-semibold hover:bg-primary-hover transition-colors disabled:opacity-50"
                >
                  {enviando ? 'Enviando...' : 'Enviar Avaliação'}
                </button>
                {msg && <div className="mt-2 text-center text-sm text-primary">{msg}</div>}
              </form>
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
} 