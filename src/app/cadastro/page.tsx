"use client";
import { useState, useEffect, useRef } from "react";
import Image from "next/image";
import { createClientComponentClient } from "@supabase/auth-helpers-nextjs";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { validarTelefone, formatarTelefone } from '@/lib/validation';

interface Cidade {
  id: string;
  nome: string;
  estado: string;
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
const inputClass = "w-full bg-white border border-[#CFB78B] rounded-lg px-4 py-2 text-[#4E3950] focus:outline-none focus:ring-2 focus:ring-[#CFB78B] text-base transition mb-0 placeholder-[#CFB78B]";
const labelClass = "block font-semibold text-[#4E3950] mb-1";
const checkboxClass = "accent-[#CFB78B] w-5 h-5 mr-2 align-middle";
const buttonClass = "w-full py-3 bg-[#4E3950] text-white border-none rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-[#CFB78B] hover:text-[#4E3950] disabled:opacity-50 disabled:cursor-not-allowed mt-2";
const uploadButtonClass = "flex items-center justify-center gap-2 w-full py-3 bg-white border-2 border-dashed border-[#CFB78B] rounded-lg font-medium text-[#4E3950] cursor-pointer transition-all hover:bg-[#fdf8ed] hover:border-[#b89a76] active:bg-[#f5e9d4] disabled:opacity-50 disabled:cursor-not-allowed";

export default function CadastroAcompanhante() {
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
    cidade_id: "",
    estado: "",
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

  useEffect(() => {
    const fetchCidades = async () => {
      const supabase = createClientComponentClient();
      const { data, error } = await supabase
        .from("cidades")
        .select("id, nome, estado")
        .order("nome");
      if (!error && data) setCidades(data);
    };
    fetchCidades();
  }, []);
  
  useEffect(() => {
    if (estadoSelecionado) {
      const filtradas = cidades.filter(c => c.estado_uf === estadoSelecionado);
      setCidadesFiltradas(filtradas);
      setForm(prev => ({ ...prev, cidade_id: "" })); // Reseta a cidade ao mudar o estado
    } else {
      setCidadesFiltradas([]);
    }
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
        .from("images")
        .upload(fileName, file, { upsert: false });
      
      if (error) {
        setFotoMsg("Erro ao enviar foto.");
        console.error("Erro upload foto:", error);
        return null;
      }

      const { data: publicUrl } = supabase.storage
        .from("images")
        .getPublicUrl(fileName);

      setFotoMsg("Foto enviada com sucesso!");
      return {
        url: publicUrl?.publicUrl || null,
        path: fileName
      };
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
      .from("documents")
      .upload(fileName, file, { upsert: false });
    if (error) {
      console.error("Erro ao enviar documento:", error);
      return null;
    }
    const { data: publicUrl } = supabase.storage
      .from("documents")
      .getPublicUrl(fileName);
    return publicUrl?.publicUrl || null;
  };

  // Upload de vídeo para Supabase Storage
  const handleVideoUpload = async (file: File) => {
    const supabase = createClientComponentClient();
    const fileExt = file.name.split(".").pop();
    const fileName = `videos-verificacao/${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    const { data, error } = await supabase.storage
      .from("videos")
      .upload(fileName, file, { upsert: false });
    if (error) {
      console.error("Erro ao enviar vídeo:", error);
      return null;
    }
    const { data: publicUrl } = supabase.storage
      .from("videos")
      .getPublicUrl(fileName);
    return publicUrl?.publicUrl || null;
  };

  // Upload de fotos da galeria
  const handleGaleriaUpload = async (file: File) => {
    const supabase = createClientComponentClient();
    const fileExt = file.name.split(".").pop();
    const fileName = `galeria/${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    
    try {
      const { data, error } = await supabase.storage
        .from("images")
        .upload(fileName, file, { upsert: false });
      
      if (error) {
        console.error("Erro upload galeria:", error);
        return null;
      }

      const { data: publicUrl } = supabase.storage
        .from("images")
        .getPublicUrl(fileName);

      return {
        url: publicUrl?.publicUrl || null,
        path: fileName
      };
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
          .from("images")
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
    const novoEstado = e.target.value;
    setEstadoSelecionado(novoEstado);
    setForm(prev => ({ ...prev, estado: novoEstado }));
  };
  
  const handleTelefoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let valor = e.target.value;
    
    // Remove tudo que não é número
    const numeros = valor.replace(/\D/g, '');
    
    // Formata o número conforme vai digitando
    if (numeros.length <= 2) {
      valor = numeros;
    } else if (numeros.length <= 7) {
      valor = `(${numeros.slice(0,2)}) ${numeros.slice(2)}`;
    } else {
      valor = `(${numeros.slice(0,2)}) ${numeros.slice(2,7)}-${numeros.slice(7,11)}`;
    }
    
    // Atualiza o estado do formulário
    setForm(prev => ({
      ...prev,
      telefone: valor
    }));
  };

  // Handle form submit
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setMsg("");

    const uploadedFiles: { path: string }[] = [];

    try {
      // Validar telefone
      const telefoneError = validarTelefone(form.telefone);
      if (telefoneError) {
        setMsg(telefoneError);
        setLoading(false);
        return;
      }

      // Formatar telefone
      const telefoneFormatado = formatarTelefone(form.telefone);
      if (!telefoneFormatado) {
        setMsg("Telefone inválido. Use o formato (XX) XXXXX-XXXX");
        setLoading(false);
        return;
      }

      // Upload da foto principal
      let fotoUrl = null;
      if (fotoFile) {
        const result = await handleFotoUpload(fotoFile);
        if (!result) {
          setMsg("Erro ao enviar foto principal");
          setLoading(false);
          return;
        }
        fotoUrl = result.url;
        uploadedFiles.push({ path: result.path });
      }

      // Upload das fotos da galeria
      const galeriaUrls: string[] = [];
      for (const file of galeriaFiles) {
        const result = await handleGaleriaUpload(file);
        if (!result || !result.url) {
          setMsg("Erro ao enviar foto da galeria");
          await cleanupFiles(uploadedFiles);
          setLoading(false);
          return;
        }
        galeriaUrls.push(result.url);
        uploadedFiles.push({ path: result.path });
      }

      // Preparar dados para envio
      const dadosParaEnvio = {
        nome: form.nome,
        email: form.email,
        telefone: telefoneFormatado,
        senha: form.senha,
        cidade_id: form.cidade_id,
        estado: form.estado,
        idade: form.idade,
        genero: form.genero,
        genitalia: form.genitalia,
        genitalia_outro: form.genitalia_outro,
        preferencia_sexual: form.preferencia_sexual,
        preferencia_sexual_outro: form.preferencia_sexual_outro,
        peso: form.peso,
        altura: form.altura,
        etnia: form.etnia,
        cor_olhos: form.cor_olhos,
        estilo_cabelo: form.estilo_cabelo,
        tamanho_cabelo: form.tamanho_cabelo,
        tamanho_pe: form.tamanho_pe,
        silicone: form.silicone,
        tatuagens: form.tatuagens,
        piercings: form.piercings,
        fumante: form.fumante,
        idiomas: form.idiomas,
        endereco: form.endereco,
        horario_expediente: form.horario_expediente,
        formas_pagamento: form.formas_pagamento,
        descricao: form.descricao,
        foto: fotoUrl,
        galeria_fotos: galeriaUrls
      };

      // Enviar dados para API
      console.log('7. Iniciando requisição para API...');
      const response = await fetch('/api/cadastro', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(dadosParaEnvio),
      });

      let data;
      const contentType = response.headers.get("content-type");
      
      try {
        // Verifica se a resposta é JSON
        if (contentType && contentType.indexOf("application/json") !== -1) {
          data = await response.json();
        } else {
          // Se não for JSON, pega o texto da resposta para debug
          const text = await response.text();
          console.error('9. ERRO: Resposta não-JSON recebida:', {
            status: response.status,
            contentType,
            text: text.substring(0, 500) // Limita o tamanho do log
          });
          throw new Error('Erro interno do servidor. Por favor, tente novamente em alguns minutos.');
        }
      } catch (error) {
        console.error('9. ERRO ao processar resposta:', error);
        await cleanupFiles(uploadedFiles);
        setMsg("Erro interno do servidor. Por favor, tente novamente em alguns minutos.");
        setLoading(false);
        return;
      }

      console.log('8. Resposta da API:', {
        status: response.status,
        ok: response.ok,
        data
      });

      // Tratamento específico para erro de rate limit
      if (response.status === 429) {
        console.log('9. ERRO: Rate limit atingido (429)');
        const retryAfter = parseInt(response.headers.get('Retry-After') || '60');
        setMsg("Sistema temporariamente indisponível. Por favor, aguarde...");
        startRetryTimer(retryAfter);
        await cleanupFiles(uploadedFiles);
        setLoading(false);
        return;
      }

      // Tratamento para erro interno do servidor
      if (response.status === 500) {
        console.error('9. ERRO: Erro interno do servidor (500):', data);
        setMsg("Erro interno do servidor. Por favor, tente novamente em alguns minutos.");
        await cleanupFiles(uploadedFiles);
        setLoading(false);
        return;
      }

      if (!response.ok) {
        console.error('9. ERRO na resposta da API:', data);
        
        if (typeof data.erro === 'string' && data.erro.toLowerCase().includes('rate limit')) {
          setMsg("Sistema temporariamente indisponível. Por favor, aguarde...");
          startRetryTimer(parseInt(response.headers.get('Retry-After') || '60'));
        } else {
          setMsg(data.erro || "Erro ao processar o cadastro");
        }
        
        await cleanupFiles(uploadedFiles);
        setLoading(false);
        return;
      }

      console.log('9. Cadastro realizado com sucesso');
      console.log('=== FIM DO PROCESSO DE CADASTRO (FRONTEND) ===');

      // Se chegou até aqui, cadastro foi realizado com sucesso
      router.push('/obrigado');

    } catch (error) {
      console.error('ERRO GERAL no processo de cadastro:', error);
      await cleanupFiles(uploadedFiles);
      setMsg("Erro ao processar o cadastro. Por favor, tente novamente.");
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#f6f3f9]">
      {/* Header */}
      <header className="w-full flex items-center justify-between px-6 py-4 bg-white border-b border-[#CFB78B] shadow-sm">
        <div className="flex items-center gap-3">
          <img src="/assets/img/logo.png" alt="SigilosasVip - Logo" className="h-10 w-auto" />
        </div>
        <Link href="/" className="text-[#4E3950] font-semibold px-4 py-2 rounded-lg border border-[#CFB78B] hover:bg-[#CFB78B] hover:text-[#4E3950] transition">Voltar para o site</Link>
      </header>
      <main className="flex-1 flex items-center justify-center">
        <form className="w-full max-w-3xl bg-white p-8 rounded-2xl shadow-xl space-y-7 border border-[#CFB78B]" onSubmit={handleSubmit} autoComplete="off">
          <h2 className="text-3xl font-bold mb-2 text-center text-[#4E3950]">Cadastro de Acompanhante</h2>
          <p className="text-center text-[#4E3950] text-lg mb-6">
            Preencha seu cadastro completo abaixo. Após o envio, seus dados serão analisados pela equipe. Você receberá as credenciais de acesso por e-mail e, após aprovação, seu perfil será incluído na plataforma Sigilosas VIP.
          </p>
          {/* Dados pessoais */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label className={labelClass}>Nome do acompanhante *</label>
              <input type="text" name="nome" className={inputClass} value={form.nome} onChange={e => setForm(f => ({ ...f, nome: e.target.value }))} required />
            </div>
            <div>
              <label className={labelClass}>Idade *</label>
              <input type="number" name="idade" className={inputClass} value={form.idade} onChange={e => setForm(f => ({ ...f, idade: e.target.value }))} min={18} required />
            </div>
            <div>
              <label className={labelClass}>Gênero *</label>
              <select name="genero" className={inputClass} value={form.genero} onChange={e => setForm(f => ({ ...f, genero: e.target.value }))} required>
                <option value="">Selecione</option>
                <option value="F">Feminino</option>
                <option value="M">Masculino</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div>
              <label className={labelClass}>Genitália</label>
              <select
                name="genitalia"
                className={inputClass}
                value={form.genitalia}
                onChange={e => {
                  setForm(f => ({ ...f, genitalia: e.target.value }));
                  setShowGenitaliaOutro(e.target.value === "Outro");
                }}
              >
                <option value="">Selecione</option>
                <option value="Vagina">Vagina</option>
                <option value="Pênis">Pênis</option>
                <option value="Outro">Outro</option>
              </select>
              {showGenitaliaOutro && (
                <input
                  type="text"
                  className={inputClass + " mt-2"}
                  placeholder="Descreva a genitália"
                  value={form.genitalia_outro || ""}
                  onChange={e => setForm(f => ({ ...f, genitalia_outro: e.target.value }))}
                />
              )}
            </div>
            <div>
              <label className={labelClass}>Preferência sexual</label>
              <select
                name="preferencia_sexual"
                className={inputClass}
                value={form.preferencia_sexual}
                onChange={e => {
                  setForm(f => ({ ...f, preferencia_sexual: e.target.value }));
                  setShowPrefOutro(e.target.value === "Outro");
                }}
              >
                <option value="">Selecione</option>
                <option value="Hetero">Hetero</option>
                <option value="Homo">Homo</option>
                <option value="Bi">Bi</option>
                <option value="Outro">Outro</option>
              </select>
              {showPrefOutro && (
                <input
                  type="text"
                  className={inputClass + " mt-2"}
                  placeholder="Descreva a preferência sexual"
                  value={form.preferencia_sexual_outro || ""}
                  onChange={e => setForm(f => ({ ...f, preferencia_sexual_outro: e.target.value }))}
                />
              )}
            </div>
            <div>
              <label className={labelClass}>Peso (kg)</label>
              <input type="text" name="peso" className={inputClass} value={form.peso} onChange={e => setForm(f => ({ ...f, peso: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Altura (m)</label>
              <input type="text" name="altura" className={inputClass} value={form.altura} onChange={e => setForm(f => ({ ...f, altura: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Etnia</label>
              <select
                name="etnia"
                className={inputClass}
                value={form.etnia}
                onChange={e => setForm(f => ({ ...f, etnia: e.target.value }))}
              >
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
              <input type="text" name="cor_olhos" className={inputClass} value={form.cor_olhos} onChange={e => setForm(f => ({ ...f, cor_olhos: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Estilo de cabelo</label>
              <input type="text" name="estilo_cabelo" className={inputClass} value={form.estilo_cabelo} onChange={e => setForm(f => ({ ...f, estilo_cabelo: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Tamanho do cabelo</label>
              <input type="text" name="tamanho_cabelo" className={inputClass} value={form.tamanho_cabelo} onChange={e => setForm(f => ({ ...f, tamanho_cabelo: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Tamanho do pé</label>
              <input type="text" name="tamanho_pe" className={inputClass} value={form.tamanho_pe} onChange={e => setForm(f => ({ ...f, tamanho_pe: e.target.value }))} />
            </div>
          </div>
          {/* Características */}
          <div className="flex gap-8 items-center flex-wrap mb-2">
            <label className={labelClass}><input type="checkbox" checked={form.silicone} onChange={e => setForm(f => ({ ...f, silicone: e.target.checked }))} className={checkboxClass} />Silicone</label>
            <label className={labelClass}><input type="checkbox" checked={form.tatuagens} onChange={e => setForm(f => ({ ...f, tatuagens: e.target.checked }))} className={checkboxClass} />Tatuagens</label>
            <label className={labelClass}><input type="checkbox" checked={form.piercings} onChange={e => setForm(f => ({ ...f, piercings: e.target.checked }))} className={checkboxClass} />Piercings</label>
          </div>
          {/* Outros dados */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label className={labelClass}>Fumante</label>
              <input type="text" name="fumante" className={inputClass} value={form.fumante} onChange={e => setForm(f => ({ ...f, fumante: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Idiomas</label>
              <input type="text" name="idiomas" className={inputClass} value={form.idiomas} onChange={e => setForm(f => ({ ...f, idiomas: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Endereço</label>
              <input type="text" name="endereco" className={inputClass} value={form.endereco} onChange={e => setForm(f => ({ ...f, endereco: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Telefone *</label>
              <input
                type="tel"
                name="telefone"
                className={inputClass}
                value={form.telefone || ''}
                onChange={handleTelefoneChange}
                placeholder="(XX) XXXXX-XXXX"
                required
              />
            </div>
            <div className="md:col-span-1">
              <label htmlFor="estado" className={labelClass}>Estado</label>
              <select
                id="estado"
                name="estado"
                value={estadoSelecionado}
                onChange={handleEstadoChange}
                className={inputClass}
                required
              >
                <option value="">Selecione um estado</option>
                {ESTADOS.map(e => (
                  <option key={e.uf} value={e.uf}>{e.nome}</option>
                ))}
              </select>
            </div>
            <div className="md:col-span-1">
              <label htmlFor="cidade" className={labelClass}>Cidade</label>
              <select
                id="cidade"
                name="cidade_id"
                value={form.cidade_id}
                onChange={(e) => setForm({ ...form, cidade_id: e.target.value })}
                className={inputClass}
                required
                disabled={!estadoSelecionado}
              >
                <option value="">Selecione uma cidade</option>
                {cidadesFiltradas.map(c => (
                  <option key={c.id} value={c.id}>{c.nome}</option>
                ))}
              </select>
            </div>
            <div>
              <label className={labelClass}>Horário de expediente</label>
              <input type="text" name="horario_expediente" className={inputClass} value={form.horario_expediente} onChange={e => setForm(f => ({ ...f, horario_expediente: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Formas de pagamento</label>
              <input type="text" name="formas_pagamento" className={inputClass} value={form.formas_pagamento} onChange={e => setForm(f => ({ ...f, formas_pagamento: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Data de criação</label>
              <input type="date" name="data_criacao" className={inputClass} value={form.data_criacao} onChange={e => setForm(f => ({ ...f, data_criacao: e.target.value }))} />
            </div>
          </div>
          {/* Descrição */}
          <div>
            <label className={labelClass}>Descrição</label>
            <textarea name="descricao" className={inputClass} rows={4} value={form.descricao} onChange={e => setForm(f => ({ ...f, descricao: e.target.value }))} />
          </div>
          {/* Foto de capa */}
          <div className="space-y-4">
            <label className={labelClass}>Foto</label>
            {fotoPreview && (
              <div className="relative w-48 h-48 mx-auto mb-4">
                <Image
                  src={fotoPreview}
                  alt="Preview da foto"
                  fill
                  className="object-cover rounded-lg"
                  sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                  priority
                />
              </div>
            )}
            <input
              type="file"
              accept="image/*"
              onChange={(e) => {
                const file = e.target.files?.[0];
                if (file) {
                  if (file.size > 5 * 1024 * 1024) {
                    setFotoMsg("A foto deve ter no máximo 5MB");
                    return;
                  }
                  setFotoFile(file);
                  setFotoMsg("");
                }
              }}
              className="hidden"
              id="foto"
            />
            <label
              htmlFor="foto"
              className={uploadButtonClass}
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              {fotoPreview ? "Alterar foto" : "Adicionar foto"}
            </label>
            {fotoMsg && <p className="text-center text-sm mt-2 text-[#4E3950]">{fotoMsg}</p>}
          </div>

          {/* Documentos */}
          <div className="space-y-4">
            <label className={labelClass}>Documentos do perfil</label>
            {documentosPreview.length > 0 && (
              <div className="grid grid-cols-2 gap-4 mb-4">
                {documentosPreview.map((doc, index) => (
                  <div key={index} className="relative">
                    {doc.type === "image" ? (
                      <div className="relative w-full h-40">
                        <Image
                          src={doc.url}
                          alt={`Documento ${index + 1}`}
                          fill
                          className="object-cover rounded-lg"
                          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                        />
                      </div>
                    ) : (
                      <div className="w-full h-40 bg-gray-100 rounded-lg flex items-center justify-center">
                        <span className="text-gray-500">{doc.name}</span>
                      </div>
                    )}
                    <button
                      onClick={() => {
                        setDocumentosFiles(files => files.filter((_, i) => i !== index));
                        setDocumentosPreview(prev => prev.filter((_, i) => i !== index));
                      }}
                      className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors"
                    >
                      ×
                    </button>
                  </div>
                ))}
              </div>
            )}
            <input
              type="file"
              accept="image/*,.pdf"
              onChange={(e) => {
                if (e.target.files?.length) {
                  setDocumentosFiles(prev => [...prev, ...Array.from(e.target.files || [])]);
                }
              }}
              className="hidden"
              id="documentos"
              multiple
            />
            <label
              htmlFor="documentos"
              className={uploadButtonClass}
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Adicionar documentos
            </label>
          </div>

          {/* Vídeo de verificação */}
          <div className="space-y-4">
            <label className={labelClass}>Vídeo de verificação</label>
            {videoPreview && (
              <div className="mb-4">
                <video
                  src={videoPreview}
                  controls
                  className="w-full max-w-md mx-auto rounded-lg"
                />
                <button
                  onClick={() => {
                    setVideoFile(null);
                    setVideoPreview("");
                  }}
                  className="mt-2 text-red-500 hover:text-red-700 transition-colors flex items-center gap-2 mx-auto"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Remover vídeo
                </button>
              </div>
            )}
            <input
              type="file"
              accept="video/*"
              onChange={(e) => {
                if (e.target.files?.length) {
                  setVideoFile(e.target.files[0]);
                }
              }}
              className="hidden"
              id="video"
            />
            <label
              htmlFor="video"
              className={uploadButtonClass}
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              {videoPreview ? "Alterar vídeo" : "Adicionar vídeo"}
            </label>
            <p className="text-sm text-[#CFB78B] mt-2">
              Instrução: Grave um vídeo segurando o documento RG ou CNH (com foto) ao lado do seu rosto,
              mostrando ambos claramente para a câmera.
            </p>
          </div>

          {/* Galeria de fotos */}
          <div className="space-y-4">
            <label className={labelClass}>Galeria de Fotos</label>
            {galeriaPreview.length > 0 && (
              <div className="grid grid-cols-3 gap-4 mb-4">
                {galeriaPreview.map((url, index) => (
                  <div key={index} className="relative">
                    <div className="relative w-full h-40">
                      <Image
                        src={url}
                        alt={`Foto ${index + 1}`}
                        fill
                        className="object-cover rounded-lg"
                        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                      />
                    </div>
                    <button
                      onClick={() => {
                        setGaleriaFiles(files => files.filter((_, i) => i !== index));
                        setGaleriaPreview(prev => prev.filter((_, i) => i !== index));
                      }}
                      className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors"
                    >
                      ×
                    </button>
                  </div>
                ))}
              </div>
            )}
            <input
              type="file"
              accept="image/*"
              onChange={(e) => {
                if (e.target.files?.length) {
                  setGaleriaFiles(prev => [...prev, ...Array.from(e.target.files || [])]);
                }
              }}
              className="hidden"
              id="galeria"
              multiple
            />
            <label
              htmlFor="galeria"
              className={uploadButtonClass}
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Adicionar fotos à galeria
            </label>
          </div>
          {/* Dados de acesso */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
            <div>
              <label className={labelClass}>E-mail *</label>
              <input type="email" name="email" className={inputClass} value={form.email} onChange={e => setForm(f => ({ ...f, email: e.target.value }))} required />
            </div>
            <div>
              <label className={labelClass}>Senha *</label>
              <input type="password" name="senha" className={inputClass} value={form.senha} onChange={e => setForm(f => ({ ...f, senha: e.target.value }))} required />
            </div>
          </div>
          <div className="flex justify-end">
            <button
              type="submit"
              disabled={loading || retryTimeLeft > 0}
              className={`px-4 py-2 text-white rounded ${
                loading || retryTimeLeft > 0 ? 'bg-gray-400' : 'bg-primary hover:bg-primary-dark'
              }`}
            >
              {loading ? (
                'Cadastrando...'
              ) : retryTimeLeft > 0 ? (
                `Aguarde ${retryTimeLeft}s`
              ) : (
                'Cadastrar'
              )}
            </button>
          </div>
          {msg && (
            <div className={`p-4 rounded ${msg.includes('sucesso') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
              {msg}
              {retryTimeLeft > 0 && (
                <div className="mt-2">
                  Você poderá tentar novamente em {retryTimeLeft} segundos.
                </div>
              )}
            </div>
          )}
        </form>
      </main>
      {/* Footer */}
      <footer className="w-full bg-white border-t border-[#CFB78B] py-4 px-6 text-center text-[#4E3950] text-sm mt-8">
        <div className="flex flex-col md:flex-row items-center justify-between gap-2 max-w-3xl mx-auto">
          <span>Sigilosas VIP &copy; {new Date().getFullYear()}</span>
          <span>Contato: <a href="mailto:contato@sigilosasvip.com" className="text-[#CA5272] underline">contato@sigilosasvip.com</a> | WhatsApp: <a href="https://wa.me/5599999999999" className="text-[#CA5272] underline" target="_blank">(99) 99999-9999</a></span>
        </div>
      </footer>
    </div>
  );
}

// Tailwind input style
// Add this to your global CSS if not present:
// .input { @apply w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-purple-400; } 