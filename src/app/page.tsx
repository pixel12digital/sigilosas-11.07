'use client';

import { useState, useEffect, useRef } from 'react';
import { supabase } from '@/lib/supabase';
import type { Acompanhante, Cidade, Configuracao } from '@/lib/supabase';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import AcompanhanteCard from '@/components/AcompanhanteCard';
import LoadingSpinner from '@/components/LoadingSpinner';

export default function Home() {
  const [config, setConfig] = useState<Record<string, string>>({});
  const [acompanhantes, setAcompanhantes] = useState<Acompanhante[]>([]);
  const [cidades, setCidades] = useState<Cidade[]>([]);
  const [loading, setLoading] = useState(true);
  const [filtros, setFiltros] = useState({
    cidade: '',
    genero: '',
  });
  const [showPopup, setShowPopup] = useState(true);
  const [cidadeInput, setCidadeInput] = useState('');
  const [cidadeIdSelecionada, setCidadeIdSelecionada] = useState<number | string | null>(null);
  const [sugestoesCidades, setSugestoesCidades] = useState<Cidade[]>([]);
  const sugestoesRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    carregarDados();
  }, []);

  useEffect(() => {
    if (showPopup && typeof window !== 'undefined') {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(() => {}, () => {});
      }
    }
  }, [showPopup]);

  const carregarDados = async () => {
    try {
      // Carregar configurações
      const { data: configData } = await supabase
        .from('configuracoes')
        .select('chave, valor');

      const configObj: Record<string, string> = {};
      configData?.forEach(item => {
        configObj[item.chave] = item.valor || '';
      });
      setConfig(configObj);

      // Carregar cidades
      const { data: cidadesData } = await supabase
        .from('cidades')
        .select('*')
        .order('nome');
      setCidades(cidadesData || []);

      // Carregar acompanhantes em destaque
      await carregarAcompanhantes();
    } catch (error) {
      console.error('Erro ao carregar dados:', error);
    } finally {
      setLoading(false);
    }
  };

  const carregarAcompanhantes = async (filtrosAplicados = filtros) => {
    try {
      setLoading(true);
      
      let query = supabase
        .from('acompanhantes')
        .select(`
          id, nome, status, idade, etnia, bairro, destaque, verificado, silicone, tatuagens, piercings, cidade_id, valor_padrao,
          fotos ( url, storage_path, tipo, principal ),
          videos_verificacao ( url, storage_path ),
          documentos_acompanhante ( url, storage_path, tipo )
        `)
        .eq('status', 'aprovado')
        .order('destaque', { ascending: false })
        .order('created_at', { ascending: false });

      if (filtrosAplicados.cidade) {
        query = query.eq('cidade_id', filtrosAplicados.cidade);
      }

      if (filtrosAplicados.genero) {
        query = query.eq('genero', filtrosAplicados.genero);
      }

      const { data, error } = await query.limit(12);

      if (error) throw error;
      const acompanhantesVisiveis = data?.filter(a => a.status === 'aprovado') || [];
      setAcompanhantes(acompanhantesVisiveis as any);
    } catch (error) {
      console.error('Erro ao carregar acompanhantes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleFiltroChange = (campo: string, valor: string) => {
    const novosFiltros = { ...filtros, [campo]: valor };
    setFiltros(novosFiltros);
    carregarAcompanhantes(novosFiltros);
  };

  const handleBuscaCidade = (event: React.FormEvent) => {
    event.preventDefault();
    const inputCidade = document.getElementById('inputBuscaCidade') as HTMLSelectElement;
    const inputGenero = document.getElementById('inputBuscaGenero') as HTMLSelectElement;
    
    const novosFiltros = {
      cidade: inputCidade.value,
      genero: inputGenero.value
    };

    setFiltros(novosFiltros);
    carregarAcompanhantes(novosFiltros).then(() => {
      setTimeout(() => {
        const secaoResultados = document.getElementById('secao-acompanhantes');
        if (secaoResultados) {
          secaoResultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 200);
    });
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  return (
    <div className="min-h-screen bg-[#F8F6F9]">
      {showPopup && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-2xl shadow-2xl max-w-[370px] w-full p-6 border border-[#CFB78B] text-center">
            <div className="flex items-center justify-center gap-2 mb-2">
              <span className="text-4xl font-extrabold text-[#4E3950]">18+</span>
              <div className="text-left">
                <div className="text-xs font-bold text-[#4E3950] leading-tight">CONTEÚDO</div>
                <div className="text-base font-bold text-[#4E3950] leading-tight -mt-1">ADULTO</div>
              </div>
            </div>
            <hr className="my-2 border-[#CFB78B]" />
            <p className="text-[#4E3950] text-base mb-2">
              Entendo que o site <span className="font-bold">Sigilosas VIP</span> apresenta <span className="font-bold">conteúdo explícito</span> destinado a adultos.<br />
              <a href="/termos" className="underline text-[#CFB78B]">Termos de uso</a>
            </p>
            <hr className="my-2 border-[#CFB78B]" />
            <div className="mb-2">
              <div className="text-xs font-bold text-[#4E3950] mb-1">AVISO DE COOKIES E LOCALIZAÇÃO</div>
              <p className="text-[#4E3950] text-sm">
                Usamos cookies, tecnologias semelhantes e localização para melhorar sua experiência em nosso site.
              </p>
            </div>
            <hr className="my-2 border-[#CFB78B]" />
            <div className="text-xs text-[#4E3950] mb-4">
              A profissão de acompanhante é legalizada no Brasil e deve ser respeitada. <a href="/saiba-mais" className="underline text-[#CFB78B]">Saiba mais</a>
            </div>
            <button
              className="w-full py-3 bg-[#4E3950] text-white rounded-lg font-semibold text-lg tracking-wide transition-colors hover:bg-[#CFB78B] hover:text-[#4E3950]"
              onClick={() => setShowPopup(false)}
            >
              Concordo
            </button>
          </div>
        </div>
      )}
      <Header config={config} />
      
      {/* Banner principal */}
      <section
        className="w-full flex items-center justify-between gap-6 md:gap-10 px-4 md:px-8 lg:px-20 mx-auto flex-wrap"
        style={{
          background: "linear-gradient(90deg, #F8F6F9 0%, #CFB78B 50%, #4E3950 100%)",
          borderBottom: '1px solid #CFB78B',
          minHeight: 'unset',
          paddingTop: 16,
          paddingBottom: 4,
        }}
      >
        <div className="flex-1 min-w-[320px] max-w-[900px] flex flex-col justify-center h-full px-6 pt-8 md:pl-[60px] md:pt-0 md:px-0">
          <h1 className="w-full max-w-[900px] text-3xl md:text-4xl lg:text-5xl font-bold mb-6 leading-tight md:leading-[1.1] break-words text-[#4E3950] relative animate-fade-in">
            <span className="reluzente-title">CONECTANDO DESEJOS COM ELEGÂNCIA<br />E PRIVACIDADE!</span>
          </h1>
          <p className="text-xl md:text-2xl text-[#4E3950] mb-8 max-w-[600px]">
            Um espaço seguro, respeitoso e exclusivo
          </p>
          <div className="flex gap-6 flex-wrap mb-2">
            <a href="/cadastro" className="btn-secondary text-base md:text-lg px-6 py-3">
              Anunciar como acompanhante
            </a>
          </div>
        </div>
        <div className="flex-1 min-w-[320px] max-w-[700px] flex items-end justify-center mt-[-32px] md:mt-0">
          <img 
            src="/assets/img/imagem_banner.png" 
            alt="Banner principal" 
            className="w-auto h-[336px] md:h-[416px] lg:h-[480px] object-contain drop-shadow-xl"
            style={{ background: 'none', maxHeight: '80%', paddingTop: 16, paddingBottom: 4 }}
          />
        </div>
      </section>

      {/* Bloco de destaque */}
      <section className="max-w-4xl mx-auto mb-8 p-10 md:p-12 bg-white rounded-2xl shadow-lg text-center border border-[#CFB78B] mt-12">
        <h2 className="text-3xl text-[#4E3950] font-bold mb-6">
          Sua nova referência em acompanhantes no Brasil!
        </h2>
        <form onSubmit={handleBuscaCidade} className="flex flex-col md:flex-row gap-4 justify-center items-center my-8" autoComplete="off">
          <div className="relative w-full md:flex-1 md:max-w-[340px]">
             <select 
              id="inputBuscaCidade"
              className="w-full px-4 py-3 rounded-lg border border-[#CFB78B] text-lg bg-[#F8F6F9] text-[#4E3950]"
              value={filtros.cidade}
              onChange={(e) => handleFiltroChange('cidade', e.target.value)}
            >
              <option value="">Todas as cidades</option>
              {cidades.map(cidade => (
                <option key={cidade.id} value={cidade.id}>
                  {cidade.nome}
                </option>
              ))}
            </select>
          </div>
          <div className="relative w-full md:flex-1 md:max-w-[240px]">
             <select 
              id="inputBuscaGenero"
              className="w-full px-4 py-3 rounded-lg border border-[#CFB78B] text-lg bg-[#F8F6F9] text-[#4E3950]"
              value={filtros.genero}
              onChange={(e) => handleFiltroChange('genero', e.target.value)}
            >
              <option value="">Todos os gêneros</option>
              <option value="F">Feminino</option>
              <option value="M">Masculino</option>
              <option value="O">Outro</option>
            </select>
          </div>
          <button 
            type="submit" 
            className="bg-[#CFB78B] text-[#4E3950] px-7 py-3 rounded-lg font-semibold text-lg hover:bg-[#4E3950] hover:text-[#CFB78B] transition-colors"
          >
            <svg width="22" height="22" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="7"/>
              <line x1="16" y1="16" x2="21" y2="21"/>
            </svg>
          </button>
        </form>
        <p className="text-[#4E3950] text-lg mt-4">Sigiloso, seguro e exclusivo.</p>
      </section>

      {/* Filtro compacto */}
      {/*
      <section className="max-w-2xl mx-auto mb-8 p-6 bg-white rounded-2xl shadow-lg border border-[#CFB78B]">
        <form className="flex gap-4 flex-wrap items-end justify-center">
          <div className="flex-1 min-w-[160px]">
            <label htmlFor="cidadeFiltro" className="font-medium text-[#4E3950] mb-2 block">
              Cidade
            </label>
            <select 
              id="cidadeFiltro"
              value={filtros.cidade}
              onChange={(e) => handleFiltroChange('cidade', e.target.value)}
              className="w-full border border-[#CFB78B] rounded-lg px-3 py-2 text-base bg-[#F8F6F9] text-[#4E3950]"
            >
              <option value="">Todas as cidades</option>
              {cidades.map(cidade => (
                <option key={cidade.id} value={cidade.id}>
                  {cidade.nome}
                </option>
              ))}
            </select>
          </div>
          <div className="flex-1 min-w-[140px]">
            <label htmlFor="generoFiltro" className="font-medium text-[#4E3950] mb-2 block">
              Gênero
            </label>
            <select 
              id="generoFiltro"
              value={filtros.genero}
              onChange={(e) => handleFiltroChange('genero', e.target.value)}
              className="w-full border border-[#CFB78B] rounded-lg px-3 py-2 text-base bg-[#F8F6F9] text-[#4E3950]"
            >
              <option value="">Todos</option>
              <option value="F">Feminino</option>
              <option value="M">Masculino</option>
              <option value="O">Outro</option>
            </select>
          </div>
          <button
            type="button"
            className="bg-[#CFB78B] text-[#4E3950] px-8 py-3 rounded-lg font-semibold text-lg hover:bg-[#4E3950] hover:text-[#CFB78B] transition-colors mt-4"
            onClick={() => carregarAcompanhantes()}
          >
            Buscar
          </button>
        </form>
      </section>
      */}

      {/* Cards de acompanhantes */}
      <section id="secao-acompanhantes" className="container mx-auto py-12 px-4">
        <h2 className="text-3xl font-bold text-center text-[#4E3950] mb-8">
          Acompanhantes em Destaque
        </h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          {acompanhantes.map(acompanhante => {
            const cidadeNome = cidades.find(c => c.id === acompanhante.cidade_id)?.nome;
            return (
              <AcompanhanteCard
                key={acompanhante.id}
                acompanhante={acompanhante}
                cidadeNome={cidadeNome}
              />
            );
          })}
        </div>
      </section>

      {/* Bloco de verificação, documentos e fotos reais */}
      {/*
      <section className="flex gap-8 justify-center mb-10 flex-wrap">
        <div className="flex-1 min-w-[180px] max-w-[260px] text-center p-6 border border-[#CFB78B] rounded-2xl bg-white">
          <img src="/assets/img/icons/icon-search.svg" alt="Verificação facial" className="h-11 mb-3 mx-auto" />
          <div className="font-medium text-[#4E3950]">Verificação facial</div>
        </div>
        <div className="flex-1 min-w-[180px] max-w-[260px] text-center p-6 border border-[#CFB78B] rounded-2xl bg-white">
          <img src="/assets/img/icons/icon-painel.svg" alt="Documentos validados" className="h-11 mb-3 mx-auto" />
          <div className="font-medium text-[#4E3950]">Documentos validados</div>
        </div>
        <div className="flex-1 min-w-[180px] max-w-[260px] text-center p-6 border border-[#CFB78B] rounded-2xl bg-white">
          <img src="/assets/img/icons/icon-menu.svg" alt="Fotos reais" className="h-11 mb-3 mx-auto" />
          <div className="font-medium text-[#4E3950]">Fotos reais</div>
        </div>
      </section>
      */}

      <Footer />
    </div>
  );
} 