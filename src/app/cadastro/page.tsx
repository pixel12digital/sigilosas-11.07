"use client";
import { useState, useEffect, useRef } from "react";
import Image from "next/image";
import { createClientComponentClient } from "@supabase/auth-helpers-nextjs";
import Link from "next/link";
import { useRouter } from "next/navigation";

interface Cidade {
  id: number;
  nome: string;
}

// Estilo customizado para inputs
const inputClass = "w-full bg-white border border-[#CFB78B] rounded-lg px-4 py-2 text-[#4E3950] focus:outline-none focus:ring-2 focus:ring-[#CFB78B] text-base transition mb-0 placeholder-[#CFB78B]";
const labelClass = "block font-semibold text-[#4E3950] mb-1";
const checkboxClass = "accent-[#CFB78B] w-5 h-5 mr-2 align-middle";
const buttonClass = "w-full py-3 bg-[#4E3950] text-white border-none rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-[#CFB78B] hover:text-[#4E3950] disabled:opacity-50 disabled:cursor-not-allowed mt-2";

export default function CadastroAcompanhante() {
  const [cidades, setCidades] = useState<Cidade[]>([]);
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
    clientes_conjunto: "",
    atende: "",
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

  // Referências para arquivos
  const documentosRef = useRef<HTMLInputElement>(null);
  const videoRef = useRef<HTMLInputElement>(null);
  const galeriaFotosRef = useRef<HTMLInputElement>(null);

  const router = useRouter();

  // Buscar cidades do Supabase
  useEffect(() => {
    const fetchCidades = async () => {
      const supabase = createClientComponentClient();
      const { data, error } = await supabase
        .from("cidades")
        .select("id, nome")
        .order("nome");
      if (!error && data) setCidades(data);
    };
    fetchCidades();
  }, []);

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
    const fileName = `acompanhante_${Date.now()}.${fileExt}`;
    const { data, error } = await supabase.storage
      .from("fotos")
      .upload(fileName, file, { upsert: false });
    if (error) {
      setFotoMsg("Erro ao enviar foto.");
      return null;
    }
    const { data: publicUrl } = supabase.storage
      .from("fotos")
      .getPublicUrl(fileName);
    setFotoMsg("Foto enviada com sucesso!");
    return publicUrl?.publicUrl || null;
  };

  // Atualiza previews ao selecionar arquivos
  useEffect(() => {
    if (!documentosFiles.length) {
      setDocumentosPreview([]);
      return;
    }
    const previews = documentosFiles.map(file => {
      if (file.type.startsWith("image/")) {
        return { url: URL.createObjectURL(file), type: "image", name: file.name };
      } else if (file.type === "application/pdf") {
        return { url: "/assets/img/pdf-icon.png", type: "pdf", name: file.name };
      } else {
        return { url: "", type: "other", name: file.name };
      }
    });
    setDocumentosPreview(previews);
    // Cleanup URLs
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

  // Handle form submit
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setMsg("");
    let fotoUrl = form.foto;
    if (fotoFile) {
      const url = await handleFotoUpload(fotoFile);
      if (url) fotoUrl = url;
    }
    // Envia dados para API Route
    const res = await fetch("/api/cadastro", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ...form, foto: fotoUrl }),
    });
    const data = await res.json();
    if (data.sucesso) {
      setForm({
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
        clientes_conjunto: "",
        atende: "",
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
      setFotoFile(null);
      setFotoPreview("");
      router.push("/obrigado");
      return;
    } else {
      setMsg(data.erro || "Erro ao cadastrar.");
    }
    setLoading(false);
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#f6f3f9]">
      {/* Header */}
      <header className="w-full flex items-center justify-between px-6 py-4 bg-white border-b border-[#e2c98f] shadow-sm">
        <div className="flex items-center gap-3">
          <img src="/assets/img/logo.png" alt="SigilosasVip - Logo" className="h-10 w-auto" />
        </div>
        <Link href="/" className="text-[#CA5272] font-semibold px-4 py-2 rounded-lg border border-[#CA5272] hover:bg-[#CA5272] hover:text-white transition">Voltar para o site</Link>
      </header>
      <main className="flex-1 flex items-center justify-center">
        <form className="w-full max-w-3xl bg-white p-8 rounded-2xl shadow-xl space-y-7 border border-[#e2c98f]" onSubmit={handleSubmit} autoComplete="off">
          <h2 className="text-3xl font-bold mb-2 text-center text-[#2E1530]">Cadastro de Acompanhante</h2>
          <p className="text-center text-[#CA5272] text-lg mb-6">
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
              <label className={labelClass}>Cidade *</label>
              <select name="cidade_id" className={inputClass} value={form.cidade_id} onChange={e => setForm(f => ({ ...f, cidade_id: e.target.value }))} required>
                <option value="">Selecione</option>
                {cidades.map(c => (
                  <option key={c.id} value={c.id}>{c.nome}</option>
                ))}
              </select>
            </div>
            <div>
              <label className={labelClass}>Clientes em conjunto</label>
              <input type="number" name="clientes_conjunto" className={inputClass} value={form.clientes_conjunto} onChange={e => setForm(f => ({ ...f, clientes_conjunto: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>Atende</label>
              <input type="text" name="atende" className={inputClass} value={form.atende} onChange={e => setForm(f => ({ ...f, atende: e.target.value }))} />
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
          <div>
            <label className={labelClass}>URL da foto</label>
            <input type="text" name="foto" className={inputClass} value={form.foto} onChange={e => setForm(f => ({ ...f, foto: e.target.value }))} />
            <div className="mt-2">
              <label className={labelClass}>Ou envie uma foto:</label>
              <input type="file" accept="image/*" onChange={e => { const file = e.target.files?.[0]; if (file) setFotoFile(file); }} className={inputClass} />
              {fotoPreview && <div className="mt-2"><Image src={fotoPreview} alt="Preview" width={120} height={120} className="rounded-lg object-cover" /></div>}
              {fotoMsg && <div className="text-sm mt-1 text-purple-700">{fotoMsg}</div>}
            </div>
          </div>
          {/* Documentos do perfil */}
          <div className="border rounded-lg p-4 mt-4 bg-[#fdf8ed] border-[#e2c98f]">
            <label className={labelClass}>Documentos do perfil</label>
            <input
              type="file"
              multiple
              accept=".pdf,.jpg,.jpeg,.png"
              className={inputClass}
              onChange={e => {
                const files = Array.from(e.target.files || []);
                setDocumentosFiles(prev => [...prev, ...files]);
              }}
            />
            <div className="flex gap-4 mt-3 flex-wrap">
              {documentosPreview.map((doc, idx) => (
                <div key={idx} className="relative group">
                  {doc.type === "image" ? (
                    <img src={doc.url} alt={doc.name} className="w-20 h-20 object-cover rounded shadow border border-[#e2c98f]" />
                  ) : doc.type === "pdf" ? (
                    <img src="/assets/img/pdf-icon.png" alt="PDF" className="w-20 h-20 object-contain rounded shadow border border-[#e2c98f] bg-white" />
                  ) : (
                    <span className="block w-20 h-20 bg-gray-200 rounded flex items-center justify-center text-xs">Arquivo</span>
                  )}
                  <button
                    type="button"
                    className="absolute -top-2 -right-2 bg-[#CA5272] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow hover:bg-[#a03a5a]"
                    onClick={() => {
                      setDocumentosFiles(files => files.filter((_, i) => i !== idx));
                    }}
                    title="Remover"
                  >×</button>
                  <div className="text-xs text-[#2E1530] mt-1 text-center w-20 truncate">{doc.name}</div>
                </div>
              ))}
            </div>
          </div>
          {/* Vídeo de verificação */}
          <div className="border rounded-lg p-4 mt-4 bg-[#fdf8ed] border-[#e2c98f]">
            <label className={labelClass}>Vídeo de verificação</label>
            <input
              type="file"
              accept="video/*"
              className={inputClass}
              onChange={e => {
                const file = e.target.files?.[0];
                if (file) setVideoFile(file);
              }}
            />
            {videoPreview && (
              <div className="mt-2 relative group">
                <video src={videoPreview} controls className="w-32 h-32 rounded shadow border border-[#e2c98f] object-cover" />
                <button
                  type="button"
                  className="absolute -top-2 -right-2 bg-[#CA5272] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow hover:bg-[#a03a5a]"
                  onClick={() => setVideoFile(null)}
                  title="Remover vídeo"
                >×</button>
              </div>
            )}
            <div className="text-sm text-[#b89a76] mt-2">Instrução: Grave um vídeo segurando o documento RG ou CNH (com foto) ao lado do seu rosto, mostrando ambos claramente para a câmera.</div>
          </div>
          {/* Galeria de Fotos */}
          <div className="border rounded-lg p-4 mt-4 bg-[#fdf8ed] border-[#e2c98f]">
            <label className={labelClass}>Galeria de Fotos</label>
            <input
              type="file"
              multiple
              accept="image/*"
              className={inputClass}
              onChange={e => {
                const files = Array.from(e.target.files || []);
                setGaleriaFiles(prev => [...prev, ...files]);
              }}
            />
            <div className="flex gap-4 mt-3 flex-wrap">
              {galeriaPreview.map((url, idx) => (
                <div key={idx} className="relative group">
                  <img src={url} alt={`Foto ${idx + 1}`} className="w-20 h-20 object-cover rounded shadow border border-[#e2c98f]" />
                  <button
                    type="button"
                    className="absolute -top-2 -right-2 bg-[#CA5272] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow hover:bg-[#a03a5a]"
                    onClick={() => setGaleriaFiles(files => files.filter((_, i) => i !== idx))}
                    title="Remover"
                  >×</button>
                </div>
              ))}
            </div>
          </div>
          {/* Dados de acesso */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
            <div className="col-span-2 mb-2">
              <p className="text-[#CA5272] text-base font-medium mb-2">O e-mail e a senha informados abaixo serão utilizados para acessar o painel após a aprovação do seu cadastro.</p>
            </div>
            <div>
              <label className={labelClass}>E-mail *</label>
              <input type="email" name="email" className={inputClass} value={form.email} onChange={e => setForm(f => ({ ...f, email: e.target.value }))} required />
            </div>
            <div>
              <label className={labelClass}>Telefone *</label>
              <input type="tel" name="telefone" className={inputClass} value={form.telefone} onChange={e => setForm(f => ({ ...f, telefone: e.target.value }))} required />
            </div>
            <div>
              <label className={labelClass}>Senha *</label>
              <input type="password" name="senha" className={inputClass} value={form.senha} onChange={e => setForm(f => ({ ...f, senha: e.target.value }))} required />
            </div>
          </div>
          <button type="submit" className={buttonClass} disabled={loading}>
            {loading ? "Cadastrando..." : "Cadastrar"}
          </button>
          {msg && (
            <p className={`text-center mb-4 ${msg.startsWith("Cadastro") ? "text-green-700" : "text-red-600"}`}>{msg}</p>
          )}
        </form>
      </main>
      {/* Footer */}
      <footer className="w-full bg-white border-t border-[#e2c98f] py-4 px-6 text-center text-[#2E1530] text-sm mt-8">
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