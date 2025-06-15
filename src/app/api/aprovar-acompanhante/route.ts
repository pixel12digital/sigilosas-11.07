import { NextRequest, NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import bcrypt from "bcryptjs";

export async function POST(req: NextRequest) {
  const { acompanhante_id } = await req.json();
  if (!acompanhante_id) {
    return NextResponse.json({ erro: "ID do acompanhante não informado." }, { status: 400 });
  }

  const supabase = createClient(
    process.env.SUPABASE_URL!,
    process.env.SUPABASE_SERVICE_ROLE_KEY!
  );

  // Busca dados do acompanhante
  const { data: acomp, error: acompErr } = await supabase
    .from("acompanhantes")
    .select("id, email, senha")
    .eq("id", acompanhante_id)
    .single();
  if (acompErr || !acomp) {
    return NextResponse.json({ erro: "Acompanhante não encontrado." }, { status: 404 });
  }

  // Atualiza status para aprovado
  const { error: updErr } = await supabase
    .from("acompanhantes")
    .update({ status: "aprovado" })
    .eq("id", acompanhante_id);
  if (updErr) {
    return NextResponse.json({ erro: "Erro ao aprovar acompanhante." }, { status: 500 });
  }

  // Verifica se já existe usuário para este acompanhante
  const { data: userExists } = await supabase
    .from("usuarios")
    .select("id")
    .eq("acompanhante_id", acompanhante_id)
    .maybeSingle();
  if (!userExists) {
    // Cria usuário editora
    const senhaHash = await bcrypt.hash(acomp.senha, 10);
    const { error: userErr } = await supabase
      .from("usuarios")
      .insert([
        {
          email: acomp.email,
          senha: senhaHash,
          tipo: "editora",
          acompanhante_id,
        },
      ]);
    if (userErr) {
      return NextResponse.json({ erro: "Erro ao criar usuário." }, { status: 500 });
    }
  }

  return NextResponse.json({ sucesso: true });
} 