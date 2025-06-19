import { createClient } from '@supabase/supabase-js';
import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  try {
    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL!,
      process.env.SUPABASE_SERVICE_ROLE_KEY!
    );

    const { id } = await request.json();

    if (!id) {
      return NextResponse.json({ 
        success: false, 
        error: 'O ID da cidade é obrigatório' 
      }, { status: 400 });
    }

    const { error } = await supabase
      .from('cidades')
      .delete()
      .eq('id', id);

    if (error) {
        throw error;
    }

    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error('Erro ao excluir cidade:', error);
    return NextResponse.json({ 
      success: false, 
      error: error.message || 'Erro ao excluir cidade' 
    }, { status: 500 });
  }
} 