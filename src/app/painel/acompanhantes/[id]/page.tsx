"use client";
import { useEffect, useState, useRef } from "react";
import { useRouter, useParams } from "next/navigation";
import Image from "next/image";
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import type { Database } from '@/lib/database.types';
import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';

const inputClass = "w-full bg-white border border-[#CFB78B] rounded-lg px-4 py-2 text-[#4E3950] focus:outline-none focus:ring-2 focus:ring-[#CFB78B] text-base transition mb-0 placeholder-[#CFB78B]";
const labelClass = "block font-semibold text-[#4E3950] mb-1";
const checkboxClass = "accent-[#CFB78B] w-5 h-5 mr-2 align-middle";
const buttonClass = "w-full py-3 bg-[#4E3950] text-white border-none rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-[#CFB78B] hover:text-[#4E3950] disabled:opacity-50 disabled:cursor-not-allowed mt-2";
const uploadButtonClass = "flex items-center justify-center gap-2 w-full py-3 bg-white border-2 border-dashed border-[#CFB78B] rounded-lg font-medium text-[#4E3950] cursor-pointer transition-all hover:bg-[#fdf8ed] hover:border-[#b89a76] active:bg-[#f5e9d4] disabled:opacity-50 disabled:cursor-not-allowed";

export default function EditarAcompanhanteAdmin() {
  const supabase = createClientComponentClient<Database>();
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const [cidades, setCidades] = useState<any[]>([]);
  const [form, setForm] = useState<any>(null);
  const [fotoFile, setFotoFile] = useState<File | null>(null);
  const [fotoPreview, setFotoPreview] = useState<string>("");
  const [fotoMsg, setFotoMsg] = useState<string>("");
  const [loading, setLoading] = useState(true);
  const [msg, setMsg] = useState("");
  const [showGenitaliaOutro, setShowGenitaliaOutro] = useState(false);
  const [showPrefOutro, setShowPrefOutro] = useState(false);
  const [documentosPreview, setDocumentosPreview] = useState<any[]>([]);
  const [documentosFiles, setDocumentosFiles] = useState<File[]>([]);
  const [videoFile, setVideoFile] = useState<File | null>(null);
  const [videoPreview, setVideoPreview] = useState<string>("");
  const [galeriaFiles, setGaleriaFiles] = useState<File[]>([]);
  const [galeriaPreview, setGaleriaPreview] = useState<string[]>([]);
  const documentosRef = useRef<HTMLInputElement>(null);
  const videoRef = useRef<HTMLInputElement>(null);
  const galeriaFotosRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const fetchCidades = async () => {
      const { data, error } = await supabase.from("cidades").select("id, nome").order("nome");
      if (!error && data) setCidades(data);
    };
    fetchCidades();
  }, []);

  useEffect(() => {
    loadAcompanhante();
  }, []);

  const loadAcompanhante = async () => {
    try {
      const { data, error } = await supabase
        .from('acompanhantes')
        .select('*')
        .eq('id', id)
        .single();

      if (error) throw error;
      setForm(data);
      setShowGenitaliaOutro(data.genitalia === "Outro");
      setShowPrefOutro(data.preferencia_sexual === "Outro");
    } catch (error) {
      console.error('Erro ao carregar dados:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!fotoFile) { setFotoPreview(""); return; }
    const url = URL.createObjectURL(fotoFile);
    setFotoPreview(url);
    return () => URL.revokeObjectURL(url);
  }, [fotoFile]);

  useEffect(() => {
    if (!documentosFiles.length) { setDocumentosPreview([]); return; }
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
    return () => previews.forEach(p => p.type === "image" && URL.revokeObjectURL(p.url));
  }, [documentosFiles]);

  useEffect(() => {
    if (!videoFile) { setVideoPreview(""); return; }
    const url = URL.createObjectURL(videoFile);
    setVideoPreview(url);
    return () => URL.revokeObjectURL(url);
  }, [videoFile]);

  useEffect(() => {
    if (!galeriaFiles.length) { setGaleriaPreview([]); return; }
    const urls = galeriaFiles.map(file => URL.createObjectURL(file));
    setGaleriaPreview(urls);
    return () => urls.forEach(url => URL.revokeObjectURL(url));
  }, [galeriaFiles]);

  if (loading) return <div className="p-8 text-center">Carregando...</div>;

  if (!form) return <div className="p-8 text-center">Acompanhante não encontrada</div>;

  // Função para obter URL pública da foto de perfil
  const getFotoPerfilUrl = (fotoPath: string) => {
    if (!fotoPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('perfil')
      .getPublicUrl(fotoPath.split('/').pop() || '')
    return publicUrl
  }

  // Função para obter URL pública do documento
  const getDocumentoUrl = (docPath: string) => {
    if (!docPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('documentos')
      .getPublicUrl(docPath.split('/').pop() || '')
    return publicUrl
  }

  // Função para obter URL pública do vídeo
  const getVideoUrl = (videoPath: string) => {
    if (!videoPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('videos-verificacao')
      .getPublicUrl(videoPath.split('/').pop() || '')
    return publicUrl
  }

  // Função para obter URL pública da foto da galeria
  const getFotoGaleriaUrl = (fotoPath: string) => {
    if (!fotoPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('galeria')
      .getPublicUrl(fotoPath.split('/').pop() || '')
    return publicUrl
  }

  // Carregar fotos da galeria
  const loadGaleriaFotos = async () => {
    const { data: fotos, error } = await supabase
      .from('fotos_galeria')
      .select('*')
      .eq('acompanhante_id', id)
    
    if (error) {
      console.error('Erro ao carregar galeria:', error)
      return []
    }

    return fotos.map(foto => ({
      ...foto,
      url: getFotoGaleriaUrl(foto.url)
    }))
  }

  // Carregar vídeo de verificação
  const loadVideo = async () => {
    const { data: video, error } = await supabase
      .from('videos_verificacao')
      .select('*')
      .eq('acompanhante_id', id)
      .single()
    
    if (error) {
      console.error('Erro ao carregar vídeo:', error)
      return null
    }

    return {
      ...video,
      url: getVideoUrl(video.url)
    }
  }

  // Função para upload de foto
  const handleFotoUpload = async (file: File) => {
    setFotoMsg("Enviando...");
    const fileExt = file.name.split(".").pop();
    const fileName = `fotos/acompanhante_${id}_${Date.now()}.${fileExt}`;
    const { error } = await supabase.storage
      .from("media")
      .upload(fileName, file, { upsert: true });
    if (error) {
      setFotoMsg("Erro ao enviar foto.");
      console.error("Erro upload foto:", error);
      return null;
    }
    const { data: publicUrl } = supabase.storage
      .from("media")
      .getPublicUrl(fileName);
    setFotoMsg("Foto enviada com sucesso!");
    return publicUrl?.publicUrl || null;
  };

  // Upload de documentos
  const handleDocumentosUpload = async (file: File) => {
    const fileExt = file.name.split(".").pop();
    const fileName = `documentos/doc_${id}_${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    const { error } = await supabase.storage
      .from("media")
      .upload(fileName, file, { upsert: true });
    if (error) {
      console.error("Erro ao enviar documento:", error);
      return null;
    }
    const { data: publicUrl } = supabase.storage
      .from("media")
      .getPublicUrl(fileName);
    return publicUrl?.publicUrl || null;
  };

  // Upload de vídeo
  const handleVideoUpload = async (file: File) => {
    const fileExt = file.name.split(".").pop();
    const fileName = `videos/verificacao_${id}_${Date.now()}.${fileExt}`;
    const { error } = await supabase.storage
      .from("media")
      .upload(fileName, file, { upsert: true });
    if (error) {
      console.error("Erro ao enviar vídeo:", error);
      return null;
    }
    const { data: publicUrl } = supabase.storage
      .from("media")
      .getPublicUrl(fileName);
    return publicUrl?.publicUrl || null;
  };

  // Upload de fotos da galeria
  const handleGaleriaUpload = async (file: File) => {
    const fileExt = file.name.split(".").pop();
    const fileName = `galeria/foto_${id}_${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    const { error } = await supabase.storage
      .from("media")
      .upload(fileName, file, { upsert: true });
    if (error) {
      console.error("Erro ao enviar foto da galeria:", error);
      return null;
    }
    const { data: publicUrl } = supabase.storage
      .from("media")
      .getPublicUrl(fileName);
    return publicUrl?.publicUrl || null;
  };

  // Função para salvar alterações
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setMsg("");
    // Filtrar campos inválidos
    const payload = { ...form };
    // Remove senha from payload since it belongs to usuarios table
    const senha = payload.senha;
    delete payload.senha;
    
    // Garante que galeria_fotos, documentos e outros arrays sejam arrays de strings ou null
    if (Array.isArray(payload.galeria_fotos)) {
      payload.galeria_fotos = payload.galeria_fotos.filter((f: string) => typeof f === 'string' && f.startsWith('http'));
      if (payload.galeria_fotos.length === 0) payload.galeria_fotos = null;
    }
    if (Array.isArray(payload.documentos)) {
      payload.documentos = payload.documentos.filter((f: string) => typeof f === 'string' && f.length > 0);
      if (payload.documentos.length === 0) payload.documentos = null;
    }
    if (payload.video_verificacao && typeof payload.video_verificacao !== 'string') {
      payload.video_verificacao = null;
    }
    // Logar payload para debug
    console.log('Payload enviado:', payload);
    // Salvar alterações do acompanhante
    const { error } = await supabase.from('acompanhantes').update(payload).eq('id', id);
    if (!error) {
      // Se status for aprovado, criar usuário se não existir
      if (payload.status === 'aprovado' && payload.email && senha) {
        // Verifica se já existe usuário com esse e-mail
        const { data: existingUser, error: userError } = await supabase
          .from('usuarios')
          .select('id')
          .eq('email', payload.email)
          .single();
        if (!existingUser && !userError) {
          // Cria usuário
          const { error: createUserError } = await supabase.from('usuarios').insert({
            email: payload.email,
            senha: senha,
            tipo: 'acompanhante',
            acompanhante_id: id
          });
          if (createUserError) {
            setMsg('Acompanhante aprovado, mas erro ao criar usuário: ' + createUserError.message);
            setLoading(false);
            return;
          }
        }
      }
      setMsg("Alterações salvas com sucesso!");
      setTimeout(() => router.push("/painel/acompanhantes"), 1200);
    } else {
      setMsg("Erro ao salvar alterações: " + (error.message || JSON.stringify(error)));
      console.error('Erro ao salvar:', error);
    }
    setLoading(false);
  };

  const handleChange = (e: any) => {
    const { name, value, type, checked } = e.target;
    setForm((f: any) => ({ ...f, [name]: type === 'checkbox' ? checked : value }));
  };

  const handleSave = async () => {
    setMsg("");
    const payload = { status: form.status };
    console.log("Salvando:", payload);
    const { error } = await supabase
      .from("acompanhantes")
      .update(payload)
      .eq("id", id);
    if (error) {
      setMsg("Erro ao salvar!");
      console.log("Erro ao salvar:", error);
    } else {
      setMsg("Alterações salvas com sucesso!");
      setTimeout(() => router.push("/painel/acompanhantes"), 1000);
    }
  };

  // Função para upload de foto
  const handleUploadFoto = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) return;
    setFotoMsg("Enviando...");
    const file = e.target.files[0];
    const url = await handleFotoUpload(file);
    if (url) setForm((f: any) => ({ ...f, foto: url }));
    setFotoMsg("Foto enviada com sucesso!");
  };

  // Função para remover documento
  const handleRemoverDocumento = async (docUrl: string) => {
    if (!window.confirm('Tem certeza que deseja remover este documento?')) return;

    // Atualiza o estado
    setForm((f: any) => ({
      ...f,
      documentos: f.documentos.filter((d: string) => d !== docUrl)
    }));

    // Remove do storage
    const fileName = docUrl.split('/').pop();
    if (fileName) {
      await supabase.storage
        .from('media')
        .remove([`documentos/${fileName}`]);
    }
  };

  // Função para remover vídeo
  const handleRemoverVideo = async () => {
    if (!window.confirm('Tem certeza que deseja remover este vídeo?')) return;

    // Atualiza o estado
    setForm((f: any) => ({
      ...f,
      video_verificacao: null
    }));

    // Remove do storage
    const fileName = form.video_verificacao?.split('/').pop();
    if (fileName) {
      await supabase.storage
        .from('media')
        .remove([`videos/${fileName}`]);
    }
  };

  // Função para remover foto da galeria
  const handleRemoverFoto = async (fotoUrl: string) => {
    if (!window.confirm('Tem certeza que deseja remover esta foto?')) return;

    // Atualiza o estado
    setForm((f: any) => ({
      ...f,
      galeria_fotos: f.galeria_fotos.filter((foto: string) => foto !== fotoUrl)
    }));

    // Remove do storage
    const fileName = fotoUrl.split('/').pop();
    if (fileName) {
      await supabase.storage
        .from('media')
        .remove([`galeria/${fileName}`]);
    }
  };

  // Função para excluir acompanhante e todos seus arquivos
  const handleDelete = async () => {
    if (!confirm('Tem certeza que deseja excluir este cadastro? Esta ação não pode ser desfeita.')) {
      return;
    }

    try {
      setLoading(true);
      // Excluir arquivos do storage
      await supabase.storage.from('perfil').remove([`${id}/*`]);
      await supabase.storage.from('documentos').remove([`${id}/*`]);
      await supabase.storage.from('videos-verificacao').remove([`${id}/*`]);
      await supabase.storage.from('galeria').remove([`${id}/*`]);

      // Excluir registros das tabelas
      await supabase.from('fotos_galeria').delete().eq('acompanhante_id', id);
      await supabase.from('documentos').delete().eq('acompanhante_id', id);
      await supabase.from('videos_verificacao').delete().eq('acompanhante_id', id);
      await supabase.from('acompanhantes').delete().eq('id', id);

      router.push('/painel/acompanhantes');
    } catch (error) {
      console.error('Erro ao excluir:', error);
      alert('Erro ao excluir o cadastro. Por favor, tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  // Função para atualizar arquivos
  const handleFileUpdate = async (bucket: string, file: File) => {
    const fileExt = file.name.split('.').pop();
    const fileName = `${id}/${Date.now()}.${fileExt}`;
    
    const { error } = await supabase.storage
      .from(bucket)
      .upload(fileName, file, { upsert: true });
      
    if (error) {
      console.error(`Erro ao atualizar arquivo no bucket ${bucket}:`, error);
      return null;
    }

    const { data: { publicUrl } } = supabase.storage
      .from(bucket)
      .getPublicUrl(fileName);

    return publicUrl;
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#f6f3f9]">
      <main className="flex-1 flex items-center justify-center">
        <form className="w-full max-w-3xl bg-white p-8 rounded-2xl shadow-xl space-y-7 border border-[#CFB78B]" onSubmit={handleSubmit} autoComplete="off">
          <h2 className="text-3xl font-bold mb-2 text-center text-[#4E3950]">Editar Cadastro de Acompanhante</h2>
          <p className="text-center text-[#4E3950] text-lg mb-6">
            Edite os dados do cadastro abaixo. Após salvar, as alterações serão aplicadas imediatamente.
          </p>
          <div className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label className={labelClass}>Nome do acompanhante *</label>
                <input type="text" name="nome" className={inputClass} value={form.nome} onChange={e => setForm((f: any) => ({ ...f, nome: e.target.value }))} required />
              </div>
              <div>
                <label className={labelClass}>Idade *</label>
                <input type="number" name="idade" className={inputClass} value={form.idade} onChange={e => setForm((f: any) => ({ ...f, idade: e.target.value }))} min={18} required />
              </div>
              <div>
                <label className={labelClass}>Gênero *</label>
                <select name="genero" className={inputClass} value={form.genero} onChange={e => setForm((f: any) => ({ ...f, genero: e.target.value }))} required>
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
                    setForm((f: any) => ({ ...f, genitalia: e.target.value }));
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
                    onChange={e => setForm((f: any) => ({ ...f, genitalia_outro: e.target.value }))}
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
                    setForm((f: any) => ({ ...f, preferencia_sexual: e.target.value }));
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
                    onChange={e => setForm((f: any) => ({ ...f, preferencia_sexual_outro: e.target.value }))}
                  />
                )}
              </div>
              <div>
                <label className={labelClass}>Peso (kg)</label>
                <input type="text" name="peso" className={inputClass} value={form.peso} onChange={e => setForm((f: any) => ({ ...f, peso: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Altura (m)</label>
                <input type="text" name="altura" className={inputClass} value={form.altura} onChange={e => setForm((f: any) => ({ ...f, altura: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Etnia</label>
                <select
                  name="etnia"
                  className={inputClass}
                  value={form.etnia}
                  onChange={e => setForm((f: any) => ({ ...f, etnia: e.target.value }))}
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
                <input type="text" name="cor_olhos" className={inputClass} value={form.cor_olhos} onChange={e => setForm((f: any) => ({ ...f, cor_olhos: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Estilo de cabelo</label>
                <input type="text" name="estilo_cabelo" className={inputClass} value={form.estilo_cabelo} onChange={e => setForm((f: any) => ({ ...f, estilo_cabelo: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Tamanho do cabelo</label>
                <input type="text" name="tamanho_cabelo" className={inputClass} value={form.tamanho_cabelo} onChange={e => setForm((f: any) => ({ ...f, tamanho_cabelo: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Tamanho do pé</label>
                <input type="text" name="tamanho_pe" className={inputClass} value={form.tamanho_pe} onChange={e => setForm((f: any) => ({ ...f, tamanho_pe: e.target.value }))} />
              </div>
            </div>
            <div className="flex gap-8 items-center flex-wrap mb-2">
              <label className={labelClass}><input type="checkbox" checked={form.silicone} onChange={e => setForm((f: any) => ({ ...f, silicone: e.target.checked }))} className={checkboxClass} />Silicone</label>
              <label className={labelClass}><input type="checkbox" checked={form.tatuagens} onChange={e => setForm((f: any) => ({ ...f, tatuagens: e.target.checked }))} className={checkboxClass} />Tatuagens</label>
              <label className={labelClass}><input type="checkbox" checked={form.piercings} onChange={e => setForm((f: any) => ({ ...f, piercings: e.target.checked }))} className={checkboxClass} />Piercings</label>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label className={labelClass}>Fumante</label>
                <input type="text" name="fumante" className={inputClass} value={form.fumante} onChange={e => setForm((f: any) => ({ ...f, fumante: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Idiomas</label>
                <input type="text" name="idiomas" className={inputClass} value={form.idiomas} onChange={e => setForm((f: any) => ({ ...f, idiomas: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Endereço</label>
                <input type="text" name="endereco" className={inputClass} value={form.endereco} onChange={e => setForm((f: any) => ({ ...f, endereco: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Telefone</label>
                <input name="telefone" value={form.telefone || ''} onChange={handleChange} className={inputClass} />
              </div>
              <div>
                <label className={labelClass}>Cidade *</label>
                <select name="cidade_id" className={inputClass} value={form.cidade_id} onChange={e => setForm((f: any) => ({ ...f, cidade_id: e.target.value }))} required>
                  <option value="">Selecione</option>
                  {cidades.map(c => (
                    <option key={c.id} value={c.id}>{c.nome}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className={labelClass}>Clientes em conjunto</label>
                <input type="number" name="clientes_conjunto" className={inputClass} value={form.clientes_conjunto} onChange={e => setForm((f: any) => ({ ...f, clientes_conjunto: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Atende</label>
                <input type="text" name="atende" className={inputClass} value={form.atende} onChange={e => setForm((f: any) => ({ ...f, atende: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Horário de expediente</label>
                <input type="text" name="horario_expediente" className={inputClass} value={form.horario_expediente} onChange={e => setForm((f: any) => ({ ...f, horario_expediente: e.target.value }))} />
              </div>
              <div>
                <label className={labelClass}>Formas de pagamento</label>
                <input type="text" name="formas_pagamento" className={inputClass} value={form.formas_pagamento} onChange={e => setForm((f: any) => ({ ...f, formas_pagamento: e.target.value }))} />
              </div>
            </div>
            <div>
              <label className={labelClass}>Descrição</label>
              <textarea name="descricao" className={inputClass} rows={4} value={form.descricao} onChange={e => setForm((f: any) => ({ ...f, descricao: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>E-mail</label>
              <input name="email" value={form.email || ''} onChange={handleChange} className="input-field" />
            </div>
            <div>
              <label className={labelClass}>Senha</label>
              <input name="senha" type="text" value={form.senha || ''} onChange={handleChange} className="input-field" />
            </div>
            {/* Foto */}
            <div className="space-y-4">
              <label className={labelClass}>Foto</label>
              {form.foto && (
                <div className="relative w-48 h-48 mx-auto mb-4">
                  <Image
                    src={getFotoPerfilUrl(form.foto)}
                    alt="Foto do perfil"
                    fill
                    className="object-cover rounded-lg"
                  />
                </div>
              )}
              <input
                type="file"
                accept="image/*"
                onChange={handleUploadFoto}
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
                {form.foto ? "Alterar foto" : "Adicionar foto"}
              </label>
              {fotoMsg && <p className="text-center text-sm mt-2 text-[#4E3950]">{fotoMsg}</p>}
            </div>

            {/* Documentos */}
            <div className="space-y-4">
              <label className={labelClass}>Documentos do perfil</label>
              {form.documentos && form.documentos.length > 0 && (
                <div className="grid grid-cols-2 gap-4 mb-4">
                  {form.documentos.map((doc: string, index: number) => (
                    <div key={index} className="relative">
                      <Image
                        src={getDocumentoUrl(doc)}
                        alt={`Documento ${index + 1}`}
                        width={200}
                        height={150}
                        className="object-cover rounded-lg"
                      />
                      <button
                        onClick={() => handleRemoverDocumento(doc)}
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
                    setDocumentosFiles(Array.from(e.target.files));
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
              {form.video_verificacao && (
                <div className="mb-4">
                  <video
                    src={loadVideo()?.url}
                    controls
                    className="w-full max-w-md mx-auto rounded-lg"
                  />
                  <button
                    onClick={() => handleRemoverVideo()}
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
                {form.video_verificacao ? "Alterar vídeo" : "Adicionar vídeo"}
              </label>
            </div>

            {/* Galeria de fotos */}
            <div className="space-y-4">
              <label className={labelClass}>Galeria de Fotos</label>
              {form.galeria_fotos && form.galeria_fotos.length > 0 && (
                <div className="grid grid-cols-3 gap-4 mb-4">
                  {form.galeria_fotos.map((foto: string, index: number) => (
                    <div key={index} className="relative">
                      <Image
                        src={getFotoGaleriaUrl(foto)}
                        alt={`Foto ${index + 1}`}
                        width={200}
                        height={150}
                        className="object-cover rounded-lg"
                      />
                      <button
                        onClick={() => handleRemoverFoto(foto)}
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
                    setGaleriaFiles(Array.from(e.target.files));
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
            <div>
              <label className={labelClass}>Status</label>
              <select name="status" value={form.status || ''} onChange={handleChange} className={inputClass}>
                <option value="pendente">Pendente</option>
                <option value="aprovado">Aprovado</option>
                <option value="rejeitado">Rejeitado</option>
              </select>
            </div>
            <div className="flex gap-4">
              <button type="submit" className={buttonClass} disabled={loading}>
                {loading ? "Salvando..." : "Salvar Alterações"}
              </button>
              
              <button
                type="button"
                onClick={handleDelete}
                className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                disabled={loading}
              >
                {loading ? "Excluindo..." : "Excluir Acompanhante"}
              </button>
            </div>
            <button
              type="button"
              className="w-full py-3 mt-2 bg-gray-300 text-[#4E3950] border-none rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-gray-400 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed"
              onClick={() => router.push('/painel/acompanhantes')}
            >
              Cancelar / Sair
            </button>
            {msg && <div className="mt-2">{msg}</div>}
          </div>
        </form>
      </main>
    </div>
  );
} 