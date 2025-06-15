'use client';

import { useState, useEffect } from 'react';
import { supabase } from '@/lib/supabase';
import Link from 'next/link';

interface Configuracao {
  chave: string;
  valor: string;
}

const imagens = [
  { chave: 'logo', label: 'Logo do site', tamanho: '220x60px' },
  { chave: 'favicon', label: 'Favicon', tamanho: '64x64px' },
  { chave: 'banner', label: 'Banner principal', tamanho: '1200x320px' },
  { chave: 'banner_cadastro', label: 'Banner de cadastro', tamanho: '800x240px' },
  { chave: 'icone_painel', label: 'Ícone do painel', tamanho: '64x64px' },
  { chave: 'icone_heart', label: 'Ícone de favorito (coração)', tamanho: '48x48px' },
  { chave: 'icone_search', label: 'Ícone de busca', tamanho: '48x48px' },
  { chave: 'icone_map_marker', label: 'Ícone de localização (mapa)', tamanho: '48x48px' },
  { chave: 'icone_user', label: 'Ícone de usuário', tamanho: '48x48px' },
  { chave: 'icone_star', label: 'Ícone de avaliação (estrela)', tamanho: '48x48px' },
  { chave: 'icone_whatsapp', label: 'Ícone do WhatsApp', tamanho: '48x48px' },
  { chave: 'icone_refresh', label: 'Ícone de limpar/refresh', tamanho: '48x48px' },
];

