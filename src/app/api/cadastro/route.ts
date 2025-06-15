import { NextRequest, NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import bcrypt from "bcryptjs";

export async function POST(req: NextRequest) {
  const body = await req.json();
  const {
    nome,
    email,
    telefone,
    senha,
    cidade_id,
    idade,
    genero,
    valor,
    descricao,
    foto,
    genitalia,
    genitalia_outro,
    preferencia_sexual,
    preferencia_sexual_outro,
    peso,
    altura,
    etnia,
    cor_olhos,
    estilo_cabelo,
    tamanho_cabelo,
    tamanho_pe,
    silicone,
    tatuagens,
    piercings,
    fumante,
    idiomas,
    endereco,
    clientes_conjunto,
    atende,
    horario_expediente,
    formas_pagamento,
    data_criacao,
    galeria_fotos,
    documentos,
    video_verificacao,
  } = body;

  // Validação básica
  if (!nome || !email || !senha || !cidade_id || !idade || !genero) {
    return NextResponse.json({ erro: "Preencha todos os campos obrigatórios." }, { status: 400 });
  }

  // Conecta com Supabase usando Service Role
  const supabase = createClient(
    process.env.SUPABASE_URL!,
    process.env.SUPABASE_SERVICE_ROLE_KEY!
  );

  try {
    // 1. Cria acompanhante (status pendente)
    const { data: acomp, error: acompErr } = await supabase
      .from("acompanhantes")
      .insert([
        {
          nome,
          email,
          telefone,
          cidade_id: Number(cidade_id),
          idade: Number(idade),
          genero,
          valor: valor ? Number(valor) : null,
          descricao,
          status: "pendente",
          genitalia,
          genitalia_outro,
          preferencia_sexual,
          preferencia_sexual_outro,
          peso,
          altura,
          etnia,
          cor_olhos,
          estilo_cabelo,
          tamanho_cabelo,
          tamanho_pe,
          silicone: !!silicone,
          tatuagens: !!tatuagens,
          piercings: !!piercings,
          fumante,
          idiomas,
          endereco,
          clientes_conjunto: clientes_conjunto ? Number(clientes_conjunto) : null,
          atende,
          horario_expediente,
          formas_pagamento,
          data_criacao,
          video_verificacao: video_verificacao || null,
        },
      ])
      .select("id")
      .single();
    if (acompErr) throw acompErr;
    const acompanhante_id = acomp.id;

    // 2. Cria usuário editora
    const senhaHash = await bcrypt.hash(senha, 10);
    const { error: userErr } = await supabase
      .from("usuarios")
      .insert([
        {
          email,
          senha: senhaHash,
          tipo: "editora",
          acompanhante_id,
        },
      ]);
    if (userErr) throw userErr;

    // 3. Foto de capa (opcional)
    if (foto) {
      const { error: fotoErr } = await supabase
        .from("fotos")
        .insert([
          {
            acompanhante_id,
            url: foto,
            capa: true,
          },
        ]);
      if (fotoErr) throw fotoErr;
    }

    // 4. Galeria de fotos (array de URLs)
    if (galeria_fotos && Array.isArray(galeria_fotos)) {
      for (const url of galeria_fotos) {
        if (url) {
          const { error: galeriaErr } = await supabase
            .from("fotos")
            .insert([
              {
                acompanhante_id,
                url,
                capa: false,
              },
            ]);
          if (galeriaErr) throw galeriaErr;
        }
      }
    }

    // 5. Documentos (array de URLs)
    if (documentos && Array.isArray(documentos)) {
      for (const url of documentos) {
        if (url) {
          const { error: docErr } = await supabase
            .from("documentos")
            .insert([
              {
                acompanhante_id,
                url,
              },
            ]);
          if (docErr) throw docErr;
        }
      }
    }

    // 6. Vídeo de verificação (URL)
    if (video_verificacao) {
      const { error: videoErr } = await supabase
        .from("videos")
        .insert([
          {
            acompanhante_id,
            url: video_verificacao,
          },
        ]);
      if (videoErr) throw videoErr;
    }

    return NextResponse.json({ sucesso: true });
  } catch (err: any) {
    return NextResponse.json({ erro: err.message || "Erro ao cadastrar." }, { status: 500 });
  }
} 