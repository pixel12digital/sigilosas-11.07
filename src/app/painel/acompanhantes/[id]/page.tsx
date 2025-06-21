"use client";
import { useEffect, useState, useRef } from "react";
import { useRouter, useParams } from "next/navigation";
import Image from "next/image";
import { createClientComponentClient } from '@supabase/auth-helpers-nextjs';
import type { Database } from '@/lib/database.types';

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
  const [videoUrl, setVideoUrl] = useState<string | null>(null);
  const [galeriaFiles, setGaleriaFiles] = useState<File[]>([]);
  const [galeriaPreview, setGaleriaPreview] = useState<any[]>([]);
  const documentosRef = useRef<HTMLInputElement>(null);
  const videoRef = useRef<HTMLInputElement>(null);
  const galeriaFotosRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    loadAcompanhante();
    fetchCidades();
  }, [id]);

  const fetchCidades = async () => {
    const { data, error } = await supabase.from("cidades").select("id, nome").order("nome");
    if (!error && data) setCidades(data);
  };

  const loadAcompanhante = async () => {
    if (!id) return;
    setLoading(true);
    setMsg("");
    try {
      // 1. Busca o perfil principal primeiro
      const { data: perfil, error: perfilError } = await supabase
        .from('acompanhantes')
        .select(`
          *,
          cidades (id, nome)
        `)
        .eq('id', id)
        .single();

      if (perfilError) {
        console.error("Erro ao buscar o perfil da acompanhante:", perfilError);
        throw new Error(`Falha ao carregar perfil: ${perfilError.message}`);
      }
      
      if (perfil) {
        // Normalize boolean-like fields that might come as strings from the DB
        const processedPerfil = { ...perfil };
        const booleanKeys = ['fumante', 'silicone', 'tatuagens', 'piercings'];
        for (const key of booleanKeys) {
          if (Object.prototype.hasOwnProperty.call(processedPerfil, key)) {
            (processedPerfil as any)[key] = String((processedPerfil as any)[key]).toLowerCase() === 'true';
          }
        }
        
        setForm({
          ...processedPerfil,
          cidade_id: perfil.cidades?.id || null,
          altura: perfil.altura,
        });

        // 2. Busca as mídias em chamadas separadas e não bloqueantes
        const { data: fotos } = await supabase.from('fotos').select('*').eq('acompanhante_id', id);
        const { data: videos } = await supabase.from('videos_verificacao').select('*').eq('acompanhante_id', id);
        const { data: documentos } = await supabase.from('documentos_acompanhante').select('*').eq('acompanhante_id', id);

        console.log("DADOS BRUTOS DO SUPABASE:");
        console.log("Fotos:", fotos);
        console.log("Vídeos:", videos);
        console.log("Documentos:", documentos);

        // Processar e definir previews das mídias
        const fotoPerfil = fotos?.find(f => f.tipo === 'perfil');
        if (fotoPerfil?.url) {
          const { data } = supabase.storage.from('media').getPublicUrl(fotoPerfil.url);
          setFotoPreview(data?.publicUrl || "");
        }

        const fotosGaleria = fotos?.filter(f => f.tipo === 'galeria');
        if (fotosGaleria && fotosGaleria.length > 0) {
          const galeriaItems = fotosGaleria.map(foto => {
            if (!foto.url) return null;
            const { data } = supabase.storage.from('media').getPublicUrl(foto.url);
            if (!data?.publicUrl) return null;
            return {
              id: foto.id,
              url: data.publicUrl,
              name: foto.storage_path || `galeria_foto_${foto.id}`
            };
          }).filter(Boolean);
          setGaleriaPreview(galeriaItems as any[]);
        }

        if (videos && videos.length > 0 && videos[0].url) {
          const { data } = supabase.storage.from('media').getPublicUrl(videos[0].url);
          if (data?.publicUrl) {
            console.log("URL Pública do Vídeo:", data.publicUrl);
            setVideoPreview(data.publicUrl);
          }
        }

        // Carrega os documentos da tabela 'documentos_acompanhante'
        if (documentos && documentos.length > 0) {
          const documentosUrls = documentos.map(doc => {
             // Extrai o caminho do arquivo, seja de um objeto JSON ou de um texto simples.
             let docPath = '';
             try {
                // Tenta interpretar como um objeto JSON: {"path":"..."}
                const parsedUrl = JSON.parse(doc.url);
                docPath = parsedUrl.path;
             } catch (e) {
                // Se falhar, assume que é um texto simples.
                docPath = doc.url;
             }

             if (!docPath) return null;

             const { data } = supabase.storage.from('media').getPublicUrl(docPath);
             if (!data?.publicUrl) return null;
             return { 
               url: data.publicUrl, 
               name: doc.storage_path || 'documento',
               id: doc.id
             };
          }).filter(Boolean);
          console.log("URLs Processadas dos Documentos:", documentosUrls);
          setDocumentosPreview(documentosUrls as any[]);
        }
      }

    } catch (error) {
      console.error('Erro geral ao carregar dados da acompanhante:', error);
      const errorMessage = error instanceof Error ? error.message : 'Ocorreu um erro desconhecido.';
      setMsg(`Falha ao carregar os dados. Tente recarregar a página. Detalhes: ${errorMessage}`);
    } finally {
      setLoading(false);
    }
  };

  const handleFotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setFotoFile(file);
      const url = URL.createObjectURL(file);
      setFotoPreview(url);
    }
  };

  const handleDocumentosChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newFiles = Array.from(e.target.files || []);
    if (newFiles.length > 0) {
      // Adiciona os novos arquivos à lista de arquivos a serem enviados
      setDocumentosFiles(prevFiles => [...prevFiles, ...newFiles]);

      // Cria previews para os novos arquivos
      const newPreviews = newFiles.map(file => {
        if (file.type.startsWith("image/")) {
          // O 'id' aqui é temporário e usado apenas para a key do React. A ausência de um UUID real indica que é um novo arquivo.
          return { id: `local-${file.name}-${Date.now()}`, url: URL.createObjectURL(file), type: "image", name: file.name };
        } else if (file.type === "application/pdf") {
          return { id: `local-${file.name}-${Date.now()}`, url: "/assets/img/pdf-icon.png", type: "pdf", name: file.name };
        }
        return { id: `local-${file.name}-${Date.now()}`, url: "", type: "other", name: file.name };
      });

      // Adiciona os novos previews à lista de exibição existente
      setDocumentosPreview(prevPreviews => [...prevPreviews, ...newPreviews]);
    }
  };

  const handleVideoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setVideoFile(file);
      const url = URL.createObjectURL(file);
      setVideoPreview(url);
    }
  };

  const handleGaleriaChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newFiles = Array.from(e.target.files || []);
    if (newFiles.length > 0) {
      setGaleriaFiles(prevFiles => [...prevFiles, ...newFiles]);

      const newPreviews = newFiles.map(file => ({
        id: `local-${file.name}-${Date.now()}`,
        url: URL.createObjectURL(file),
        name: file.name
      }));

      setGaleriaPreview(prevPreviews => [...prevPreviews, ...newPreviews]);
    }
  };

  useEffect(() => {
    if (form?.video_verificacao) {
      const fetchVideo = async () => {
        const videoData = await loadVideo();
        if (videoData) {
          setVideoUrl(videoData.url);
        }
      };
      fetchVideo();
    }
  }, [form?.video_verificacao]);

  if (loading) return <div className="p-8 text-center">Carregando...</div>;

  if (!form) return <div className="p-8 text-center">Acompanhante não encontrada</div>;

  // Função para obter URL pública da foto de perfil
  const getFotoPerfilUrl = (fotoPath: string) => {
    if (!fotoPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('media')
      .getPublicUrl(fotoPath)
    return publicUrl
  }

  // Função para obter URL pública do documento
  const getDocumentoUrl = (docPath: string) => {
    if (!docPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('media')
      .getPublicUrl(docPath)
    return publicUrl
  }

  // Função para obter URL pública do vídeo
  const getVideoUrl = (videoPath: string) => {
    if (!videoPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('media')
      .getPublicUrl(videoPath)
    return publicUrl
  }

  // Função para obter URL pública da foto da galeria
  const getFotoGaleriaUrl = (fotoPath: string) => {
    if (!fotoPath) return ''
    const { data: { publicUrl } } = supabase.storage
      .from('media')
      .getPublicUrl(fotoPath)
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
    if (!form?.video_verificacao) return null;
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

    try {
      // 1. Fazer upload de novas mídias e obter os paths
      // A lógica de upload deve ser adicionada aqui se necessário, similar ao formulário de cadastro.
      // Por exemplo, se o usuário puder trocar a foto de perfil aqui:
      if (fotoFile) {
        const filePath = await handleFileUpload(fotoFile, "perfil");
        if (filePath) {
            // Remove a foto antiga antes de adicionar a nova referência
            const oldFotoPath = form.fotos?.find((f: any) => f.tipo === 'perfil')?.url;
            if (oldFotoPath) {
                await supabase.storage.from('media').remove([oldFotoPath]);
                await supabase.from('fotos').delete().match({ acompanhante_id: id, tipo: 'perfil' });
            }
            // Insere a nova foto
            await supabase.from('fotos').insert({
                acompanhante_id: id,
                url: filePath,
                storage_path: filePath,
                tipo: 'perfil',
                principal: true,
            });
        }
      }
      
      if (documentosFiles.length > 0) {
        for (const file of documentosFiles) {
          const filePath = await handleFileUpload(file, "documentos");
          if (filePath) {
            await supabase.from('documentos_acompanhante').insert({
              acompanhante_id: id,
              url: filePath, // O path retornado pela função de upload
              storage_path: filePath,
              tipo: 'documento',
            });
          }
        }
        // Limpa os arquivos selecionados após o upload
        setDocumentosFiles([]);
      }

      if (galeriaFiles.length > 0) {
        for (const file of galeriaFiles) {
          const filePath = await handleFileUpload(file, "galeria");
          if (filePath) {
            await supabase.from('fotos').insert({
              acompanhante_id: id,
              url: filePath,
              storage_path: filePath,
              tipo: 'galeria',
            });
          }
        }
        setGaleriaFiles([]);
      }

      // 2. Preparar e salvar os dados do formulário principal
      // Clona o formulário para evitar mutação do estado
      const updateData = { ...form };

      // Converte altura para o formato numérico correto (metros), tratando vírgula decimal
      if (updateData.altura) {
        const alturaStr = String(updateData.altura).replace(',', '.');
        updateData.altura = parseFloat(alturaStr);
      }

      // Remove campos que não devem ir para o update ou que são de outras tabelas
      delete updateData.cidades; // Objeto de relacionamento
      delete updateData.auth_user_id; // Campo sensível/interno
      delete updateData.created_at; 
      delete updateData.id;
      // Remova outros campos que não existem na tabela 'acompanhantes' se houver
      // Ex: delete updateData.fotos; delete updateData.videos_verificacao; etc.


      const { error: updateError } = await supabase
        .from('acompanhantes')
        .update(updateData)
        .eq('id', id);

      if (updateError) {
        console.error("Erro ao salvar no banco de dados:", updateError);
        throw new Error(`Falha ao salvar alterações: ${updateError.message}`);
      }

      setMsg("Alterações salvas com sucesso! Redirecionando em 2 segundos...");
      setTimeout(() => {
        router.push('/painel/acompanhantes');
      }, 2000);

    } catch (error: any) {
      const defaultMessage = "Ocorreu um erro desconhecido ao salvar.";
      // Checa se a mensagem de erro já é a que queremos exibir
      const errorMessage = error.message.startsWith("Falha ao salvar alterações:") 
        ? error.message 
        : `${defaultMessage} Detalhes: ${error.message}`;
      setMsg(errorMessage);
      console.error("Erro no handleSubmit:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleFileUpload = async (file: File, folder: string) => {
    const fileExt = file.name.split('.').pop();
    const fileName = `${folder}/${id}_${Date.now()}.${fileExt}`;
    
    const { error } = await supabase.storage
      .from('media')
      .upload(fileName, file, { upsert: true });

    if (error) {
      console.error(`Erro no upload para ${folder}:`, error);
      return null;
    }
    return fileName; // Retorna o path, não a URL pública
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

  // Função para remover documento
  const handleRemoverDocumento = async (docToRemove: any) => {
    if (!window.confirm('Tem certeza que deseja remover este documento?')) return;

    // Caso 1: Documento ainda não foi salvo (é um arquivo local)
    // Identificamos isso porque o ID não é um UUID ou a URL é um blob.
    // A forma mais segura é checar se ele está na lista de `documentosFiles`.
    const isLocalFile = documentosFiles.some(file => file.name === docToRemove.name);

    if (isLocalFile) {
        // Remove da lista de arquivos a serem enviados
        setDocumentosFiles(prevFiles => prevFiles.filter(file => file.name !== docToRemove.name));
        // Remove da lista de previews
        setDocumentosPreview(prevPreviews => prevPreviews.filter(preview => preview.id !== docToRemove.id));
        
        if (docToRemove.url.startsWith('blob:')) {
            URL.revokeObjectURL(docToRemove.url);
        }
        alert('Documento removido da lista de upload.');
        return;
    }

    // Caso 2: Documento já salvo no banco de dados
    try {
        const urlParts = docToRemove.url.split('/');
        const bucketAndPath = urlParts.slice(urlParts.indexOf('media') + 2).join('/');
        
        const { error: dbError } = await supabase
            .from('documentos_acompanhante')
            .delete()
            .eq('id', docToRemove.id);

        if (dbError) throw new Error(`Falha ao remover do banco de dados: ${dbError.message}`);
        
        if (bucketAndPath) {
            const { error: storageError } = await supabase.storage.from('media').remove([bucketAndPath]);
            if (storageError) console.warn("Erro ao remover do storage:", storageError);
        }

        setDocumentosPreview(prev => prev.filter(doc => doc.id !== docToRemove.id));
        alert('Documento salvo removido com sucesso.');

    } catch (error) {
        console.error('Erro ao remover documento:', error);
        const errorMessage = error instanceof Error ? error.message : "Ocorreu um erro desconhecido.";
        alert(`Erro ao remover documento: ${errorMessage}`);
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
  const handleRemoverFoto = async (fotoToRemove: any) => {
    if (!window.confirm('Tem certeza que deseja remover esta foto?')) return;

    // Caso 1: Arquivo local ainda não salvo
    const isLocalFile = galeriaFiles.some(file => file.name === fotoToRemove.name);
    if (isLocalFile) {
        setGaleriaFiles(prev => prev.filter(file => file.name !== fotoToRemove.name));
        setGaleriaPreview(prev => prev.filter(p => p.id !== fotoToRemove.id));
        if (fotoToRemove.url.startsWith('blob:')) {
            URL.revokeObjectURL(fotoToRemove.url);
        }
        alert('Foto removida da lista de upload.');
        return;
    }

    // Caso 2: Foto já salva no banco de dados
    try {
        const urlParts = fotoToRemove.url.split('/');
        const bucketAndPath = urlParts.slice(urlParts.indexOf('media') + 2).join('/');

        const { error: dbError } = await supabase.from('fotos').delete().eq('id', fotoToRemove.id);
        if (dbError) throw new Error(`Falha ao remover do banco de dados: ${dbError.message}`);

        if (bucketAndPath) {
            const { error: storageError } = await supabase.storage.from('media').remove([bucketAndPath]);
            if (storageError) console.warn("Erro ao remover do storage:", storageError.message);
        }
        
        setGaleriaPreview(prev => prev.filter(p => p.id !== fotoToRemove.id));
        alert('Foto da galeria removida com sucesso.');
    } catch (error) {
        console.error('Erro ao remover foto da galeria:', error);
        alert(`Erro ao remover foto: ${error instanceof Error ? error.message : "Erro desconhecido"}`);
    }
  };

  // Função para excluir acompanhante e todos seus arquivos
  const handleDelete = async () => {
    if (!confirm('Tem certeza que deseja excluir este cadastro? Esta ação não pode ser desfeita.')) {
      return;
    }

    try {
      setLoading(true);
      // Excluir arquivos do storage - USA UM ÚNICO BUCKET 'media'
      const { data: files, error: listError } = await supabase.storage.from('media').list(id);
      if (files && files.length > 0) {
        const filePaths = files.map(file => `${id}/${file.name}`);
        await supabase.storage.from('media').remove(filePaths);
      }
      
      // Excluir registros das tabelas
      await supabase.from('fotos').delete().eq('acompanhante_id', id);
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
            <h2 className="text-2xl font-bold text-[#4E3950] mb-6">Informações Pessoais</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
              <div>
                <label htmlFor="nome" className={labelClass}>Nome</label>
                <input id="nome" name="nome" type="text" value={form.nome || ''} onChange={handleChange} className={inputClass} />
              </div>
              <div>
                <label htmlFor="idade" className={labelClass}>Idade</label>
                <input id="idade" name="idade" type="number" value={form.idade || ''} onChange={handleChange} className={inputClass} />
              </div>
              <div>
                <label htmlFor="telefone" className={labelClass}>Telefone</label>
                <input id="telefone" name="telefone" type="text" value={form.telefone || ''} onChange={handleChange} className={inputClass} />
              </div>
              <div>
                <label htmlFor="email" className={labelClass}>E-mail</label>
                <input id="email" name="email" type="email" value={form.email || ''} disabled className={`${inputClass} bg-gray-100`} />
              </div>
              <div>
                <label htmlFor="cidade_id" className={labelClass}>Cidade</label>
                <select id="cidade_id" name="cidade_id" value={form.cidade_id || ''} onChange={handleChange} className={inputClass}>
                  <option value="">Selecione uma cidade</option>
                  {cidades.map(c => <option key={c.id} value={c.id}>{c.nome}</option>)}
                </select>
              </div>
               <div>
                <label htmlFor="endereco" className={labelClass}>Endereço (Bairro)</label>
                <input id="endereco" name="endereco" type="text" value={form.endereco || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Centro" />
              </div>
            </div>

            {/* DETALHES DO PERFIL */}
            <div className="bg-white p-8 rounded-xl shadow-md mb-8">
              <h2 className="text-2xl font-bold text-[#4E3950] mb-6">Detalhes do Perfil</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">
                {/* Coluna 1 */}
                <div>
                  <label htmlFor="genero" className={labelClass}>Gênero</label>
                  <select id="genero" name="genero" value={form.genero || ''} onChange={handleChange} className={inputClass}>
                    <option value="">Selecione</option>
                    <option value="feminino">Feminino</option>
                    <option value="masculino">Masculino</option>
                    <option value="trans">Trans</option>
                    <option value="outro">Outro</option>
                  </select>

                  <label htmlFor="genitalia" className={labelClass}>Genitália</label>
                  <input id="genitalia" name="genitalia" type="text" value={form.genitalia || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Natural, Operada" />
                  
                  <label htmlFor="preferencia_sexual" className={labelClass}>Preferência Sexual</label>
                  <input id="preferencia_sexual" name="preferencia_sexual" type="text" value={form.preferencia_sexual || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Heterossexual, Bissexual" />
                </div>
                {/* Coluna 2 */}
                <div>
                  <label htmlFor="peso" className={labelClass}>Peso (kg)</label>
                  <input id="peso" name="peso" type="number" value={form.peso || ''} onChange={handleChange} className={inputClass} placeholder="Ex: 65" />
                  
                  <label htmlFor="altura" className={labelClass}>Altura (m)</label>
                  <input id="altura" name="altura" type="number" step="0.01" value={form.altura || ''} onChange={handleChange} className={inputClass} placeholder="Ex: 1.70" />

                  <label htmlFor="tamanho_pe" className={labelClass}>Tamanho do Pé</label>
                  <input id="tamanho_pe" name="tamanho_pe" type="number" value={form.tamanho_pe || ''} onChange={handleChange} className={inputClass} placeholder="Ex: 38" />
                </div>
                {/* Coluna 3 */}
                <div>
                  <label htmlFor="etnia" className={labelClass}>Etnia</label>
                  <input id="etnia" name="etnia" type="text" value={form.etnia || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Branca, Morena, Negra" />

                  <label htmlFor="cor_dos_olhos" className={labelClass}>Cor dos Olhos</label>
                  <input id="cor_dos_olhos" name="cor_dos_olhos" type="text" value={form.cor_dos_olhos || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Castanhos, Azuis" />

                  <label htmlFor="estilo_cabelo" className={labelClass}>Estilo do Cabelo</label>
                  <input id="estilo_cabelo" name="estilo_cabelo" type="text" value={form.estilo_cabelo || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Liso, Ondulado" />
                  
                  <label htmlFor="tamanho_cabelo" className={labelClass}>Tamanho do Cabelo</label>
                  <input id="tamanho_cabelo" name="tamanho_cabelo" type="text" value={form.tamanho_cabelo || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Curto, Longo" />
                </div>
              </div>

              <div className="mt-6">
                <label htmlFor="descricao" className={labelClass}>Descrição / Sobre mim</label>
                <textarea id="descricao" name="descricao" value={form.descricao || ''} onChange={handleChange} className={inputClass} rows={5}></textarea>
              </div>
              
              <div className="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                  <div className="flex items-center">
                      <input id="fumante" name="fumante" type="checkbox" checked={form.fumante || false} onChange={handleChange} className={checkboxClass} />
                      <label htmlFor="fumante" className="text-[#4E3950]">Fumante</label>
                  </div>
                  <div className="flex items-center">
                      <input id="silicone" name="silicone" type="checkbox" checked={form.silicone || false} onChange={handleChange} className={checkboxClass} />
                      <label htmlFor="silicone" className="text-[#4E3950]">Silicone</label>
                  </div>
                  <div className="flex items-center">
                      <input id="tatuagens" name="tatuagens" type="checkbox" checked={form.tatuagens || false} onChange={handleChange} className={checkboxClass} />
                      <label htmlFor="tatuagens" className="text-[#4E3950]">Tatuagens</label>
                  </div>
                  <div className="flex items-center">
                      <input id="piercings" name="piercings" type="checkbox" checked={form.piercings || false} onChange={handleChange} className={checkboxClass} />
                      <label htmlFor="piercings" className="text-[#4E3950]">Piercings</label>
                  </div>
              </div>
            </div>

            {/* DETALHES DE ATENDIMENTO */}
            <div className="bg-white p-8 rounded-xl shadow-md mb-8">
              <h2 className="text-2xl font-bold text-[#4E3950] mb-6">Detalhes de Atendimento</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                 <div>
                  <label htmlFor="idiomas" className={labelClass}>Idiomas</label>
                  <input id="idiomas" name="idiomas" type="text" value={form.idiomas || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Português, Inglês" />
                </div>
                <div>
                  <label htmlFor="horario_expediente" className={labelClass}>Horário de Expediente</label>
                  <input id="horario_expediente" name="horario_expediente" type="text" value={form.horario_expediente || ''} onChange={handleChange} className={inputClass} placeholder="Ex: 08:00 - 18:00" />
                </div>
                 <div>
                  <label htmlFor="formas_pagamento" className={labelClass}>Formas de Pagamento</label>
                  <input id="formas_pagamento" name="formas_pagamento" type="text" value={form.formas_pagamento || ''} onChange={handleChange} className={inputClass} placeholder="Ex: Dinheiro, Pix, Cartão" />
                </div>
              </div>
            </div>

            {/* MÍDIAS E DOCUMENTOS */}
            <div className="bg-white p-8 rounded-xl shadow-md mb-8">
              <h2 className="text-2xl font-bold text-[#4E3950] mb-6">Mídias e Documentos</h2>
              
              {/* CAMPO FOTO */}
              <div className="mb-6">
                <label className={labelClass}>Foto de Perfil</label>
                {fotoPreview ? (
                  <div className="mt-2 text-center">
                    <Image src={fotoPreview} alt="Preview da Foto" width={192} height={192} className="w-48 h-48 object-cover rounded-lg inline-block" />
                    <button type="button" onClick={() => { setFotoFile(null); setFotoPreview(""); }} className="mt-2 text-sm text-red-600 hover:text-red-800">Remover Foto</button>
                  </div>
                ) : (
                  <label htmlFor="foto" className={uploadButtonClass}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Adicionar foto
                  </label>
                )}
                <input id="foto" type="file" accept="image/*" onChange={handleFotoChange} className="hidden" />
                {fotoMsg && <p className="text-sm mt-2 text-center">{fotoMsg}</p>}
              </div>

              {/* CAMPO DOCUMENTOS */}
              <div className="mb-6">
                <label className={labelClass}>Documentos do Perfil</label>
                <div className="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                  {documentosPreview.map((doc) => (
                    <div key={doc.id} className="relative group">
                      {doc.type === 'pdf' ? (
                          <Image src="/assets/img/icons/icon-document.svg" alt={doc.name} width={100} height={100} className="w-full h-32 object-contain rounded-lg border p-2" />
                      ) : (
                          <Image src={doc.url} alt={doc.name} width={150} height={150} className="w-full h-32 object-cover rounded-lg" />
                      )}
                      <p className="text-xs text-center truncate mt-1">{doc.name}</p>
                      <button
                        type="button"
                        onClick={() => handleRemoverDocumento(doc)}
                        className="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        ×
                      </button>
                    </div>
                  ))}
                </div>
                 <label htmlFor="documentos" className={`${uploadButtonClass} mt-4`}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    Adicionar documentos
                </label>
                <input id="documentos" type="file" multiple accept="image/*,application/pdf" ref={documentosRef} onChange={handleDocumentosChange} className="hidden" />
              </div>

              {/* CAMPO VÍDEO DE VERIFICAÇÃO */}
              <div className="mb-6">
                <label className={labelClass}>Vídeo de Verificação</label>
                {videoPreview ? (
                  <div className="mt-2">
                    <video src={videoPreview} controls className="w-full rounded-lg max-h-64"></video>
                    <button type="button" onClick={() => { setVideoFile(null); setVideoPreview(""); }} className="mt-2 text-sm text-red-600 hover:text-red-800">Remover Vídeo</button>
                  </div>
                ) : (
                  <label htmlFor="video" className={uploadButtonClass}>
                     <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.55a1 1 0 01.45 1.72l-2 1.5a1 1 0 01-1.2-.22L13 10M15 10l-2 4.5a1 1 0 01-1.73.45L8 10m7-5a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                     Adicionar vídeo
                  </label>
                )}
                <input id="video" type="file" accept="video/*" ref={videoRef} onChange={handleVideoChange} className="hidden" />
              </div>

              {/* CAMPO GALERIA DE FOTOS */}
              <div className="mb-6">
                <label className={labelClass}>Galeria de Fotos</label>
                 <div className="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                  {galeriaPreview.map((foto) => (
                    <div key={foto.id} className="relative group">
                      <Image src={foto.url} alt={`Foto da galeria ${foto.name}`} width={150} height={150} className="w-full h-32 object-cover rounded-lg" />
                      <button
                        type="button"
                        onClick={() => handleRemoverFoto(foto)}
                        className="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        ×
                      </button>
                    </div>
                  ))}
                </div>
                 <label htmlFor="galeria" className={`${uploadButtonClass} mt-4`}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Adicionar fotos à galeria
                </label>
                <input id="galeria" type="file" multiple accept="image/*" ref={galeriaFotosRef} onChange={handleGaleriaChange} className="hidden" />
              </div>

            </div>

            {/* STATUS E AÇÕES */}
            <div className="bg-white p-8 rounded-xl shadow-md">
              <h3 className="text-xl font-bold text-[#4E3950] mb-4">Status e Ações</h3>
              <div>
                <label htmlFor="status" className={labelClass}>Status do Perfil</label>
                <select id="status" name="status" value={form.status || ''} onChange={handleChange} className={inputClass}>
                  <option value="pendente">Pendente</option>
                  <option value="aprovado">Aprovado</option>
                  <option value="rejeitado">Rejeitado</option>
                </select>
              </div>

              <div className="mt-8 flex flex-col md:flex-row gap-4">
                <button
                  type="submit"
                  className="w-full md:w-auto flex-1 py-3 px-6 bg-[#4E3950] text-white rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-[#CFB78B] hover:text-[#4E3950] disabled:opacity-50 disabled:cursor-not-allowed"
                  disabled={loading}
                >
                  {loading ? 'Salvando...' : 'Salvar Alterações'}
                </button>
                
                <button
                  type="button"
                  onClick={handleDelete}
                  className="w-full md:w-auto flex-1 py-3 px-6 bg-red-600 text-white rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-red-700 disabled:opacity-50"
                  disabled={loading}
                >
                  {loading ? "Excluindo..." : "Excluir"}
                </button>
                
                <button
                  type="button"
                  className="w-full md:w-auto flex-1 py-3 px-6 bg-gray-300 text-[#4E3950] rounded-lg font-semibold text-lg tracking-wide cursor-pointer transition-colors hover:bg-gray-400 disabled:opacity-50"
                  onClick={() => router.push('/painel/acompanhantes')}
                >
                  Cancelar
                </button>
              </div>
              {msg && <div className="mt-4 text-center text-sm text-gray-600">{msg}</div>}
            </div>
          </div>
        </form>
      </main>
    </div>
  );
} 