export default function ConfiguracoesPage() {
  const [configuracoes, setConfiguracoes] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetchConfiguracoes();
  }, []);

  const fetchConfiguracoes = async () => {
    try {
      const { data, error } = await supabase
        .from('configuracoes')
        .select('chave, valor');

      if (error) throw error;

      const configMap: Record<string, string> = {};
      data?.forEach(config => {
        configMap[config.chave] = config.valor;
      });

      setConfiguracoes(configMap);
    } catch (error) {
      console.error('Erro ao buscar configurações:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    try {
      // Salvar todas as configurações
      const configEntries = Object.entries(configuracoes);
      const { error } = await supabase
        .from('configuracoes')
        .upsert(
          configEntries.map(([chave, valor]) => ({
            chave,
            valor: valor.trim()
          })),
          { onConflict: 'chave' }
        );

      if (error) throw error;

      setMessage('Configurações salvas com sucesso!');
    } catch (error) {
      console.error('Erro ao salvar configurações:', error);
      setMessage('Erro ao salvar configurações. Tente novamente.');
    } finally {
      setSaving(false);
    }
  };

  const handleInputChange = (chave: string, valor: string) => {
    setConfiguracoes(prev => ({
      ...prev,
      [chave]: valor
    }));
  };

  const handleImageUpload = async (chave: string, file: File) => {
    try {
      const fileExt = file.name.split('.').pop();
      const fileName = `${chave}_${Date.now()}.${fileExt}`;
      const filePath = `configuracoes/${fileName}`;

      const { error: uploadError } = await supabase.storage
        .from('uploads')
        .upload(filePath, file);

      if (uploadError) throw uploadError;

      const { data: { publicUrl } } = supabase.storage
        .from('uploads')
        .getPublicUrl(filePath);

      handleInputChange(chave, publicUrl);
    } catch (error) {
      console.error('Erro ao fazer upload da imagem:', error);
      alert('Erro ao fazer upload da imagem. Tente novamente.');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-[#2E1530] text-lg">Carregando configurações...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-[#2E1530] mb-2">Configurações do Site</h1>
        <p className="text-gray-600">Gerencie as configurações e imagens do site</p>
      </div>

      {/* Ações rápidas */}
      <div className="flex flex-wrap gap-4 justify-center">
        <Link
          href="/painel"
          className="inline-flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-gray-700 transition-colors"
        >
          🏠 Voltar ao Dashboard
        </Link>
        <Link
          href="/painel/acompanhantes"
          className="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition-colors"
        >
          👥 Acompanhantes
        </Link>
        <Link
          href="/painel/cidades"
          className="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-700 transition-colors"
        >
          🏙️ Cidades
        </Link>
      </div>

      <form onSubmit={handleSubmit} className="space-y-8">
        {/* Vídeo explicativo */}
        <div className="bg-white rounded-xl shadow-lg p-6">
          <h2 className="text-xl font-bold text-[#2E1530] mb-4">Vídeo Explicativo (Home)</h2>
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                URL do vídeo (YouTube, Vimeo, etc)
              </label>
              <input
                type="text"
                value={configuracoes.video_cadastro || ''}
                onChange={(e) => handleInputChange('video_cadastro', e.target.value)}
                placeholder="Cole aqui a URL do vídeo"
                className="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            {configuracoes.video_cadastro ? (
              <div className="relative pb-[56.25%] h-0 overflow-hidden rounded-lg shadow-md">
                <iframe
                  src={configuracoes.video_cadastro}
                  className="absolute top-0 left-0 w-full h-full rounded-lg"
                  frameBorder="0"
                  allowFullScreen
                />
              </div>
            ) : (
              <div className="bg-gray-100 rounded-lg h-32 flex items-center justify-center shadow-md">
                <span className="text-gray-500">Preview do vídeo será exibido aqui</span>
              </div>
            )}
          </div>
        </div>

        {/* Configurações de imagens */}
        <div className="bg-white rounded-xl shadow-lg p-6">
          <h2 className="text-xl font-bold text-[#2E1530] mb-4">Configurações de Imagens do Site</h2>
          <p className="text-gray-600 mb-6">
            Preencha o endereço (URL) ou caminho do arquivo de cada imagem. As imagens aparecerão automaticamente no site. 
            Se não preencher, será exibido um ícone padrão.
          </p>

          {message && (
            <div className={`p-4 rounded-md text-center font-semibold mb-6 ${
              message.includes('sucesso') 
                ? 'bg-green-100 text-green-700' 
                : 'bg-red-100 text-red-700'
            }`}>
              {message}
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {imagens.map((imagem) => (
              <div key={imagem.chave} className="bg-gray-50 rounded-lg p-4 space-y-3">
                <label className="block text-sm font-semibold text-[#2E1530]">
                  {imagem.label}
                </label>
                
                <input
                  type="text"
                  value={configuracoes[imagem.chave] || ''}
                  onChange={(e) => handleInputChange(imagem.chave, e.target.value)}
                  placeholder="URL ou caminho da imagem"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />

                <div className="flex flex-col items-center space-y-2">
                  <label className="bg-[#C5A572] text-white px-4 py-2 rounded-md cursor-pointer font-medium hover:bg-[#b38f5f] transition-colors text-sm">
                    📤 Enviar imagem
                    <input
                      type="file"
                      accept="image/*"
                      className="hidden"
                      onChange={(e) => {
                        const file = e.target.files?.[0];
                        if (file) handleImageUpload(imagem.chave, file);
                      }}
                    />
                  </label>
                </div>

                <img
                  src={configuracoes[imagem.chave] || `https://via.placeholder.com/120x48?text=${imagem.label}`}
                  alt="Prévia"
                  className="w-full h-12 object-contain bg-gray-200 rounded-md border border-gray-300"
                />

                <small className="text-gray-500 text-xs block text-center">
                  Tamanho ideal: {imagem.tamanho}
                </small>
              </div>
            ))}
          </div>
        </div>

        {/* Botão salvar */}
        <div className="text-center">
          <button
            type="submit"
            disabled={saving}
            className="bg-blue-600 text-white px-8 py-3 rounded-md font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 max-w-xs w-full"
          >
            {saving ? '💾 Salvando...' : '💾 Salvar Configurações'}
          </button>
        </div>
      </form>
    </div>
  );
} 