"use client";
import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { supabase } from "@/lib/supabase";

export default function RevisarAcompanhante() {
  const { id } = useParams();
  const router = useRouter();
  const [form, setForm] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [msg, setMsg] = useState("");

  useEffect(() => {
    const fetchAcompanhante = async () => {
      const { data, error } = await supabase
        .from("acompanhantes")
        .select("*")
        .eq("id", id)
        .single();
      setForm(data);
      setLoading(false);
    };
    fetchAcompanhante();
  }, [id]);

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

  if (loading) return <div>Carregando...</div>;
  if (!form) return <div>Cadastro não encontrado.</div>;

  return (
    <div className="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8 mt-8">
      <h1 className="text-2xl font-bold mb-4">Revisar Cadastro</h1>
      <div className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label className="block font-semibold">Nome</label>
            <input name="nome" value={form.nome || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Idade</label>
            <input name="idade" value={form.idade || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Gênero</label>
            <input name="genero" value={form.genero || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Genitália</label>
            <input name="genitalia" value={form.genitalia || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Genitália (Outro)</label>
            <input name="genitalia_outro" value={form.genitalia_outro || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Preferência sexual</label>
            <input name="preferencia_sexual" value={form.preferencia_sexual || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Preferência sexual (Outro)</label>
            <input name="preferencia_sexual_outro" value={form.preferencia_sexual_outro || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Peso</label>
            <input name="peso" value={form.peso || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Altura</label>
            <input name="altura" value={form.altura || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Etnia</label>
            <input name="etnia" value={form.etnia || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Cor dos olhos</label>
            <input name="cor_olhos" value={form.cor_olhos || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Estilo de cabelo</label>
            <input name="estilo_cabelo" value={form.estilo_cabelo || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Tamanho do cabelo</label>
            <input name="tamanho_cabelo" value={form.tamanho_cabelo || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Tamanho do pé</label>
            <input name="tamanho_pe" value={form.tamanho_pe || ''} onChange={handleChange} className="input-field" />
          </div>
        </div>
        <div className="flex gap-8 items-center flex-wrap mb-2">
          <label className="font-semibold"><input type="checkbox" name="silicone" checked={!!form.silicone} onChange={handleChange} className="mr-2" />Silicone</label>
          <label className="font-semibold"><input type="checkbox" name="tatuagens" checked={!!form.tatuagens} onChange={handleChange} className="mr-2" />Tatuagens</label>
          <label className="font-semibold"><input type="checkbox" name="piercings" checked={!!form.piercings} onChange={handleChange} className="mr-2" />Piercings</label>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label className="block font-semibold">Fumante</label>
            <input name="fumante" value={form.fumante || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Idiomas</label>
            <input name="idiomas" value={form.idiomas || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Endereço</label>
            <input name="endereco" value={form.endereco || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Cidade</label>
            <input name="cidade_id" value={form.cidade_id || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Clientes em conjunto</label>
            <input name="clientes_conjunto" value={form.clientes_conjunto || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Atende</label>
            <input name="atende" value={form.atende || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Horário de expediente</label>
            <input name="horario_expediente" value={form.horario_expediente || ''} onChange={handleChange} className="input-field" />
          </div>
          <div>
            <label className="block font-semibold">Formas de pagamento</label>
            <input name="formas_pagamento" value={form.formas_pagamento || ''} onChange={handleChange} className="input-field" />
          </div>
        </div>
        <div>
          <label className="block font-semibold">Descrição</label>
          <textarea name="descricao" value={form.descricao || ''} onChange={handleChange} className="input-field" />
        </div>
        <div>
          <label className="block font-semibold">E-mail</label>
          <input name="email" value={form.email || ''} onChange={handleChange} className="input-field" />
        </div>
        <div>
          <label className="block font-semibold">Telefone</label>
          <input name="telefone" value={form.telefone || ''} onChange={handleChange} className="input-field" />
        </div>
        {/* Arquivos enviados */}
        <div>
          <label className="block font-semibold">Foto</label>
          {form.foto && <img src={form.foto} alt="Foto" className="h-32 w-32 object-cover rounded" />}
        </div>
        <div>
          <label className="block font-semibold">Documentos</label>
          {Array.isArray(form.documentos) && form.documentos.length > 0 ? (
            <ul>
              {form.documentos.map((doc: string, idx: number) => (
                <li key={idx}><a href={doc} target="_blank" rel="noopener noreferrer">Documento {idx + 1}</a></li>
              ))}
            </ul>
          ) : <span>Nenhum documento enviado</span>}
        </div>
        <div>
          <label className="block font-semibold">Vídeo de verificação</label>
          {form.video_verificacao && (
            <video src={form.video_verificacao} controls className="w-full max-w-xs" />
          )}
        </div>
        <div>
          <label className="block font-semibold">Galeria de Fotos</label>
          {Array.isArray(form.galeria_fotos) && form.galeria_fotos.length > 0 ? (
            <div className="flex gap-2 flex-wrap">
              {form.galeria_fotos.map((foto: string, idx: number) => (
                <img key={idx} src={foto} alt={`Galeria ${idx + 1}`} className="h-24 w-24 object-cover rounded" />
              ))}
            </div>
          ) : <span>Nenhuma foto enviada</span>}
        </div>
        <div>
          <label className="block font-semibold">Status</label>
          <select name="status" value={form.status || ''} onChange={handleChange} className="input-field">
            <option value="pendente">Pendente</option>
            <option value="aprovado">Aprovado</option>
            <option value="rejeitado">Rejeitado</option>
          </select>
        </div>
        <button type="button" onClick={handleSave} className="btn-primary mt-4">Salvar Alterações</button>
        {msg && <div className="mt-2">{msg}</div>}
      </div>
    </div>
  );
} 