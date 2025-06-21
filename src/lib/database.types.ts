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
          id: string
          user_id: string | null
          nome: string
          email: string
          telefone: string
          idade: number | null
          whatsapp: string | null
          telegram: string | null
          genero: string
          genitalia: string | null
          preferencia_sexual: string | null
          bairro: string | null
          endereco: string | null
          cep: string | null
          peso: number | null
          altura: number | null
          manequim: string | null
          etnia: string | null
          cor_olhos: string | null
          cor_cabelo: string | null
          estilo_cabelo: string | null
          tamanho_cabelo: string | null
          tamanho_pe: number | null
          busto: number | null
          cintura: number | null
          quadril: number | null
          silicone: boolean | null
          tatuagens: boolean | null
          piercings: boolean | null
          fumante: boolean | null
          local_atendimento: string[] | null
          formas_pagamento: string[] | null
          horario_atendimento: Json | null
          valor_padrao: number | null
          valor_promocional: number | null
          idiomas: string[] | null
          especialidades: string[] | null
          descricao: string | null
          sobre_mim: string | null
          instagram: string | null
          twitter: string | null
          tiktok: string | null
          site: string | null
          status: "pendente" | "aprovado" | "rejeitado" | "bloqueado"
          verificado: boolean | null
          destaque: boolean | null
          destaque_ate: string | null
          bloqueado: boolean | null
          motivo_bloqueio: string | null
          motivo_rejeicao: string | null
          revisado_por: number | null
          data_revisao: string | null
          total_fotos: number | null
          total_videos: number | null
          total_documentos: number | null
          created_at: string
          updated_at: string
          ultimo_login: string | null
          ultima_atualizacao: string | null
          cidade_id: string | null
          estado_id: number | null
        }
        Insert: {
          id: string
          user_id?: string | null
          nome: string
          email: string
          telefone: string
          idade?: number | null
          whatsapp?: string | null
          telegram?: string | null
          genero: string
          genitalia?: string | null
          preferencia_sexual?: string | null
          bairro?: string | null
          endereco?: string | null
          cep?: string | null
          peso?: number | null
          altura?: number | null
          manequim?: string | null
          etnia?: string | null
          cor_olhos?: string | null
          cor_cabelo?: string | null
          estilo_cabelo?: string | null
          tamanho_cabelo?: string | null
          tamanho_pe?: number | null
          busto?: number | null
          cintura?: number | null
          quadril?: number | null
          silicone?: boolean | null
          tatuagens?: boolean | null
          piercings?: boolean | null
          fumante?: boolean | null
          local_atendimento?: string[] | null
          formas_pagamento?: string[] | null
          horario_atendimento?: Json | null
          valor_padrao?: number | null
          valor_promocional?: number | null
          idiomas?: string[] | null
          especialidades?: string[] | null
          descricao?: string | null
          sobre_mim?: string | null
          instagram?: string | null
          twitter?: string | null
          tiktok?: string | null
          site?: string | null
          status?: "pendente" | "aprovado" | "rejeitado" | "bloqueado"
          verificado?: boolean | null
          destaque?: boolean | null
          destaque_ate?: string | null
          bloqueado?: boolean | null
          motivo_bloqueio?: string | null
          motivo_rejeicao?: string | null
          revisado_por?: number | null
          data_revisao?: string | null
          total_fotos?: number | null
          total_videos?: number | null
          total_documentos?: number | null
          created_at?: string
          updated_at?: string
          ultimo_login?: string | null
          ultima_atualizacao?: string | null
          cidade_id?: string | null
          estado_id?: number | null
        }
        Update: {
          id?: string
          user_id?: string | null
          nome?: string
          email?: string
          telefone?: string
          idade?: number | null
          whatsapp?: string | null
          telegram?: string | null
          genero?: string
          genitalia?: string | null
          preferencia_sexual?: string | null
          bairro?: string | null
          endereco?: string | null
          cep?: string | null
          peso?: number | null
          altura?: number | null
          manequim?: string | null
          etnia?: string | null
          cor_olhos?: string | null
          cor_cabelo?: string | null
          estilo_cabelo?: string | null
          tamanho_cabelo?: string | null
          tamanho_pe?: number | null
          busto?: number | null
          cintura?: number | null
          quadril?: number | null
          silicone?: boolean | null
          tatuagens?: boolean | null
          piercings?: boolean | null
          fumante?: boolean | null
          local_atendimento?: string[] | null
          formas_pagamento?: string[] | null
          horario_atendimento?: Json | null
          valor_padrao?: number | null
          valor_promocional?: number | null
          idiomas?: string[] | null
          especialidades?: string[] | null
          descricao?: string | null
          sobre_mim?: string | null
          instagram?: string | null
          twitter?: string | null
          tiktok?: string | null
          site?: string | null
          status?: "pendente" | "aprovado" | "rejeitado" | "bloqueado"
          verificado?: boolean | null
          destaque?: boolean | null
          destaque_ate?: string | null
          bloqueado?: boolean | null
          motivo_bloqueio?: string | null
          motivo_rejeicao?: string | null
          revisado_por?: number | null
          data_revisao?: string | null
          total_fotos?: number | null
          total_videos?: number | null
          total_documentos?: number | null
          created_at?: string
          updated_at?: string
          ultimo_login?: string | null
          ultima_atualizacao?: string | null
          cidade_id?: string | null
          estado_id?: number | null
        }
      }
      fotos: {
        Row: {
          id: string
          acompanhante_id: string
          url: string
          storage_path: string
          tipo: string
          principal: boolean | null
          aprovada: boolean | null
          created_at: string
          updated_at: string
        }
        Insert: {
          id?: string
          acompanhante_id: string
          url: string
          storage_path: string
          tipo: string
          principal?: boolean | null
          aprovada?: boolean | null
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          acompanhante_id?: string
          url?: string
          storage_path?: string
          tipo?: string
          principal?: boolean | null
          aprovada?: boolean | null
          created_at?: string
          updated_at?: string
        }
      }
      videos_verificacao: {
        Row: {
          id: string
          acompanhante_id: string
          url: string
          storage_path: string
          verificado: boolean | null
          created_at: string
          updated_at: string
        }
        Insert: {
          id?: string
          acompanhante_id: string
          url: string
          storage_path: string
          verificado?: boolean | null
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          acompanhante_id?: string
          url?: string
          storage_path?: string
          verificado?: boolean | null
          created_at?: string
          updated_at?: string
        }
      }
      documentos_acompanhante: {
        Row: {
          id: string
          acompanhante_id: string
          url: string
          storage_path: string
          tipo: string
          verificado: boolean | null
          created_at: string
          updated_at: string
        }
        Insert: {
          id?: string
          acompanhante_id: string
          url: string
          storage_path: string
          tipo: string
          verificado?: boolean | null
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          acompanhante_id?: string
          url?: string
          storage_path?: string
          tipo?: string
          verificado?: boolean | null
          created_at?: string
          updated_at?: string
        }
      }
      cidades: {
        Row: {
          id: string
          nome: string
          estado: string
          created_at: string
          estado_id: number | null
        }
        Insert: {
          id: string
          nome: string
          estado: string
          created_at?: string
          estado_id?: number | null
        }
        Update: {
          id?: string
          nome?: string
          estado?: string
          created_at?: string
          estado_id?: number | null
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