export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export interface Database {
  public: {
    Tables: {
      acompanhantes: {
        Row: {
          id: number
          nome: string
          cidade_id: number | null
          idade: number | null
          genero: string | null
          valor: number | null
          descricao: string | null
          destaque: boolean
          data_cadastro: string
          status: 'pendente' | 'aprovado' | 'rejeitado' | 'bloqueado'
          disponibilidade: string | null
          verificado: boolean
          bairro: string | null
          aceita_cartao: boolean
          atende_casal: boolean
          local_proprio: boolean
          aceita_pix: boolean
          genitalia: string | null
          genitalia_outro: string | null
          preferencia_sexual: string | null
          preferencia_sexual_outro: string | null
          peso: string | null
          altura: string | null
          etnia: string | null
          cor_olhos: string | null
          estilo_cabelo: string | null
          tamanho_cabelo: string | null
          tamanho_pe: string | null
          silicone: boolean
          tatuagens: boolean
          piercings: boolean
          fumante: string | null
          idiomas: string | null
          endereco: string | null
          comodidades: string | null
          bairros_atende: string | null
          cidades_vizinhas: string | null
          clientes_conjunto: number
          atende_genero: string | null
          horario_expediente: string | null
          formas_pagamento: string | null
          seguidores: number
          favoritos: number
          penalidades: boolean
          contato_seguro: boolean
          data_criacao: string | null
          foto: string | null
          video_verificacao: string | null
          email: string | null
          telefone: string | null
          user_id: string | null
        }
        Insert: {
          id?: number
          nome: string
          cidade_id?: number | null
          idade?: number | null
          genero?: string | null
          valor?: number | null
          descricao?: string | null
          destaque?: boolean
          data_cadastro?: string
          status?: 'pendente' | 'aprovado' | 'rejeitado' | 'bloqueado'
          disponibilidade?: string | null
          verificado?: boolean
          bairro?: string | null
          aceita_cartao?: boolean
          atende_casal?: boolean
          local_proprio?: boolean
          aceita_pix?: boolean
          genitalia?: string | null
          genitalia_outro?: string | null
          preferencia_sexual?: string | null
          preferencia_sexual_outro?: string | null
          peso?: string | null
          altura?: string | null
          etnia?: string | null
          cor_olhos?: string | null
          estilo_cabelo?: string | null
          tamanho_cabelo?: string | null
          tamanho_pe?: string | null
          silicone?: boolean
          tatuagens?: boolean
          piercings?: boolean
          fumante?: string | null
          idiomas?: string | null
          endereco?: string | null
          comodidades?: string | null
          bairros_atende?: string | null
          cidades_vizinhas?: string | null
          clientes_conjunto?: number
          atende_genero?: string | null
          horario_expediente?: string | null
          formas_pagamento?: string | null
          seguidores?: number
          favoritos?: number
          penalidades?: boolean
          contato_seguro?: boolean
          data_criacao?: string | null
          foto?: string | null
          video_verificacao?: string | null
          email?: string | null
          telefone?: string | null
          user_id?: string | null
        }
        Update: {
          id?: number
          nome?: string
          cidade_id?: number | null
          idade?: number | null
          genero?: string | null
          valor?: number | null
          descricao?: string | null
          destaque?: boolean
          data_cadastro?: string
          status?: 'pendente' | 'aprovado' | 'rejeitado' | 'bloqueado'
          disponibilidade?: string | null
          verificado?: boolean
          bairro?: string | null
          aceita_cartao?: boolean
          atende_casal?: boolean
          local_proprio?: boolean
          aceita_pix?: boolean
          genitalia?: string | null
          genitalia_outro?: string | null
          preferencia_sexual?: string | null
          preferencia_sexual_outro?: string | null
          peso?: string | null
          altura?: string | null
          etnia?: string | null
          cor_olhos?: string | null
          estilo_cabelo?: string | null
          tamanho_cabelo?: string | null
          tamanho_pe?: string | null
          silicone?: boolean
          tatuagens?: boolean
          piercings?: boolean
          fumante?: string | null
          idiomas?: string | null
          endereco?: string | null
          comodidades?: string | null
          bairros_atende?: string | null
          cidades_vizinhas?: string | null
          clientes_conjunto?: number
          atende_genero?: string | null
          horario_expediente?: string | null
          formas_pagamento?: string | null
          seguidores?: number
          favoritos?: number
          penalidades?: boolean
          contato_seguro?: boolean
          data_criacao?: string | null
          foto?: string | null
          video_verificacao?: string | null
          email?: string | null
          telefone?: string | null
          user_id?: string | null
        }
      }
      cidades: {
        Row: {
          id: number
          nome: string
        }
        Insert: {
          id?: number
          nome: string
        }
        Update: {
          id?: number
          nome?: string
        }
      }
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      [_ in never]: never
    }
    Enums: {
      [_ in never]: never
    }
  }
} 