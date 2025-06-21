/**
 * Validação de telefone no formato (XX) XXXXX-XXXX.
 * Retorna true se válido, false caso contrário.
 */
export const validarTelefone = (telefone: string): boolean => {
  // Remove tudo que não é número
  const numeros = telefone.replace(/\D/g, '');
  
  // Verifica se tem 11 dígitos (DDD + 9 + número)
  if (numeros.length !== 11) {
    return false;
  }
  
  // Verifica se o DDD é válido (códigos de 11 a 99)
  const ddd = parseInt(numeros.slice(0, 2));
  if (ddd < 11 || ddd > 99) {
    return false;
  }
  
  // Verifica se o número de celular começa com 9
  if (numeros[2] !== '9') {
    return false;
  }
  
  return true;
};

/**
 * Formata telefone para o padrão (XX) XXXXX-XXXX
 */
export const formatarTelefone = (telefone: string): string => {
  // Remove tudo que não é número
  const numeros = telefone.replace(/\D/g, '');
  
  // Se não tiver 11 dígitos, retorna vazio
  if (numeros.length !== 11) return '';
  
  // Formata o número
  return `(${numeros.slice(0,2)}) ${numeros.slice(2,7)}-${numeros.slice(7,11)}`;
};

/**
 * Verifica se uma string é um email válido
 */
export const validarEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

/**
 * Limita e sanitiza uma string
 */
export const sanitizeString = (str: string | null | undefined, maxLength: number = 2000): string => {
  if (!str) return '';
  return str.trim().slice(0, maxLength);
};

/**
 * Valida uma URL
 */
export const isValidUrl = (url: string): boolean => {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
};

/**
 * Valida se a URL é de uma foto válida
 */
export const validatePhoto = (url: string): boolean => {
  if (!isValidUrl(url)) return false;
  const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
  return imageExtensions.some(ext => url.toLowerCase().endsWith(ext));
}; 