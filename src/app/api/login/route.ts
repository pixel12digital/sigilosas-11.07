import { NextRequest, NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import bcrypt from "bcryptjs";

export async function POST(req: NextRequest) {
  const { usuario, senha } = await req.json();
  console.log('Payload recebido:', usuario, senha);

  const supabase = createClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!
  );

  const { data: admin, error } = await supabase
    .from("admin")
    .select("*")
    .eq("usuario", usuario)
    .single();

  console.log('Usuário encontrado:', admin);
  console.log('Erro:', error);

  if (error || !admin) {
    return NextResponse.json({ error: "Usuário ou senha inválidos!" }, { status: 401 });
  }

  const senhaOk = await bcrypt.compare(senha, admin.senha);
  console.log('Senha OK:', senhaOk);
  if (!senhaOk) {
    return NextResponse.json({ error: "Usuário ou senha inválidos!" }, { status: 401 });
  }

  // Aqui você pode gerar um token ou apenas retornar sucesso
  return NextResponse.json({ success: true });
} 