'use client';

import { useState, useEffect } from 'react';
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import AcompanhanteCard from '@/components/AcompanhanteCard';
import LoadingSpinner from '@/components/LoadingSpinner';
import { Database } from '@/lib/database.types';
import Link from 'next/link';
import { ShieldCheckIcon, ChatBubbleLeftRightIcon, SparklesIcon } from '@heroicons/react/24/outline';

type Acompanhante = Database['public']['Tables']['acompanhantes']['Row'] & {
  fotos: Pick<Database['public']['Tables']['fotos']['Row'], 'url' | 'storage_path' | 'tipo' | 'principal'>[];
  cidades: Pick<Database['public']['Tables']['cidades']['Row'], 'nome' | 'estado'> | null;
};
type Cidade = Database['public']['Tables']['cidades']['Row'];

const features = [
  {
    name: 'Suporte 100% Humanizado',
    description: 'Uma equipe real e dedicada, pronta para ouvir, entender suas necessidades e oferecer o melhor apoio.',
    icon: ChatBubbleLeftRightIcon,
  },
  {
    name: 'Tecnologia Moderna e Discreta',
    description: 'Nossa plataforma é construída com as ferramentas mais seguras e fáceis de usar, garantindo sua privacidade e autonomia.',
    icon: ShieldCheckIcon,
  },
  {
    name: 'Visibilidade e Oportunidades',
    description: 'Oferecemos visibilidade real e oportunidades justas para quem quer se destacar e crescer com liberdade.',
    icon: SparklesIcon,
  },
]

