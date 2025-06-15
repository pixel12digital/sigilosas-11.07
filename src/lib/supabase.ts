import { createClient } from '@supabase/supabase-js';

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL!;
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!;

export const supabase = createClient(supabaseUrl, supabaseAnonKey);

// Cliente para operações do servidor (com service role key)
export const supabaseAdmin = createClient(
  process.env.SUPABASE_URL!,
  process.env.SUPABASE_SERVICE_ROLE_KEY!
);

// Tipos para as tabelas do Supabase
export interface Acompanhante {
  id: number;
  nome: string;
  cidade_id?: number;
  idade?: number;
  genero?: 'F' | 'M' | 'Outro';
  valor?: number;
  descricao?: string;
  destaque: boolean;
  data_cadastro: string;
  status: 'pendente' | 'aprovado' | 'rejeitado';
  disponibilidade?: string;
  verificado: boolean;
  bairro?: string;
  aceita_cartao: boolean;
  atende_casal: boolean;
  local_proprio: boolean;
  aceita_pix: boolean;
  genitalia?: string;
  preferencia_sexual?: string;
  peso?: string;
  altura?: string;
  etnia?: 'Branca' | 'Negra' | 'Parda' | 'Amarela' | 'Indígena' | 'Outro';
  cor_olhos?: string;
  estilo_cabelo?: string;
  tamanho_cabelo?: string;
  tamanho_pe?: string;
  silicone: boolean;
  tatuagens: boolean;
  piercings: boolean;
  fumante?: string;
  idiomas?: string;
  endereco?: string;
  comodidades?: string;
  bairros_atende?: string;
  cidades_vizinhas?: string;
  clientes_conjunto: number;
  atende_genero?: string;
  horario_expediente?: string;
  formas_pagamento?: string;
  seguidores: number;
  favoritos: number;
  penalidades: boolean;
  contato_seguro: boolean;
  data_criacao?: string;
  foto?: string;
  video_verificacao?: string;
}

export interface Cidade {
  id: number;
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