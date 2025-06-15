'use client';

import { useState, useEffect } from 'react';
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

  useEffect(() => {
    carregarDados();
  }, []);

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
          *,
          cidades(nome),
          fotos(url, capa)
        `)
        .eq('status', 'aprovado')
        .order('destaque', { ascending: false })
        .order('data_cadastro', { ascending: false });

      if (filtrosAplicados.cidade) {
        query = query.eq('cidade_id', filtrosAplicados.cidade);
      }

      if (filtrosAplicados.genero) {
        query = query.eq('genero', filtrosAplicados.genero);
      }

      const { data, error } = await query.limit(12);

      if (error) throw error;
      setAcompanhantes(
        (data || []).map((item: any) => ({
          ...item,
          cidades: Array.isArray(item.cidades) ? item.cidades[0] : item.cidades
        }))
      );
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
    const input = document.getElementById('inputBuscaCidade') as HTMLInputElement;
    if (input.value.trim()) {
      // Implementar busca por cidade
      console.log('Buscar por:', input.value);
    }
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  return (
    <div className="min-h-screen bg-[#F8F6F9]">
      <Header config={config} />
      
      {/* Banner principal */}
      <section className="flex items-center justify-between gap-8 py-12 px-4 max-w-7xl mx-auto flex-wrap bg-white border-b border-[#CFB78B]">
        <div className="flex-1 min-w-[320px] max-w-[600px]">
          <h1 className="text-4xl text-[#4E3950] font-bold mb-4">
            A acompanhante trabalha e você contrata!
          </h1>
          <p className="text-xl text-[#4E3950] mb-7">
            O acordo começa com respeito
          </p>
          <div className="flex gap-4 flex-wrap mb-4">
            <a href="/cadastro" className="btn-primary">
              Criar perfil de cliente
            </a>
            <a href="/anunciar" className="btn-secondary">
              Anunciar como acompanhante
            </a>
          </div>
        </div>
        <div className="flex-1 min-w-[320px] max-w-[520px] text-right">
          <img 
            src={config.banner || '/assets/img/banner.jpg'} 
            alt="Banner principal" 
            className="w-full max-w-[520px] rounded-2xl shadow-lg object-cover border border-[#CFB78B]"
          />
        </div>
      </section>

      {/* Bloco de destaque */}
      <section className="max-w-4xl mx-auto mb-8 p-8 bg-white rounded-2xl shadow-lg text-center border border-[#CFB78B]">
        <h2 className="text-3xl text-[#4E3950] font-bold mb-3">
          Sua nova referência em acompanhantes no Brasil!
        </h2>
        <form onSubmit={handleBuscaCidade} className="flex gap-3 justify-center items-center my-6">
          <input 
            type="text" 
            id="inputBuscaCidade"
            placeholder="Buscar acompanhantes por cidade" 
            className="flex-1 max-w-[320px] px-4 py-3 rounded-lg border border-[#CFB78B] text-lg bg-[#F8F6F9] text-[#4E3950]"
          />
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
        <p className="text-[#4E3950] text-lg">Sigiloso, seguro e exclusivo.</p>
      </section>

      {/* Filtro compacto */}
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
              <option value="Outro">Outro</option>
            </select>
          </div>
          <div className="min-w-[120px]">
            <button 
              type="button" 
              onClick={() => carregarAcompanhantes()}
              className="bg-[#CFB78B] text-[#4E3950] px-7 py-2 rounded-lg font-semibold text-base w-full hover:bg-[#4E3950] hover:text-[#CFB78B] transition-colors"
            >
              Buscar
            </button>
          </div>
        </form>
      </section>

      {/* Cards de acompanhantes */}
      <section className="max-w-7xl mx-auto mb-10">
        <h3 className="text-2xl text-[#4E3950] font-bold mb-4 text-center">
          Acompanhantes em destaque
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
          {loading ? (
            <LoadingSpinner />
          ) : acompanhantes.length > 0 ? (
            acompanhantes.map(acompanhante => (
              <AcompanhanteCard 
                key={acompanhante.id} 
                acompanhante={acompanhante} 
              />
            ))
          ) : (
            <div className="col-span-full text-center py-8">
              <p className="text-lg text-[#4E3950]">Nenhum acompanhante encontrado</p>
              <span className="text-sm text-[#CFB78B]">Tente ajustar os filtros</span>
            </div>
          )}
        </div>
      </section>

      {/* Diferenciais */}
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

      <Footer />
    </div>
  );
} 