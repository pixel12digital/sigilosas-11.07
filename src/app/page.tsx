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
  estado_uf: string;
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

const inputClass = "w-full bg-white border border-[#CFB78B] rounded-lg px-4 py-2 text-[#4E3950] focus:outline-none focus:ring-2 focus:ring-[#CFB78B] text-base transition mb-0 placeholder-[#CFB78B]";
const labelClass = "block font-semibold text-[#4E3950] mb-1";

export default function CadastroAcompanhante() {
  const [cidades, setCidades] = useState<Cidade[]>([]);
  const [cidadesFiltradas, setCidadesFiltradas] = useState<Cidade[]>([]);
  const [estadoSelecionado, setEstadoSelecionado] = useState("");
  const [form, setForm] = useState({
    nome: "",
    email: "",
    senha: "",
    telefone: "",
    idade: "",
    genero: "",
    cidade_id: "",
    descricao: "",
    foto: "",
    galeria_fotos: [],
  });
  const [fotoFile, setFotoFile] = useState<File | null>(null);
  const [galeriaFiles, setGaleriaFiles] = useState<File[]>([]);
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState("");
  const router = useRouter();

  useEffect(() => {
    const fetchCidades = async () => {
      const supabase = createClientComponentClient();
      const { data, error } = await supabase
        .from("vw_cidades_estados")
        .select("id:cidade_id, nome:cidade, estado_uf")
        .order("nome");
      if (error) {
        console.error("Erro ao buscar cidades:", error);
      } else if (data) {
        setCidades(data);
      }
    };
    fetchCidades();
  }, []);

  useEffect(() => {
    if (estadoSelecionado) {
      const filtradas = cidades.filter(c => c.estado_uf === estadoSelecionado);
      setCidadesFiltradas(filtradas);
      setForm(prev => ({ ...prev, cidade_id: "" }));
    } else {
      setCidadesFiltradas([]);
    }
  }, [estadoSelecionado, cidades]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  };

  const handleEstadoChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setEstadoSelecionado(e.target.value);
  };

  const handleTelefoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm(prev => ({ ...prev, telefone: formatarTelefone(e.target.value) }));
  };

  const handleFotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setFotoFile(e.target.files[0]);
    }
  };

  const handleGaleriaChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      setGaleriaFiles(Array.from(e.target.files));
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setMsg("");

    try {
      const supabase = createClientComponentClient();
      let foto_url = "";
      if (fotoFile) {
        const fileName = `perfil/${Date.now()}_${fotoFile.name}`;
        const { data: uploadData, error: uploadError } = await supabase.storage
          .from("images")
          .upload(fileName, fotoFile);
        if (uploadError) throw uploadError;
        const { data: urlData } = supabase.storage.from("images").getPublicUrl(fileName);
        foto_url = urlData.publicUrl;
      }

      const galeria_urls = [];
      for (const file of galeriaFiles) {
        const fileName = `galeria/${Date.now()}_${file.name}`;
        const { data: uploadData, error: uploadError } = await supabase.storage
          .from("images")
          .upload(fileName, file);
        if (uploadError) throw uploadError;
        const { data: urlData } = supabase.storage.from("images").getPublicUrl(fileName);
        galeria_urls.push(urlData.publicUrl);
      }

      const submissionData = { ...form, foto: foto_url, galeria_fotos: galeria_urls };
      const res = await fetch("/api/cadastro", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(submissionData),
      });

      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error?.message || "Erro no servidor");
      }
      
      router.push("/obrigado");
    } catch (error: any) {
      setMsg(error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white min-h-screen text-[#4E3950]">
      <div className="max-w-4xl mx-auto p-8">
        <h1 className="text-3xl font-bold text-center mb-8">Cadastro de Acompanhante</h1>
        <form onSubmit={handleSubmit} className="space-y-6">
          <input type="text" name="nome" placeholder="Nome" onChange={handleChange} className={inputClass} required />
          <input type="email" name="email" placeholder="E-mail" onChange={handleChange} className={inputClass} required />
          <input type="password" name="senha" placeholder="Senha" onChange={handleChange} className={inputClass} required />
          <input type="tel" name="telefone" placeholder="Telefone" value={form.telefone} onChange={handleTelefoneChange} className={inputClass} required />
          <input type="number" name="idade" placeholder="Idade" onChange={handleChange} className={inputClass} required min="18" />
          <select name="genero" onChange={handleChange} className={inputClass} required>
            <option value="">Selecione o Gênero</option>
            <option value="feminino">Feminino</option>
            <option value="masculino">Masculino</option>
            <option value="trans">Trans</option>
          </select>
          <select name="estado" onChange={handleEstadoChange} className={inputClass} required>
            <option value="">Selecione um Estado</option>
            {ESTADOS.map(e => <option key={e.uf} value={e.uf}>{e.nome}</option>)}
          </select>
          <select name="cidade_id" onChange={handleChange} className={inputClass} required disabled={!estadoSelecionado}>
            <option value="">Selecione uma Cidade</option>
            {cidadesFiltradas.map(c => <option key={c.id} value={c.id}>{c.nome}</option>)}
          </select>
          <textarea name="descricao" placeholder="Descrição" onChange={handleChange} className={inputClass} />
          <div>
            <label className={labelClass}>Foto de Perfil</label>
            <input type="file" onChange={handleFotoChange} />
          </div>
          <div>
            <label className={labelClass}>Galeria de Fotos</label>
            <input type="file" multiple onChange={handleGaleriaChange} />
          </div>
          <button type="submit" disabled={loading} className="w-full py-3 bg-[#4E3950] text-white rounded-lg font-semibold hover:bg-[#CFB78B]">
            {loading ? "Enviando..." : "Cadastrar"}
          </button>
          {msg && <p className="text-red-500 text-center mt-4">{msg}</p>}
        </form>
      </div>
    </div>
  );
}