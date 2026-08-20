-- Esquema y datos de ejemplo para el organigrama
-- Codificación de dependencias: 10 posiciones (1 letra + 9 dígitos)

CREATE TABLE IF NOT EXISTS dependencias (
    codigo_dependencia VARCHAR(10) NOT NULL,
    descripcion        VARCHAR(120) NOT NULL,
    PRIMARY KEY (codigo_dependencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo (4 niveles jerárquicos, dos ministerios)
INSERT INTO dependencias (codigo_dependencia, descripcion) VALUES
('A000000000', 'MINISTERIO DE ECONOMIA'),
('A100000000', 'SECRETARIA DE POLITICA ECONOMICA'),
('A110000000', 'SUBSECRETARIA DE PROGRAMACION MACROECONOMICA'),
('A110100000', 'DIRECCION NACIONAL DE PROGRAMACION MACROECONOMICA'),
('A120000000', 'SUBSECRETARIA DE FINANCIAMIENTO'),
('A200000000', 'SECRETARIA DE HACIENDA'),
('B000000000', 'MINISTERIO DE JUSTICIA'),
('B100000000', 'SECRETARIA DE JUSTICIA')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);