export default function Home() {
  const [acompanhantes, setAcompanhantes] = useState<Acompanhante[]>([]);
  const [loading, setLoading] = useState(true);
  const [cidades, setCidades] = useState<Cidade[]>([]);
  const [generos] = useState(['Feminino', 'Masculino', 'Trans']);
  const [cidadeId, setCidadeId] = useState('');
  const [genero, setGenero] = useState('');
  const [error, setError] = useState<string | null>(null);

  const supabase = createClientComponentClient<Database>();

  const getCidadeNome = (acompanhante: Acompanhante): string => {
    return acompanhante.cidades?.nome || 'Localidade não encontrada';
  };

  useEffect(() => {
    const carregarCidades = async () => {
      try {
        const { data, error } = await supabase
          .from('cidades')
          .select('*')
          .order('nome', { ascending: true });

        if (error) throw error;
        setCidades(data || []);
      } catch (error) {
        console.error('Erro ao carregar cidades:', error);
        setError('Não foi possível carregar a lista de cidades.');
      }
    };
    carregarCidades();
  }, [supabase]);

  useEffect(() => {
    const carregarAcompanhantes = async () => {
      setLoading(true);
      setError(null);
      try {
        let query = supabase
          .from('acompanhantes')
          .select('*, fotos ( url, storage_path, tipo, principal ), cidades ( nome, estado )')
          .eq('status', 'aprovado')
          .order('destaque', { ascending: false })
          .limit(100, { foreignTable: 'fotos' })
          .limit(12);

        if (cidadeId) {
          query = query.eq('cidade_id', cidadeId);
        }
        if (genero) {
          query = query.ilike('genero', `%${genero}%`);
        }

        const { data, error } = await query;

        if (error) throw error;
        setAcompanhantes(data.map(a => ({...a, fotos: a.fotos || []})) as Acompanhante[]);
      } catch (err) {
        console.error('Erro ao carregar acompanhantes:', err);
        setError('Ocorreu um erro ao buscar os perfis. Tente novamente mais tarde.');
      } finally {
        setLoading(false);
      }
    };

    carregarAcompanhantes();
  }, [cidadeId, genero, supabase]);

  return (
    <>
      <Header />
      <main className="flex-grow">
        <div className="relative bg-cover bg-center py-20" style={{ backgroundImage: "url('/assets/img/imagem_banner.png')" }}>
          <div className="absolute inset-0 bg-black opacity-30"></div>
          <div className="container mx-auto px-4 relative z-10 text-center text-white">
            <h1 className="text-4xl md:text-5xl font-bold mb-4">Sua nova referência em acompanhantes no Brasil!</h1>
            <p className="text-lg md:text-xl mb-8">Sigiloso, seguro e exclusivo.</p>
          </div>
        </div>

        <div className="container mx-auto px-4 -mt-10 relative z-20">
          <div className="bg-white p-6 rounded-lg shadow-md">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <select
                value={cidadeId}
                onChange={(e) => setCidadeId(e.target.value)}
                className="w-full p-3 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
              >
                <option value="">Todas as cidades</option>
                {cidades.map(c => (
                  <option key={c.id} value={c.id}>{c.nome}</option>
                ))}
              </select>
              <select
                value={genero}
                onChange={(e) => setGenero(e.target.value)}
                className="w-full p-3 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
              >
                <option value="">Todos os gêneros</option>
                {generos.map(g => (
                  <option key={g} value={g}>{g}</option>
                ))}
              </select>
              <button onClick={() => {}} className="w-full bg-secondary text-white p-3 rounded-lg hover:bg-secondary-hover transition-colors flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clipRule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <section id="acompanhantes" className="py-12 bg-gray-50">
            <div className="container mx-auto px-4">
                <h2 className="text-3xl font-bold text-center text-gray-800 mb-8">Acompanhantes em Destaque</h2>
                <div className="relative min-h-[400px]">
                    {loading && acompanhantes.length === 0 ? (
                        <div className="flex justify-center items-center h-full">
                           <LoadingSpinner />
                        </div>
                    ) : (
                      <div className="relative">
                        {loading && (
                          <div className="absolute inset-0 flex items-center justify-center bg-gray-50 bg-opacity-75 z-10 rounded-lg">
                            <LoadingSpinner />
                          </div>
                        )}
                        {error ? (
                          <p className="text-center text-red-500">{error}</p>
                        ) : acompanhantes.length > 0 ? (
                          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                              {acompanhantes.map((acompanhante) => (
                                  <AcompanhanteCard 
                                    key={acompanhante.id} 
                                    acompanhante={acompanhante} 
                                    cidadeNome={getCidadeNome(acompanhante)} />
                              ))}
                          </div>
                        ) : (
                          <p className="text-center text-gray-500 pt-10">Nenhum perfil encontrado para os filtros selecionados.</p>
                        )}
                      </div>
                    )}
                </div>
            </div>
        </section>

        {/* About Section */}
        <div className="relative bg-white py-24 sm:py-32">
          <div className="mx-auto max-w-7xl lg:grid lg:grid-cols-12 lg:gap-x-8 lg:px-8">
            <div className="px-6 lg:col-span-7 lg:px-0 xl:col-span-6 flex flex-col justify-center">
              <div className="mx-auto max-w-2xl lg:mx-0">
                <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                  Feita por quem entende, para quem merece respeito.
                </h1>
                <p className="mt-6 text-lg leading-8 text-gray-600">
                  A Sigilosas VIP nasceu a partir de uma vivência real no mercado. Estamos há mais de 5 anos no ramo, conhecendo de perto os desafios, necessidades e sonhos de quem trabalha como acompanhante.
                </p>
                <p className="mt-4 text-lg leading-8 text-gray-600">
                  Com toda essa experiência, decidimos criar algo diferente: uma plataforma exclusiva, segura e acolhedora, feita para quem busca mais do que apenas uma vitrine — feita para quem quer ser valorizado, respeitado e crescer com liberdade.
                </p>
              </div>
            </div>
            <div className="relative lg:col-span-5 mt-10 lg:mt-0">
              <img
                className="aspect-[4/3] w-full bg-gray-50 object-contain rounded-xl shadow-xl"
                src="/assets/img/imagem_banner.png"
                alt="Mulher sorrindo"
              />
            </div>
          </div>
        </div>

        {/* Features Section */}
        <div className="bg-gray-50 py-24 sm:py-32">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl lg:text-center">
                <h2 className="text-base font-semibold leading-7 text-primary">Nossos Pilares</h2>
                <p className="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Tudo o que você precisa para crescer com segurança
                </p>
                </div>
                <div className="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                <dl className="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-3 lg:gap-y-16">
                    {features.map((feature) => (
                    <div key={feature.name} className="relative pl-16">
                        <dt className="text-base font-semibold leading-7 text-gray-900">
                        <div className="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                            <feature.icon className="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        {feature.name}
                        </dt>
                        <dd className="mt-2 text-base leading-7 text-gray-600">{feature.description}</dd>
                    </div>
                    ))}
                </dl>
                </div>
            </div>
        </div>

        {/* Mission Section */}
        <div className="bg-white mx-auto my-20 max-w-7xl text-center px-6 py-24 sm:py-32 rounded-lg shadow-xl">
            <h2 className="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Nossa Missão é Clara</h2>
            <p className="mt-6 text-lg leading-8 text-gray-600 max-w-4xl mx-auto">
            Conectar, empoderar e impulsionar cada profissional com seriedade e respeito. Na Sigilosas VIP, você encontra segurança, privacidade e uma equipe pronta para te apoiar de verdade. Seja você iniciante ou experiente, a Sigilosas VIP é o seu lugar.
            </p>
            <div className="mt-10 flex items-center justify-center gap-x-6">
              <Link href="/cadastro" className="rounded-md bg-primary px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                Faça parte da nossa comunidade
              </Link>
              <Link href="/acompanhantes" className="text-sm font-semibold leading-6 text-gray-900">
                Ver perfis <span aria-hidden="true">→</span>
              </Link>
            </div>
        </div>
      </main>
      <Footer />
    </>
  );
} 