# Organigrama de la Empresa (PHP + MySQL)

Genera gráficamente el organigrama de una empresa a partir de una tabla
MySQL con un código de dependencia jerárquico de 10 posiciones, y permite
hacer clic en cada "raviol" (nodo) para ver el detalle de esa dependencia.

## Estructura de archivos

```
organigrama/
├── config.php        Datos de conexión a MySQL y definición del esquema del código
├── functions.php      Lógica: nivel, código padre, armado del árbol, render
├── index.php           Página principal: dibuja el organigrama
├── detalle.php          Página de detalle de una dependencia
├── assets/style.css   Estilos del organigrama (cajas + líneas conectoras)
└── sql/schema.sql     Script para crear la tabla y cargar datos de ejemplo
```

## Requisitos

- PHP 7.4 o superior (probado pensando en 8.x) con la extensión `pdo_mysql`.
- MySQL 5.7+ / MariaDB equivalente.
- Un servidor web (Apache, Nginx+PHP-FPM) o `php -S` para pruebas.

## Instalación

1. Cree la base y la tabla ejecutando `sql/schema.sql`:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
   (el script crea la tabla `dependencias` con `codigo_dependencia VARCHAR(10)`
   y `descripcion VARCHAR(120)`, más algunos registros de ejemplo).

2. Edite `config.php` con las credenciales reales de su MySQL
   (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).

3. Copie la carpeta `organigrama/` al document root de su servidor, o
   pruébelo rápido con el servidor embebido de PHP:
   ```bash
   php -S localhost:8000 -t organigrama
   ```
   y abra `http://localhost:8000/index.php`.

## Cómo se interpreta el código de 10 posiciones

Los dos ejemplos entregados:

```
A000000000  MINISTERIO DE ECONOMIA
A100000000  SECRETARIA DE POLITICA ECONOMICA
```

muestran un código de 1 letra + 9 dígitos, donde cada nivel jerárquico
ocupa un tramo (segmento) fijo de caracteres, y ese tramo vale "0" mientras
la dependencia no exista en ese nivel. El sistema implementado generaliza
esa idea con la constante `SEGMENT_LENGTHS` en `config.php`:

```php
define('SEGMENT_LENGTHS', [1, 1, 2, 2, 2, 2]); // suma = 10
```

Con ese reparto:

| Código        | Segmentos           | Nivel | Depende de   |
|---------------|---------------------|-------|--------------|
| A000000000    | A·0·00·00·00·00     | 1     | —            |
| A100000000    | A·1·00·00·00·00     | 2     | A000000000   |
| A110000000    | A·1·10·00·00·00     | 3     | A100000000   |
| A110100000    | A·1·10·10·00·00     | 4     | A110000000   |

Regla general:

- **Nivel** de un código = posición del último segmento distinto de cero.
- **Código padre** = el mismo código con ese último segmento en ceros.

Esto evita tener que guardar una columna de "código padre" en la tabla:
se calcula siempre a partir del propio código de 10 posiciones.

> Si el esquema real de su organización reparte los dígitos de otra forma
> (por ejemplo 2+2+2+2+2, o 1+3+3+3), solo hay que ajustar el arreglo
> `SEGMENT_LENGTHS` en `config.php` — el resto del sistema no necesita
> cambios, siempre que los valores sumen 10.

## Funcionamiento

- **`index.php`** trae todas las dependencias, arma el árbol en PHP
  (`construirArbol()`) y lo dibuja como una lista `<ul>/<li>` anidada;
  el efecto de organigrama (cajas unidas por líneas) se logra con CSS puro
  en `assets/style.css`, sin JavaScript ni librerías externas.
- Cada nodo es un enlace `detalle.php?codigo=XXXXXXXXXX`.
- **`detalle.php`** valida el código recibido, busca la dependencia, calcula
  su nivel y su padre (mostrando la descripción del padre si está cargado
  en la tabla) y lista las dependencias que reportan directamente a ella.

## Seguridad

- Todas las consultas usan sentencias preparadas (PDO) — no hay
  concatenación de SQL con datos de entrada.
- El código recibido por GET se valida contra el formato esperado antes de
  usarse en cualquier consulta.
- Toda salida a HTML pasa por `htmlspecialchars()`.
