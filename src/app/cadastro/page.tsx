"use client";
import { useState, useEffect, useRef } from "react";
import Image from "next/image";
import { createClientComponentClient } from "@supabase/auth-helpers-nextjs";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { validarTelefone, formatarTelefone } from '@/lib/validation';

interface Estado {
  id: number;
  nome: string;
  uf: string;
}

interface Cidade {
  id: string; // UUID
  nome: string;
  estado_id: number;
}

const ESTADOS = [
  { uf: 'AC', nome: 'Acre' }, { uf: 'AL', nome: 'Alagoas' }, { uf: 'AP', nome: 'Amapá' },
  { uf: 'AM', nome: 'Amazonas' }, { uf: 'BA', nome: 'Bahia' }, { uf: 'CE', nome: 'Ceará' },
  { uf: 'DF', nome: 'Distrito Federal' }, { uf: 'ES', nome: 'Espírito Santo' }, { uf: 'GO', nome: 'Goiás' },
  { uf: 'MA', nome: 'Maranhão' }, { uf: 'MT', nome: 'Mato Grosso' }, { uf: 'MS', nome: 'Mato Grosso do Sul' },
  { uf: 'MG', nome: 'Minas Gerais' }, { uf: 'PA', nome: 'Pará' }, { uf: 'PB', nome: 'Paraíba' },
  { uf: 'PR', nome: 'Paraná' }, { uf: 'PE', nome: 'Pernambuco' }, { uf: 'PI', nome: 'Piauí' },
  { uf: 'RJ', nome: 'Rio de Janeiro' }, { uf: 'RN', nome: 'Rio Grande do Norte' }, { uf: 'RS', nome: 'Rio Grande do Sul' },
  { uf: 'RO', nome: 'Rondônia' }, { uf: 'RR', nome: 'Roraima' }, { uf: 'SC', nome: 'Santa Catarina' },
  { uf: 'SP', nome: 'São Paulo' }, { uf: 'SE', nome: 'Sergipe' }, { uf: 'TO', nome: 'Tocantins' }
];

// Estilo customizado para inputs
const inputClass = "w-full bg-white border border-accent rounded-lg px-4 py-2 text-secondary focus:outline-none focus:ring-2 focus:ring-accent text-base transition mb-0 placeholder-accent";
const labelClass = "block font-semibold text-secondary mb-1";
const checkboxClass = "accent-primary w-5 h-5 mr-2 align-middle";
const buttonClass = "w-full py-3 bg-primary text-white border-none rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-primary-hover disabled:opacity-50 disabled:cursor-not-allowed mt-2";
const uploadButtonClass = "flex items-center justify-center gap-2 w-full py-3 bg-white border-2 border-dashed border-accent rounded-lg font-medium text-secondary cursor-pointer transition-all hover:bg-gray-50 hover:border-accent-hover active:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed";

// Função para gerar uma senha aleatória segura
const generateRandomPassword = (length = 20) => {
  const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=";
  let password = "";
  for (let i = 0, n = charset.length; i < length; ++i) {
    password += charset.charAt(Math.floor(Math.random() * n));
  }
  return password;
};

