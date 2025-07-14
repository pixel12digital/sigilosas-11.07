CREATE TABLE videos_publicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acompanhante_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    titulo VARCHAR(100) DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    status ENUM('pendente','aprovado','rejeitado') DEFAULT 'pendente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (acompanhante_id) REFERENCES acompanhantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 