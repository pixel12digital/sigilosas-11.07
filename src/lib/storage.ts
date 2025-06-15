import { supabase } from './supabase';

export interface UploadResult {
  success: boolean;
  url?: string;
  error?: string;
}

export class StorageManager {
  private static instance: StorageManager;
  
  private constructor() {}
  
  public static getInstance(): StorageManager {
    if (!StorageManager.instance) {
      StorageManager.instance = new StorageManager();
    }
    return StorageManager.instance;
  }

  /**
   * Upload de imagem para o bucket 'images'
   */
  async uploadImage(
    file: File, 
    path: string, 
    options?: { 
      resize?: { width: number; height: number };
      quality?: number;
    }
  ): Promise<UploadResult> {
    try {
      // Validar tipo de arquivo
      if (!file.type.startsWith('image/')) {
        return {
          success: false,
          error: 'Arquivo deve ser uma imagem'
        };
      }

      // Validar tamanho (máximo 5MB)
      if (file.size > 5 * 1024 * 1024) {
        return {
          success: false,
          error: 'Arquivo muito grande (máximo 5MB)'
        };
      }

      // Upload para o Supabase Storage
      const { data, error } = await supabase.storage
        .from('images')
        .upload(path, file, {
          cacheControl: '3600',
          upsert: true
        });

      if (error) {
        return {
          success: false,
          error: error.message
        };
      }

      // Gerar URL pública
      const { data: urlData } = supabase.storage
        .from('images')
        .getPublicUrl(data.path);

      return {
        success: true,
        url: urlData.publicUrl
      };

    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Erro desconhecido'
      };
    }
  }

  /**
   * Upload de documento para o bucket 'documents'
   */
  async uploadDocument(
    file: File, 
    path: string
  ): Promise<UploadResult> {
    try {
      // Validar tipo de arquivo
      const allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg'
      ];

      if (!allowedTypes.includes(file.type)) {
        return {
          success: false,
          error: 'Tipo de arquivo não permitido'
        };
      }

      // Validar tamanho (máximo 10MB)
      if (file.size > 10 * 1024 * 1024) {
        return {
          success: false,
          error: 'Arquivo muito grande (máximo 10MB)'
        };
      }

      const { data, error } = await supabase.storage
        .from('documents')
        .upload(path, file, {
          cacheControl: '3600',
          upsert: true
        });

      if (error) {
        return {
          success: false,
          error: error.message
        };
      }

      // Para documentos, retornar URL privada
      const { data: urlData } = supabase.storage
        .from('documents')
        .getPublicUrl(data.path);

      return {
        success: true,
        url: urlData.publicUrl
      };

    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Erro desconhecido'
      };
    }
  }

  /**
   * Upload de vídeo para o bucket 'videos'
   */
  async uploadVideo(
    file: File, 
    path: string
  ): Promise<UploadResult> {
    try {
      // Validar tipo de arquivo
      if (!file.type.startsWith('video/')) {
        return {
          success: false,
          error: 'Arquivo deve ser um vídeo'
        };
      }

      // Validar tamanho (máximo 100MB)
      if (file.size > 100 * 1024 * 1024) {
        return {
          success: false,
          error: 'Arquivo muito grande (máximo 100MB)'
        };
      }

      const { data, error } = await supabase.storage
        .from('videos')
        .upload(path, file, {
          cacheControl: '3600',
          upsert: true
        });

      if (error) {
        return {
          success: false,
          error: error.message
        };
      }

      const { data: urlData } = supabase.storage
        .from('videos')
        .getPublicUrl(data.path);

      return {
        success: true,
        url: urlData.publicUrl
      };

    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Erro desconhecido'
      };
    }
  }

  /**
   * Deletar arquivo do storage
   */
  async deleteFile(
    bucket: 'images' | 'documents' | 'videos',
    path: string
  ): Promise<{ success: boolean; error?: string }> {
    try {
      const { error } = await supabase.storage
        .from(bucket)
        .remove([path]);

      if (error) {
        return {
          success: false,
          error: error.message
        };
      }

      return { success: true };

    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Erro desconhecido'
      };
    }
  }

  /**
   * Gerar nome único para arquivo
   */
  generateUniqueFileName(originalName: string, userId?: string): string {
    const timestamp = Date.now();
    const random = Math.random().toString(36).substring(2, 15);
    const extension = originalName.split('.').pop();
    const prefix = userId ? `${userId}/` : '';
    
    return `${prefix}${timestamp}_${random}.${extension}`;
  }

  /**
   * Obter URL pública de uma imagem
   */
  getPublicUrl(bucket: string, path: string): string {
    const { data } = supabase.storage
      .from(bucket)
      .getPublicUrl(path);
    
    return data.publicUrl;
  }
}

// Exportar instância singleton
export const storageManager = StorageManager.getInstance(); 