export default function CadastroAcompanhante() {
  const [estados, setEstados] = useState<Estado[]>([]);
  const [cidades, setCidades] = useState<Cidade[]>([]);
  const [cidadesFiltradas, setCidadesFiltradas] = useState<Cidade[]>([]);
  const [estadoSelecionado, setEstadoSelecionado] = useState("");
  const [form, setForm] = useState({
    nome: "",
    idade: "",
    genero: "",
    genitalia: "",
    genitalia_outro: "",
    preferencia_sexual: "",
    preferencia_sexual_outro: "",
    peso: "",
    altura: "",
    etnia: "",
    cor_olhos: "",
    estilo_cabelo: "",
    tamanho_cabelo: "",
    tamanho_pe: "",
    silicone: false,
    tatuagens: false,
    piercings: false,
    fumante: "",
    idiomas: "",
    endereco: "",
    bairro: "",
    cep: "",
    cidade_id: "",
    estado_id: "",
    horario_expediente: "",
    formas_pagamento: "",
    data_criacao: "",
    descricao: "",
    foto: "",
    documentos: [],
    video_verificacao: "",
    galeria_fotos: [],
    email: "",
    telefone: "",
    senha: "",
    tipo_atendimento: "presencial",
    valor_padrao: "",
    valor_observacao: "",
  });
  const [fotoFile, setFotoFile] = useState<File | null>(null);
  const [fotoPreview, setFotoPreview] = useState<string>("");
  const [fotoMsg, setFotoMsg] = useState<string>("");
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState("");
  const [showGenitaliaOutro, setShowGenitaliaOutro] = useState(false);
  const [showPrefOutro, setShowPrefOutro] = useState(false);
  const [documentosPreview, setDocumentosPreview] = useState<any[]>([]);
  const [documentosFiles, setDocumentosFiles] = useState<File[]>([]);
  const [videoFile, setVideoFile] = useState<File | null>(null);
  const [videoPreview, setVideoPreview] = useState<string>("");
  const [galeriaFiles, setGaleriaFiles] = useState<File[]>([]);
  const [galeriaPreview, setGaleriaPreview] = useState<string[]>([]);
  const [retryAfter, setRetryAfter] = useState(0);
  const [retryTimeLeft, setRetryTimeLeft] = useState(0);

  // Referências para arquivos
  const documentosRef = useRef<HTMLInputElement>(null);
  const videoRef = useRef<HTMLInputElement>(null);
  const galeriaFotosRef = useRef<HTMLInputElement>(null);

  const router = useRouter();

  // Busca inicial de ESTADOS e CIDADES
  useEffect(() => {
    const supabase = createClientComponentClient();

    const fetchEstados = async () => {
      const { data, error } = await supabase.from("estados").select("id, nome, uf").order("nome");
      if (error) console.error("Erro ao buscar estados:", error);
      else if (data) setEstados(data as Estado[]);
    };

    const fetchCidades = async () => {
      const { data, error } = await supabase.from("cidades").select("id, nome, estado_id").order("nome");
      if (error) console.error("Erro ao buscar cidades:", error);
      else if (data) setCidades(data as Cidade[]);
    };

    fetchEstados();
    fetchCidades();
  }, []);
  
  // Filtra as cidades quando um estado é selecionado
  useEffect(() => {
    if (estadoSelecionado) {
      const idEstadoSelecionado = Number(estadoSelecionado);
      const filtradas = cidades.filter(c => c.estado_id === idEstadoSelecionado);
      setCidadesFiltradas(filtradas);
    } else {
      setCidadesFiltradas([]);
    }
    // Reseta a cidade ao mudar o estado
    setForm(prev => ({ ...prev, cidade_id: "" }));
  }, [estadoSelecionado, cidades]);

  // Preview da foto
  useEffect(() => {
    if (!fotoFile) {
      setFotoPreview("");
      return;
    }
    const url = URL.createObjectURL(fotoFile);
    setFotoPreview(url);
    return () => URL.revokeObjectURL(url);
  }, [fotoFile]);

  // Upload da foto para Supabase Storage
  const handleFotoUpload = async (file: File) => {
    setFotoMsg("Enviando...");
    const supabase = createClientComponentClient();
    const fileExt = file.name.split(".").pop();
    const fileName = `perfil/${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    
    try {
      const { data, error } = await supabase.storage
        .from("media")
        .upload(fileName, file, { upsert: false });
      
      if (error) {
        setFotoMsg("Erro ao enviar foto.");
        console.error("Erro upload foto:", error);
        return null;
      }

      // A API espera o path, não a URL completa
      setFotoMsg("Foto enviada com sucesso!");
      return { path: fileName };

    } catch (error) {
      console.error("Erro no upload:", error);
      setFotoMsg("Erro ao enviar foto.");
      return null;
    }
  };

  // Upload de documentos para Supabase Storage
  const handleDocumentosUpload = async (file: File) => {
    const supabase = createClientComponentClient();
    const fileExt = file.name.split(".").pop();
    const fileName = `documentos/${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    const { data, error } = await supabase.storage
      .from("media")
      .upload(fileName, file, { upsert: false });
    if (error) {
      console.error("Erro ao enviar documento:", error);
      return null;
    }
     // Retorna o path para ser usado
    return { path: fileName };
  };

  // Upload de vídeo para Supabase Storage
  const handleVideoUpload = async (file: File) => {
    const supabase = createClientComponentClient();
    const fileExt = file.name.split(".").pop();
    const fileName = `videos-verificacao/${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    const { data, error } = await supabase.storage
      .from("media")
      .upload(fileName, file, { upsert: false });
    if (error) {
      console.error("Erro ao enviar vídeo:", error);
      return null;
    }
    // Retorna o path para ser usado
    return { path: fileName };
  };

  // Upload de fotos da galeria
  const handleGaleriaUpload = async (file: File) => {
    const supabase = createClientComponentClient();
    const fileExt = file.name.split(".").pop();
    const fileName = `galeria/${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    
    try {
      const { data, error } = await supabase.storage
        .from("media")
        .upload(fileName, file, { upsert: false });
      
      if (error) {
        console.error("Erro upload galeria:", error);
        return null;
      }

      // Retorna o path para ser usado
      return { path: fileName };
    } catch (error) {
      console.error("Erro no upload da galeria:", error);
      return null;
    }
  };

  // Atualiza previews ao selecionar arquivos
  useEffect(() => {
    if (!documentosFiles.length) {
      setDocumentosPreview([]);
      return;
    }
    const previews = documentosFiles.map(file => {
      if (file.type.startsWith("image/")) {
        const url = URL.createObjectURL(file);
        return { url, type: "image", name: file.name };
      } else if (file.type === "application/pdf") {
        return { url: "/assets/img/pdf-icon.png", type: "pdf", name: file.name };
      } else {
        return { url: "", type: "other", name: file.name };
      }
    });
    setDocumentosPreview(previews);
    return () => previews.forEach(p => p.type === "image" && URL.revokeObjectURL(p.url));
  }, [documentosFiles]);

  // Preview do vídeo
  useEffect(() => {
    if (!videoFile) {
      setVideoPreview("");
      return;
    }
    const url = URL.createObjectURL(videoFile);
    setVideoPreview(url);
    return () => URL.revokeObjectURL(url);
  }, [videoFile]);

  useEffect(() => {
    if (!galeriaFiles.length) {
      setGaleriaPreview([]);
      return;
    }
    const urls = galeriaFiles.map(file => URL.createObjectURL(file));
    setGaleriaPreview(urls);
    return () => urls.forEach(url => URL.revokeObjectURL(url));
  }, [galeriaFiles]);

  // Timer para retry
  const startRetryTimer = (seconds: number) => {
    setRetryAfter(seconds);
    setRetryTimeLeft(seconds);

    const timer = setInterval(() => {
      setRetryTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
  };

  // Limpar arquivos em caso de erro
  const cleanupFiles = async (files: { path: string }[]) => {
    const supabase = createClientComponentClient();
    for (const file of files) {
      try {
        const { error } = await supabase.storage
          .from("media")
          .remove([file.path]);
        
        if (error) {
          console.error("Erro ao limpar arquivo:", file.path, error);
        }
      } catch (error) {
        console.error("Erro ao limpar arquivo:", file.path, error);
      }
    }
  };

  const handleEstadoChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setEstadoSelecionado(e.target.value);
  };

  // Handle form submit
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (loading || retryTimeLeft > 0) return;

    if (
      !form.nome || !form.idade || !form.cidade_id ||
      !form.descricao || !fotoFile || !documentosFiles.length || !videoFile
    ) {
      setMsg("Por favor, preencha todos os campos obrigatórios e envie os arquivos necessários.");
      return;
    }

    if (!form.email) {
      setMsg("Por favor, preencha o campo de e-mail.");
      return;
    }

    if (!validarTelefone(form.telefone)) {
      setMsg("Por favor, insira um número de telefone válido com DDD.");
      return;
    }

    setLoading(true);
    setMsg("");

    let fotoData = null;
    let videoUrl = null;
    let galeriaUrls = [];
    let documentosPaths = [];

    try {
      // 1. Upload da foto de perfil (se existir)
      if (fotoFile) {
        fotoData = await handleFotoUpload(fotoFile);
        if (!fotoData?.path) throw new Error("Falha no upload da foto de perfil.");
      }

      // 2. Upload do vídeo de verificação (se existir)
      if (videoFile) {
        const videoResult = await handleVideoUpload(videoFile);
        if (!videoResult) throw new Error("Falha no upload do vídeo de verificação.");
        videoUrl = videoResult.path;
      }

      // 3. Upload das fotos da galeria (se existirem)
      if (galeriaFiles.length > 0) {
        for (const file of galeriaFiles) {
          const result = await handleGaleriaUpload(file);
          if (result?.path) {
            galeriaUrls.push(result.path);
          } else {
            console.warn(`Falha no upload de um arquivo da galeria: ${file.name}`);
            // Decide-se por continuar ou parar. Por enquanto, continuamos.
          }
        }
        if (galeriaUrls.length !== galeriaFiles.length) {
            // Opcional: Lançar erro se nem todos os uploads da galeria funcionaram
            // throw new Error("Falha no upload de uma ou mais fotos da galeria.");
        }
      }

      // 4. Upload dos documentos (se existirem)
      if (documentosFiles.length > 0) {
        for (const file of documentosFiles) {
          const result = await handleDocumentosUpload(file);
          if (result?.path) {
            // Ajustado para enviar apenas o 'path', conforme a nova função SQL.
            documentosPaths.push({ path: result.path });
          } else {
            console.warn(`Falha no upload de um documento: ${file.name}`);
          }
        }
      }

      // 5. Montar o corpo da requisição com todos os dados e URLs
      const dadosCadastro = {
        ...form,
        senha: generateRandomPassword(), // Gera e envia uma senha aleatória
        foto: fotoData?.path || null,
        galeria_fotos: galeriaUrls,
        video_url: videoUrl,
        documentos: documentosPaths,
        // Garante que "Outro" seja tratado
        genitalia: form.genitalia === 'Outro' ? form.genitalia_outro : form.genitalia,
        preferencia_sexual: form.preferencia_sexual === 'Outro' ? form.preferencia_sexual_outro : form.preferencia_sexual,
        tipo_atendimento: form.tipo_atendimento,
        valor_padrao: form.valor_padrao,
        valor_observacao: form.valor_observacao,
        p_altura: form.altura ? parseFloat(form.altura) : null,
      };

      // 6. Enviar tudo para a API
      const response = await fetch('/api/cadastro', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dadosCadastro),
      });

      const result = await response.json();

      if (!response.ok) {
        // Se a API retornar erro, limpa os arquivos e exibe a mensagem diretamente.
        const filesToClean = [{ path: fotoData?.path }].concat(galeriaUrls.map(p => ({ path: p })));
        if(videoUrl) filesToClean.push({path: videoUrl});
        await cleanupFiles(filesToClean.filter(f => f.path) as {path: string}[]);

        const errorMessage = result.error?.message || 'Ocorreu um erro desconhecido no cadastro.';
        setMsg(errorMessage);
        setLoading(false);
        return; // Para a execução aqui
      }
      
      // Se tudo correu bem, redireciona para a página de sucesso
      router.push('/obrigado');

    } catch (error: any) {
      // Este catch agora lidará com erros de upload ou falhas de rede
      setMsg(error.message || "Erro ao processar o cadastro. Tente novamente.");
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value, type } = e.target;

    if (type === "checkbox") {
      const { checked } = e.target as HTMLInputElement;
      setForm(prev => ({ ...prev, [name]: checked }));
    } else {
      setForm(prev => ({ ...prev, [name]: value }));
    }

    if (name === "genitalia") {
      setShowGenitaliaOutro(value === "Outro");
    }
    if (name === "preferencia_sexual") {
      setShowPrefOutro(value === "Outro");
    }
    if (name === "tipo_atendimento") {
      setForm(prev => ({ ...prev, tipo_atendimento: value }));
      return;
    }
    if (name === "valor_padrao") {
      setForm(prev => ({ ...prev, valor_padrao: value }));
      return;
    }
    if (name === "valor_observacao") {
      setForm(prev => ({ ...prev, valor_observacao: value }));
      return;
    }
    if (name === "endereco" || name === "bairro" || name === "cep") {
      setForm(prev => ({ ...prev, [name]: value }));
      return;
    }
    if (name === "altura") {
      setForm(prev => ({ ...prev, altura: value }));
      return;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
        <div>
          <h1 className="text-center text-4xl font-extrabold text-secondary">
            Crie seu Perfil de Destaque
          </h1>
          <p className="mt-2 text-center text-sm text-gray-600">
            Já tem uma conta?{' '}
            <Link href="/login" className="font-medium text-primary hover:text-primary-hover">
              Faça seu cadastro
            </Link>
          </p>
        </div>
        <form className="w-full max-w-4xl bg-[#fdfafc] p-8 rounded-2xl shadow-lg space-y-8 border border-[#e0c8a2]" onSubmit={handleSubmit} autoComplete="off">
          
          <div className="text-center">
            <h2 className="text-3xl font-bold text-[#4E3950]">Cadastro de Acompanhante</h2>
            <p className="text-[#4E3950] mt-2 max-w-2xl mx-auto">
              Preencha seu cadastro completo abaixo. Após o envio, seus dados serão analisados pela equipe. Você receberá as credenciais de acesso por e-mail e, após aprovação, seu perfil será incluído na plataforma Sigilosas VIP.
            </p>
          </div>

          {/* Bloco de Dados Cadastrais */}
          <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 className="text-xl font-bold text-[#4E3950] border-b border-[#CFB78B] pb-2 mb-4">
              Dados Cadastrais
            </h3>
            <div className="grid md:grid-cols-2 gap-6">
              <div>
                <label className={labelClass} htmlFor="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" value={form.nome} onChange={handleChange} className={inputClass} required />
              </div>
              <div>
                <label className={labelClass} htmlFor="telefone">Telefone (WhatsApp) *</label>
                <input type="tel" id="telefone" name="telefone" value={form.telefone} onChange={handleChange} className={inputClass} placeholder="(99) 99999-9999" required />
              </div>
              <div>
                <label className={labelClass} htmlFor="email">E-mail *</label>
                <input type="email" id="email" name="email" value={form.email} onChange={handleChange} className={inputClass} required />
              </div>
              <div>
                <label className={labelClass} htmlFor="idade">Idade *</label>
                <input type="number" id="idade" name="idade" value={form.idade} onChange={handleChange} className={inputClass} required min="18" />
              </div>
            </div>
          </div>

          {/* Bloco de Detalhes Físicos e Preferências */}
          <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 className="text-xl font-bold text-[#4E3950] border-b border-[#CFB78B] pb-2 mb-4">
              Detalhes Físicos e Preferências
            </h3>
            <div className="grid md:grid-cols-2 gap-6">
              <div>
                <label className={labelClass}>Gênero *</label>
                <select name="genero" className={inputClass} value={form.genero} onChange={handleChange} required>
                  <option value="">Selecione</option>
                  <option value="feminino">Feminino</option>
                  <option value="masculino">Masculino</option>
                  <option value="trans">Trans</option>
                  <option value="outro">Outro</option>
                </select>
              </div>
              <div>
                <label className={labelClass}>Genitália</label>
                <select name="genitalia" className={inputClass} value={form.genitalia} onChange={handleChange}>
                  <option value="">Selecione</option>
                  <option value="vagina">Vagina</option>
                  <option value="penis">Pênis</option>
                  <option value="outro">Outro</option>
                </select>
                {showGenitaliaOutro && <input type="text" name="genitalia_outro" className={inputClass + " mt-2"} placeholder="Descreva a genitália" value={form.genitalia_outro} onChange={handleChange} />}
              </div>
              <div>
                <label className={labelClass}>Preferência sexual</label>
                <select name="preferencia_sexual" className={inputClass} value={form.preferencia_sexual} onChange={handleChange}>
                  <option value="">Selecione</option>
                  <option value="heterossexual">Heterossexual</option>
                  <option value="homossexual">Homossexual</option>
                  <option value="bissexual">Bissexual</option>
                  <option value="outro">Outro</option>
                </select>
                {showPrefOutro && <input type="text" name="preferencia_sexual_outro" className={inputClass + " mt-2"} placeholder="Descreva a preferência" value={form.preferencia_sexual_outro} onChange={handleChange} />}
              </div>
              <div>
                <label className={labelClass}>Peso (kg)</label>
                <input type="text" name="peso" className={inputClass} value={form.peso} onChange={handleChange} placeholder="Ex: 65.5" />
              </div>
              <div>
                <label className={labelClass}>Altura (m)</label>
                <input
                  type="number"
                  name="altura"
                  className={inputClass}
                  value={form.altura || ""}
                  onChange={handleChange}
                  min="0"
                  step="0.01"
                  placeholder="Ex: 1.65"
                  required
                />
              </div>
              <div>
                <label className={labelClass}>Etnia</label>
                <select name="etnia" className={inputClass} value={form.etnia} onChange={handleChange}>
                  <option value="">Selecione</option>
                  <option value="Branca">Branca</option>
                  <option value="Negra">Negra</option>
                  <option value="Parda">Parda</option>
                  <option value="Amarela">Amarela</option>
                  <option value="Indígena">Indígena</option>
                  <option value="Outro">Outro</option>
                </select>
              </div>
              <div>
                <label className={labelClass}>Cor dos olhos</label>
                <input type="text" name="cor_olhos" className={inputClass} value={form.cor_olhos} onChange={handleChange} />
              </div>
              <div>
                <label className={labelClass}>Estilo de cabelo</label>
                <input type="text" name="estilo_cabelo" className={inputClass} value={form.estilo_cabelo} onChange={handleChange} />
              </div>
              <div>
                <label className={labelClass}>Tamanho do cabelo</label>
                <input type="text" name="tamanho_cabelo" className={inputClass} value={form.tamanho_cabelo} onChange={handleChange} />
              </div>
              <div>
                <label className={labelClass}>Tamanho do pé</label>
                <input type="text" name="tamanho_pe" className={inputClass} value={form.tamanho_pe} onChange={handleChange} />
              </div>
            </div>
            <div className="flex gap-8 items-center flex-wrap mt-4">
              <label className="flex items-center"><input type="checkbox" name="silicone" checked={form.silicone} onChange={handleChange} className={checkboxClass} />Silicone</label>
              <label className="flex items-center"><input type="checkbox" name="tatuagens" checked={form.tatuagens} onChange={handleChange} className={checkboxClass} />Tatuagens</label>
              <label className="flex items-center"><input type="checkbox" name="piercings" checked={form.piercings} onChange={handleChange} className={checkboxClass} />Piercings</label>
            </div>
          </div>
          
          {/* Bloco de Localização e Atendimento */}
          <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
             <h3 className="text-xl font-bold text-[#4E3950] border-b border-[#CFB78B] pb-2 mb-4">
              Localização e Atendimento
            </h3>
            <div className="grid md:grid-cols-2 gap-6">
               <div className="md:col-span-1">
                <label htmlFor="estado" className={labelClass}>Estado *</label>
                <select id="estado" name="estado_id" value={estadoSelecionado} onChange={handleEstadoChange} className={inputClass} required>
                  <option value="">Selecione um estado</option>
                  {estados.map((estado) => (<option key={estado.id} value={estado.id}>{estado.nome}</option>))}
                </select>
              </div>
              <div className="md:col-span-1">
                <label htmlFor="cidade_id" className={labelClass}>Cidade *</label>
                <select id="cidade_id" name="cidade_id" value={form.cidade_id} onChange={handleChange} className={inputClass} required disabled={!estadoSelecionado || cidadesFiltradas.length === 0}>
                  <option value="">Selecione uma cidade</option>
                  {cidadesFiltradas.map((cidade) => (<option key={cidade.id} value={cidade.id}>{cidade.nome}</option>))}
                </select>
              </div>
              <div>
                <label className={labelClass} htmlFor="endereco">Endereço</label>
                <input type="text" id="endereco" name="endereco" className={inputClass} value={form.endereco} onChange={handleChange} />
              </div>
              <div>
                <label className={labelClass} htmlFor="bairro">Bairro</label>
                <input type="text" id="bairro" name="bairro" className={inputClass} value={form.bairro} onChange={handleChange} />
              </div>
              <div>
                <label className={labelClass} htmlFor="cep">CEP</label>
                <input type="text" id="cep" name="cep" className={inputClass} value={form.cep} onChange={handleChange} />
              </div>
               <div>
                <label className={labelClass}>Idiomas</label>
                <input type="text" name="idiomas" className={inputClass} value={form.idiomas} onChange={handleChange} />
              </div>
               <div>
                <label className={labelClass}>Horário de expediente</label>
                <input type="text" name="horario_expediente" className={inputClass} value={form.horario_expediente} onChange={handleChange} />
              </div>
              <div className="md:col-span-2">
                <label className={labelClass}>Formas de pagamento</label>
                <input type="text" name="formas_pagamento" className={inputClass} value={form.formas_pagamento} onChange={handleChange} />
              </div>
              <div className="md:col-span-2">
                <label className={labelClass}>Tipo de Atendimento *</label>
                <div className="flex gap-4 mb-4">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="tipo_atendimento" value="presencial" checked={form.tipo_atendimento === 'presencial'} onChange={handleChange} required />
                    <span role="img" aria-label="Presencial">🏢</span> Presencial
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="tipo_atendimento" value="online" checked={form.tipo_atendimento === 'online'} onChange={handleChange} required />
                    <span role="img" aria-label="Online">💻</span> Online
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="tipo_atendimento" value="ambos" checked={form.tipo_atendimento === 'ambos'} onChange={handleChange} required />
                    <span role="img" aria-label="Ambos">🌐</span> Ambos
                  </label>
                </div>
              </div>
              <div>
                <label className={labelClass}>Valor do Atendimento (R$) * <span className="text-xs text-gray-500">/ hora</span></label>
                <input
                  type="number"
                  name="valor_padrao"
                  className={inputClass}
                  value={form.valor_padrao || ""}
                  onChange={handleChange}
                  min="0"
                  step="0.01"
                  required
                  placeholder="Ex: 200.00"
                />
              </div>
              <div>
                <label className={labelClass}>Observações sobre o valor</label>
                <textarea
                  name="valor_observacao"
                  className={inputClass}
                  value={form.valor_observacao || ""}
                  onChange={handleChange}
                  placeholder="Ex: Valor pode variar para atendimentos especiais, promoções, etc."
                  rows={2}
                />
              </div>
            </div>
          </div>

          {/* Bloco de Descrição */}
           <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
             <h3 className="text-xl font-bold text-[#4E3950] border-b border-[#CFB78B] pb-2 mb-4">
              Descrição do Perfil
            </h3>
            <textarea name="descricao" className={inputClass} rows={5} value={form.descricao} onChange={handleChange} placeholder="Fale sobre você, seus serviços, e o que te torna especial."/>
          </div>

          {/* Bloco de Mídia */}
          <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 className="text-xl font-bold text-[#4E3950] border-b border-[#CFB78B] pb-2 mb-4">
              Fotos e Vídeos
            </h3>
            <div className="grid md:grid-cols-2 gap-8">
              {/* Foto de Perfil */}
              <div className="space-y-4">
                <label className={labelClass}>Foto de Perfil *</label>
                {fotoPreview && (
                  <div className="relative w-48 h-48 mx-auto mb-4">
                    <Image src={fotoPreview} alt="Preview da foto" fill className="object-cover rounded-lg" sizes="(max-width: 768px) 100vw, 50vw" priority/>
                  </div>
                )}
                <input type="file" accept="image/*" onChange={(e) => { const file = e.target.files?.[0]; if (file) { if (file.size > 5*1024*1024) { setFotoMsg("A foto deve ter no máximo 5MB"); return; } setFotoFile(file); setFotoMsg(""); } }} className="hidden" id="foto"/>
                <label htmlFor="foto" className={uploadButtonClass}>
                  <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                  {fotoPreview ? "Alterar foto" : "Adicionar foto"}
                </label>
                {fotoMsg && <p className="text-center text-sm mt-2 text-[#4E3950]">{fotoMsg}</p>}
              </div>
              {/* Vídeo de Verificação */}
              <div className="space-y-4">
                <label className={labelClass}>Vídeo de Verificação *</label>
                {videoPreview && (
                  <div className="mt-2 text-center">
                    <video src={videoPreview} controls className="w-40 h-40 rounded-lg shadow-md object-cover mx-auto"/>
                    <button type="button" onClick={() => { setVideoFile(null); setVideoPreview(''); }} className="mt-2 text-red-500 hover:text-red-700 transition-colors flex items-center gap-1 mx-auto text-sm">
                       <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      Remover vídeo
                    </button>
                  </div>
                )}
                <input type="file" accept="video/*" onChange={(e) => { if (e.target.files?.length) { setVideoFile(e.target.files[0]); }}} className="hidden" id="video"/>
                <label htmlFor="video" className={uploadButtonClass}>
                  <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                  {videoPreview ? "Alterar vídeo" : "Adicionar vídeo"}
                </label>
                <p className="text-xs text-center text-gray-500 mt-1">Grave um vídeo segurando seu RG ou CNH ao lado do rosto.</p>
              </div>
            </div>
             {/* Documentos */}
            <div className="space-y-4 pt-6 border-t border-gray-200">
              <label className={labelClass}>Documentos (RG ou CNH frente e verso) *</label>
              {documentosPreview.length > 0 && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                  {documentosPreview.map((doc, index) => (
                    <div key={index} className="relative">
                      {doc.type === "image" ? (<div className="relative w-full h-40"><Image src={doc.url} alt={`Documento ${index + 1}`} fill className="object-cover rounded-lg" sizes="50vw"/></div>) : (<div className="w-full h-40 bg-gray-100 rounded-lg flex items-center justify-center p-2"><span className="text-gray-500 text-xs text-center">{doc.name}</span></div>)}
                      <button onClick={(e) => { e.preventDefault(); setDocumentosFiles(files => files.filter((_, i) => i !== index)); setDocumentosPreview(prev => prev.filter((_, i) => i !== index));}} className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">×</button>
                    </div>
                  ))}
                </div>
              )}
              <input type="file" accept="image/*,.pdf" onChange={(e) => { if (e.target.files?.length) { setDocumentosFiles(prev => [...prev, ...Array.from(e.target.files || [])]); }}} className="hidden" id="documentos" multiple/>
              <label htmlFor="documentos" className={uploadButtonClass}>
                <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Adicionar documentos
              </label>
            </div>
             {/* Galeria de Fotos */}
            <div className="space-y-4 pt-6 border-t border-gray-200">
              <label className={labelClass}>Galeria de Fotos Públicas</label>
              {galeriaPreview.length > 0 && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                  {galeriaPreview.map((url, index) => (
                    <div key={index} className="relative">
                      <div className="relative w-full h-40"><Image src={url} alt={`Foto ${index + 1}`} fill className="object-cover rounded-lg" sizes="50vw"/></div>
                      <button onClick={(e) => { e.preventDefault(); setGaleriaFiles(files => files.filter((_, i) => i !== index)); setGaleriaPreview(prev => prev.filter((_, i) => i !== index));}} className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">×</button>
                    </div>
                  ))}
                </div>
              )}
              <input type="file" accept="image/*" onChange={(e) => { if (e.target.files?.length) {setGaleriaFiles(prev => [...prev, ...Array.from(e.target.files || [])]);}}} className="hidden" id="galeria" multiple/>
              <label htmlFor="galeria" className={uploadButtonClass}>
                <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Adicionar fotos à galeria
              </label>
            </div>
          </div>

          <div className="mt-8 text-center">
            <button type="submit" disabled={loading || retryTimeLeft > 0} className={buttonClass}>
              {loading ? 'Enviando...' : (retryTimeLeft > 0 ? `Aguarde ${retryTimeLeft}s` : 'Cadastrar')}
            </button>
            {msg && (
              <p className={`text-center p-3 rounded-lg mt-4 ${String(msg).includes('sucesso') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'}`}>
                {msg}
              </p>
            )}
          </div>
        </form>
      </div>
    </div>
  );
}

// Tailwind input style
// Add this to your global CSS if not present:
// .input { @apply w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-purple-400; } 