import { NextRequest, NextResponse } from "next/server";
import bcrypt from "bcryptjs";

export async function POST(req: NextRequest) {
  const { senha } = await req.json();
  if (!senha) {
    return NextResponse.json({ error: 'Senha não informada.' }, { status: 400 });
  }
  const hash = await bcrypt.hash(senha, 10);
  return NextResponse.json({ hash });
} 