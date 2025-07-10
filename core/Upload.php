<?php
/**
 * Classe de Upload de Arquivos
 * Arquivo: core/Upload.php
 */

require_once __DIR__ . '/../config/database.php';

class Upload {
    private $db;
    private $uploadDir;
    private $allowedTypes;
    private $maxSize;
    
    public function __construct($type = 'image') {
        $this->db = getDB();
        $this->uploadDir = UPLOAD_DIR;
        $this->maxSize = MAX_FILE_SIZE;
        
        if ($type === 'image') {
            $this->allowedTypes = ALLOWED_IMAGE_TYPES;
            $this->uploadDir .= 'fotos/';
        } elseif ($type === 'document') {
            $this->allowedTypes = ALLOWED_DOC_TYPES;
            $this->uploadDir .= 'documentos/';
        } elseif ($type === 'video') {
            $this->allowedTypes = ['mp4', 'avi', 'mov', 'wmv'];
            $this->uploadDir .= 'videos/';
        }
        
        // Criar diretório se não existir
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload de arquivo
     */
    public function uploadFile($file, $acompanhanteId = null, $tipo = 'foto') {
        // Validar arquivo
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Gerar nome único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        
        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => false,
                'message' => 'Erro ao mover arquivo'
            ];
        }
        
        // Salvar no banco se acompanhante_id fornecido
        if ($acompanhanteId) {
            $data = [
                'acompanhante_id' => $acompanhanteId,
                'url' => '/uploads/' . basename($this->uploadDir) . '/' . $filename,
                'nome_arquivo' => $filename,
                'tipo' => $tipo,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $table = ($tipo === 'documento') ? 'documentos' : 'fotos';
            $id = $this->db->insert($table, $data);
            
            return [
                'success' => true,
                'id' => $id,
                'filename' => $filename,
                'url' => $data['url']
            ];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'url' => '/uploads/' . basename($this->uploadDir) . '/' . $filename
        ];
    }
    
    /**
     * Upload múltiplo
     */
    public function uploadMultiple($files, $acompanhanteId = null, $tipo = 'foto') {
        $results = [];
        
        foreach ($files as $file) {
            $result = $this->uploadFile($file, $acompanhanteId, $tipo);
            $results[] = $result;
        }
        
        return $results;
    }
    
    /**
     * Validar arquivo
     */
    private function validateFile($file) {
        // Verificar se há erro
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Erro no upload: ' . $this->getUploadErrorMessage($file['error'])
            ];
        }
        
        // Verificar tamanho
        if ($file['size'] > $this->maxSize) {
            return [
                'success' => false,
                'message' => 'Arquivo muito grande. Máximo: ' . ($this->maxSize / 1024 / 1024) . 'MB'
            ];
        }
        
        // Verificar tipo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Tipo de arquivo não permitido. Permitidos: ' . implode(', ', $this->allowedTypes)
            ];
        }
        
        // Verificar se é realmente uma imagem (para tipos de imagem)
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return [
                    'success' => false,
                    'message' => 'Arquivo não é uma imagem válida'
                ];
            }
        }
        
        return ['success' => true];
    }
    
    /**
     * Obter mensagem de erro do upload
     */
    private function getUploadErrorMessage($error) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Arquivo excede o tamanho máximo permitido pelo servidor';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Arquivo excede o tamanho máximo permitido pelo formulário';
            case UPLOAD_ERR_PARTIAL:
                return 'Upload foi feito parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'Nenhum arquivo foi enviado';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta pasta temporária';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Falha ao escrever arquivo no disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload parado por extensão';
            default:
                return 'Erro desconhecido';
        }
    }
    
    /**
     * Deletar arquivo
     */
    public function deleteFile($filename, $table = 'fotos') {
        // Buscar no banco
        $file = $this->db->fetch(
            "SELECT * FROM $table WHERE nome_arquivo = ?",
            [$filename]
        );
        
        if (!$file) {
            return [
                'success' => false,
                'message' => 'Arquivo não encontrado'
            ];
        }
        
        // Deletar arquivo físico
        $filepath = $this->uploadDir . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Deletar do banco
        $this->db->delete($table, 'id = ?', [$file['id']]);
        
        return ['success' => true];
    }
    
    /**
     * Obter arquivos de uma acompanhante
     */
    public function getFilesByAcompanhante($acompanhanteId, $tipo = 'foto') {
        $table = ($tipo === 'documento') ? 'documentos' : 'fotos';
        
        return $this->db->fetchAll(
            "SELECT * FROM $table WHERE acompanhante_id = ? ORDER BY created_at DESC",
            [$acompanhanteId]
        );
    }
    
    /**
     * Redimensionar imagem
     */
    public function resizeImage($filepath, $maxWidth = 800, $maxHeight = 600) {
        $imageInfo = getimagesize($filepath);
        if ($imageInfo === false) {
            return false;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // Calcular novas dimensões
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;
        
        // Criar nova imagem
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Carregar imagem original
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            default:
                return false;
        }
        
        // Redimensionar
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Salvar
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $filepath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $filepath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $filepath);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($newImage);
        
        return true;
    }
}

// Função helper para obter instância
function getUpload($type = 'image') {
    return new Upload($type);
}
?> 