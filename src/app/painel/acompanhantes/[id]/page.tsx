"use client";
import { useEffect, useState, useRef } from "react";
import { useParams, useRouter } from "next/navigation";
import { supabase } from "@/lib/supabase";
import Image from "next/image";

const inputClass = "w-full bg-white border border-[#CFB78B] rounded-lg px-4 py-2 text-[#4E3950] focus:outline-none focus:ring-2 focus:ring-[#CFB78B] text-base transition mb-0 placeholder-[#CFB78B]";
const labelClass = "block font-semibold text-[#4E3950] mb-1";
const checkboxClass = "accent-[#CFB78B] w-5 h-5 mr-2 align-middle";
const buttonClass = "w-full py-3 bg-[#4E3950] text-white border-none rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-[#CFB78B] hover:text-[#4E3950] disabled:opacity-50 disabled:cursor-not-allowed mt-2";

export default function EditarAcompanhanteAdmin() {
  const { id } = useParams();
  const router = useRouter();
  const [cidades, setCidades] = useState<any[]>([]);
  const [form, setForm] = useState<any>(null);
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
    const fetchAcompanhante = async () => {
      const { data, error } = await supabase.from("acompanhantes").select("*").eq("id", id).single();
      if (data) {
        setForm({ ...data, senha: "" });
        setShowGenitaliaOutro(data.genitalia === "Outro");
        setShowPrefOutro(data.preferencia_sexual === "Outro");
      }
    };
    fetchAcompanhante();
  }, [id]);

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

  if (!form) return <div className="p-8 text-center">Carregando...</div>;

  // Função para upload de foto (igual cadastro)
  const handleFotoUpload = async (file: File) => {
    setFotoMsg("Enviando...");
    const fileExt = file.name.split(".").pop();
    const fileName = `acompanhante_${id}_${Date.now()}.${fileExt}`;
    const { error } = await supabase.storage.from("fotos").upload(fileName, file, { upsert: false });
    if (error) { setFotoMsg("Erro ao enviar foto."); return null; }
    const { data: publicUrl } = supabase.storage.from("fotos").getPublicUrl(fileName);
    setFotoMsg("Foto enviada com sucesso!");
    return publicUrl?.publicUrl || null;
  };

  // Função para salvar alterações
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setMsg("");
    // Filtrar campos inválidos
    const payload = { ...form };
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
      if (payload.status === 'aprovado' && payload.email && payload.senha) {
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
            senha: payload.senha,
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
    setForm({ ...form, [name]: type === 'checkbox' ? checked : value });
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
    if (url) setForm({ ...form, foto: url });
    setFotoMsg("Foto enviada com sucesso!");
  };

  // Função para remover foto
  const handleRemoverFoto = async (fotoUrl: string) => {
    if (!window.confirm('Remover esta foto?')) return;
    // Remove do array
    const galeria = form.galeria_fotos.filter((f: string) => f !== fotoUrl);
    const { error } = await supabase.from('acompanhantes').update({ galeria_fotos: galeria }).eq('id', id);
    if (!error) setForm({ ...form, galeria_fotos: galeria });
    // Opcional: remover do storage (não obrigatório, mas recomendado)
    const path = fotoUrl.split('/acompanhantes/')[1];
    if (path) await supabase.storage.from('acompanhantes').remove([path]);
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
            <div className="flex gap-8 items-center flex-wrap mb-2">
              <label className={labelClass}><input type="checkbox" checked={form.silicone} onChange={e => setForm(f => ({ ...f, silicone: e.target.checked }))} className={checkboxClass} />Silicone</label>
              <label className={labelClass}><input type="checkbox" checked={form.tatuagens} onChange={e => setForm(f => ({ ...f, tatuagens: e.target.checked }))} className={checkboxClass} />Tatuagens</label>
              <label className={labelClass}><input type="checkbox" checked={form.piercings} onChange={e => setForm(f => ({ ...f, piercings: e.target.checked }))} className={checkboxClass} />Piercings</label>
            </div>
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
            </div>
            <div>
              <label className={labelClass}>Descrição</label>
              <textarea name="descricao" className={inputClass} rows={4} value={form.descricao} onChange={e => setForm(f => ({ ...f, descricao: e.target.value }))} />
            </div>
            <div>
              <label className={labelClass}>E-mail</label>
              <input name="email" value={form.email || ''} onChange={handleChange} className="input-field" />
            </div>
            <div>
              <label className={labelClass}>Senha</label>
              <input name="senha" type="text" value={form.senha || ''} onChange={handleChange} className="input-field" />
            </div>
            <div>
              <label className={labelClass}>Telefone</label>
              <input name="telefone" value={form.telefone || ''} onChange={handleChange} className="input-field" />
            </div>
            {/* Arquivos enviados */}
            <div>
              <label className={labelClass}>Foto</label>
              {form.foto && <img src={form.foto} alt="Foto" className="h-32 w-32 object-cover rounded" />}
            </div>
            <div className="border rounded-lg p-4 mt-4 bg-[#fdf8ed] border-[#CFB78B]">
              <label className={labelClass}>Documentos do perfil</label>
              <input
                type="file"
                multiple
                accept=".pdf,.jpg,.jpeg,.png"
                className={inputClass}
                onChange={async e => {
                  const files = Array.from(e.target.files || []);
                  let uploadedUrls = [];
                  for (const file of files) {
                    const ext = file.name.split('.').pop();
                    const path = `documentos/${id}_${Date.now()}_${file.name}`;
                    await supabase.storage.from('fotos').upload(path, file);
                    const { data: { publicUrl } } = supabase.storage.from('fotos').getPublicUrl(path);
                    uploadedUrls.push(publicUrl);
                  }
                  const novosDocs = Array.isArray(form.documentos) ? [...form.documentos, ...uploadedUrls] : uploadedUrls;
                  await supabase.from('acompanhantes').update({ documentos: novosDocs }).eq('id', id);
                  setForm(f => ({ ...f, documentos: novosDocs }));
                }}
              />
              <div className="flex gap-4 mt-3 flex-wrap">
                {(!form.documentos || form.documentos.length === 0) && <span>Nenhum documento enviado</span>}
                {form.documentos && form.documentos.map((doc: string, idx: number) => (
                  <div key={idx} className="relative group">
                    {doc.endsWith('.pdf') ? (
                      <img src="/assets/img/pdf-icon.png" alt="PDF" className="w-20 h-20 object-contain rounded shadow border border-[#e2c98f] bg-white" />
                    ) : (
                      <img src={doc} alt={doc} className="w-20 h-20 object-cover rounded shadow border border-[#e2c98f]" />
                    )}
                    <button
                      type="button"
                      className="absolute -top-2 -right-2 bg-[#CA5272] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow hover:bg-[#a03a5a]"
                      onClick={async () => {
                        const novosDocs = form.documentos.filter((_: any, i: number) => i !== idx);
                        await supabase.from('acompanhantes').update({ documentos: novosDocs }).eq('id', id);
                        setForm(f => ({ ...f, documentos: novosDocs }));
                      }}
                      title="Remover"
                    >×</button>
                  </div>
                ))}
              </div>
            </div>
            <div className="border rounded-lg p-4 mt-4 bg-[#fdf8ed] border-[#CFB78B]">
              <label className={labelClass}>Vídeo de verificação</label>
              <input
                type="file"
                accept="video/*"
                className={inputClass}
                onChange={async e => {
                  const file = e.target.files?.[0];
                  if (file) {
                    const ext = file.name.split('.').pop();
                    const path = `videos/${id}_${Date.now()}_${file.name}`;
                    await supabase.storage.from('fotos').upload(path, file);
                    const { data: { publicUrl } } = supabase.storage.from('fotos').getPublicUrl(path);
                    await supabase.from('acompanhantes').update({ video_verificacao: publicUrl }).eq('id', id);
                    setForm(f => ({ ...f, video_verificacao: publicUrl }));
                  }
                }}
              />
              <div className="mt-2 relative group">
                {!form.video_verificacao && <span>Nenhuma foto enviada</span>}
                {form.video_verificacao && (
                  <>
                    <video src={form.video_verificacao} controls className="w-32 h-32 rounded shadow border border-[#e2c98f] object-cover" />
                    <button
                      type="button"
                      className="absolute -top-2 -right-2 bg-[#CA5272] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow hover:bg-[#a03a5a]"
                      onClick={async () => {
                        await supabase.from('acompanhantes').update({ video_verificacao: null }).eq('id', id);
                        setForm(f => ({ ...f, video_verificacao: null }));
                      }}
                      title="Remover vídeo"
                    >×</button>
                  </>
                )}
              </div>
              <div className="text-sm text-[#b89a76] mt-2">Instrução: Grave um vídeo segurando o documento RG ou CNH (com foto) ao lado do seu rosto, mostrando ambos claramente para a câmera.</div>
            </div>
            <div className="border rounded-lg p-4 mt-4 bg-[#fdf8ed] border-[#CFB78B]">
              <label className={labelClass}>Galeria de Fotos</label>
              <input
                type="file"
                multiple
                accept="image/*"
                className={inputClass}
                onChange={async e => {
                  const files = Array.from(e.target.files || []);
                  let uploadedUrls = [];
                  for (const file of files) {
                    const ext = file.name.split('.').pop();
                    const path = `galeria/${id}_${Date.now()}_${file.name}`;
                    const { error: uploadError } = await supabase.storage.from('fotos').upload(path, file);
                    if (!uploadError) {
                      const { data } = supabase.storage.from('fotos').getPublicUrl(path);
                      if (data && data.publicUrl) uploadedUrls.push(data.publicUrl);
                    } else {
                      setMsg('Erro ao enviar imagem: ' + uploadError.message);
                    }
                  }
                  // Só atualiza se houver URLs válidas
                  if (uploadedUrls.length > 0) {
                    const novaGaleria = Array.isArray(form.galeria_fotos) ? [...form.galeria_fotos.filter(Boolean), ...uploadedUrls] : uploadedUrls;
                    const { error } = await supabase.from('acompanhantes').update({ galeria_fotos: novaGaleria }).eq('id', id);
                    if (error) setMsg('Erro ao salvar galeria: ' + error.message);
                    else setForm(f => ({ ...f, galeria_fotos: novaGaleria }));
                  }
                }}
              />
              <div className="flex gap-4 mt-3 flex-wrap">
                {(!form.galeria_fotos || form.galeria_fotos.length === 0) && <span>Nenhuma foto enviada</span>}
                {form.galeria_fotos && form.galeria_fotos.filter(Boolean).map((foto: string, idx: number) => (
                  <div key={idx} className="relative group">
                    <img
                      src={foto && foto.startsWith('http') ? foto : '/assets/img/placeholder.jpg'}
                      alt={`Foto ${idx + 1}`}
                      className="w-20 h-20 object-cover rounded shadow border border-[#e2c98f]"
                      onError={e => { (e.target as HTMLImageElement).src = '/assets/img/placeholder.jpg'; }}
                    />
                    <button
                      type="button"
                      className="absolute -top-2 -right-2 bg-[#CA5272] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow hover:bg-[#a03a5a]"
                      onClick={async () => {
                        const novaGaleria = form.galeria_fotos.filter((_: any, i: number) => i !== idx && Boolean(_));
                        const { error } = await supabase.from('acompanhantes').update({ galeria_fotos: novaGaleria.length > 0 ? novaGaleria : null }).eq('id', id);
                        if (error) setMsg('Erro ao remover foto: ' + error.message);
                        else setForm(f => ({ ...f, galeria_fotos: novaGaleria }));
                      }}
                      title="Remover"
                    >×</button>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <label className={labelClass}>Status</label>
              <select name="status" value={form.status || ''} onChange={handleChange} className={inputClass}>
                <option value="pendente">Pendente</option>
                <option value="aprovado">Aprovado</option>
                <option value="rejeitado">Rejeitado</option>
              </select>
            </div>
            <button type="submit" className={buttonClass} disabled={loading}>
              {loading ? "Salvando..." : "Salvar Alterações"}
            </button>
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