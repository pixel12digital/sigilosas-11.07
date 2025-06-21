import { createClient } from '@supabase/supabase-js';

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL!;
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!;

export const supabase = createClient(supabaseUrl, supabaseAnonKey);

// Cliente para operações do servidor (com service role key)
export const supabaseAdmin =
  typeof process !== 'undefined' && process.env.SUPABASE_SERVICE_ROLE_KEY
    ? createClient(
        process.env.NEXT_PUBLIC_SUPABASE_URL!,
        process.env.SUPABASE_SERVICE_ROLE_KEY!
      )
    : undefined;

// Tipos para as tabelas do Supabase
export interface Acompanhante {
  id: string;
  nome: string;
  cidade_id?: string;
  idade?: number;
  genero?: string;
  valor_padrao?: number;
  descricao?: string;
  destaque?: boolean;
  created_at?: string;
  status: 'pendente' | 'aprovado' | 'rejeitado' | 'bloqueado';
  verificado?: boolean;
  bairro?: string;
  etnia?: string;
  silicone?: boolean;
  tatuagens?: boolean;
  piercings?: boolean;
  // Relacionamentos com mídia
  fotos?: { 
    id: string;
    url: string; 
    storage_path: string;
    tipo: string;
    principal: boolean; 
  }[];
  videos_verificacao?: { 
    id: string;
    url: string; 
    storage_path: string;
  }[];
  documentos_acompanhante?: { 
    id: string;
    url: string; 
    storage_path: string;
    tipo: string;
  }[];
}

export interface Cidade {
  id: string;
  nome: string;
}

export interface Foto {
  id: number;
  acompanhante_id: number;
  url: string;
  capa: boolean;
  tipo?: string;
}

export interface Avaliacao {
  id: number;
  acompanhante_id: number;
  nota: number;
  comentario?: string;
  data: string;
  status: 'pendente' | 'aprovado' | 'rejeitado';
}

export interface Configuracao {
  id: number;
  chave: string;
  valor?: string;
}

export interface Usuario {
  id: number;
  email: string;
  tipo: 'admin' | 'editora';
  acompanhante_id?: number;
  criado_em: string;
} 