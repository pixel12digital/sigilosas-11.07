/**
 * Validação de telefone no formato (XX) XXXXX-XXXX
 */
export const validarTelefone = (telefone: string): string => {
  // Remove tudo que não é número
  const numeros = telefone.replace(/\D/g, '');
  
  // Verifica se tem 11 dígitos
  if (numeros.length !== 11) {
    return 'O telefone deve ter 11 dígitos';
  }
  
  // Verifica se o DDD é válido (10-99)
  const ddd = parseInt(numeros.slice(0, 2));
  if (ddd < 10 || ddd > 99) {
    return 'DDD inválido';
  }
  
  // Verifica se o primeiro dígito do número é válido (9)
  if (numeros[2] !== '9') {
    return 'O número deve começar com 9';
  }
  
  return '';
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