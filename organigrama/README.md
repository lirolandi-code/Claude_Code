# Organigrama de la Empresa (PHP + MySQL)

Genera gráficamente el organigrama de una empresa a partir de una tabla
MySQL con un código de dependencia jerárquico de 10 posiciones, y permite
hacer clic en cada "raviol" (nodo) para ver el detalle de esa dependencia.

Base de datos: `milegajo` · Tabla: `sv_dpnpdat0` · Campos: `DPNDEP`
(código de dependencia, VARCHAR(10)) y `DPNDES` (descripción, VARCHAR(120)).

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

- **PHP 5.1 o superior**, con la extensión `pdo_mysql`. El código está
  escrito deliberadamente sin funciones anónimas, sin type hints
  escalares/de retorno, sin `declare(strict_types)` y sin sintaxis corta
  de arrays, para poder correr en hostings que no permiten elegir una
  versión de PHP más moderna. Si tu hosting sí soporta PHP 7.4+, el
  código funciona igual sin cambios.
- MySQL 5.x / MariaDB equivalente.
- Un servidor web (Apache, Nginx+PHP-FPM) o `php -S` para pruebas.
- El charset de conexión es `utf8` (no `utf8mb4`) por compatibilidad con
  clientes MySQL antiguos; si tu servidor es moderno y necesitás emojis
  o caracteres de 4 bytes, podés cambiar `DB_CHARSET` a `utf8mb4` en
  `config.php`.

## Instalación

1. Cree la base y la tabla ejecutando `sql/schema.sql`:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
   (el script crea la base `milegajo` y la tabla `sv_dpnpdat0` con
   `DPNDEP VARCHAR(10)` y `DPNDES VARCHAR(120)`, más algunos registros
   de ejemplo).

2. Edite `config.php` con las credenciales reales de su MySQL
   (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`). Si la base ya existe con
   otro nombre de tabla o de campos, ajuste `TABLA_DEPENDENCIAS`,
   `CAMPO_CODIGO` y `CAMPO_DESCRIPCION` en el mismo archivo.

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

Con ese reparto, validado contra un caso real de 6 niveles:

| Código        | Segmentos             | Nivel | Dependencia                                    | Depende de   |
|---------------|------------------------|-------|-------------------------------------------------|--------------|
| A000000000    | A·0·00·00·00·00        | 1     | MINISTERIO DE ECONOMIA                          | —            |
| A100000000    | A·1·00·00·00·00        | 2     | SECRETARIA DE POLITICA ECONOMICA                | A000000000   |
| A102000000    | A·1·02·00·00·00        | 3     | SUBSECRETARIA DE PROGRAMACION MACROECONOMICA    | A100000000   |
| A102020000    | A·1·02·02·00·00        | 4     | DIRECCION NACIONAL DE POLITICA MACROECONOMICA   | A102000000   |
| A102020100    | A·1·02·02·01·00        | 5     | DIRECCION DE ANALISIS DE ACTIVIDAD ECONOMICA... | A102020000   |
| A102020101    | A·1·02·02·01·01        | 6     | COORDINACION AREA ECONOMIA GENERAL              | A102020100   |

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
- Cada raviol tiene dos zonas: el cuerpo (código + descripción) enlaza a
  `detalle.php?codigo=XXXXXXXXXX`, y el botón inferior "Ver ficha →" enlaza
  a `URL_DETALLE_EXTERNA` (configurable en `config.php`) agregándole
  `?Dep=XXXXXXXXXX` (o `&Dep=...` si esa URL ya trae parámetros).
- **`detalle.php`** valida el código recibido, busca la dependencia, calcula
  su nivel y su padre (mostrando la descripción del padre si está cargado
  en la tabla) y lista las dependencias que reportan directamente a ella.

## Filtrar por prefijo de código

`PREFIJO_CODIGO_FILTRO` en `config.php` limita qué dependencias se
muestran/aceptan según el primer carácter (o caracteres) del código —
por ejemplo `'A'` para mostrar solo lo que cuelga del ministerio "A".
Se aplica tanto al listado del organigrama como a `detalle.php` (un
código fuera del prefijo da 404, aunque exista en la tabla). Dejar la
constante en `''` para no filtrar nada.

## Árbol plegable (niveles ya desplegados)

Los nodos con hijos se dibujan con `<details>/<summary>` nativos de
HTML: no hace falta JavaScript para plegar/desplegar una rama, alcanza
con hacer clic en el circulito +/- debajo de cada raviol. Al entrar a
`index.php`, los niveles hasta `NIVELES_EXPANDIDOS_POR_DEFECTO` (en
`config.php`, por defecto `4`) arrancan desplegados; los niveles más
profundos arrancan plegados y se despliegan con un clic.

## Mostrar solo dependencias con personal activo

`FILTRAR_SOLO_ACTIVAS` en `config.php` (por defecto `true`) limita el
organigrama a las dependencias cuyo código aparece en el resultado de
`SQL_CODIGOS_ACTIVOS` (la subconsulta de personal activo). Poner esa
constante en `false` para desactivar el filtro y mostrar todas las
dependencias, sin tocar nada más.

El criterio es estricto: se muestra un código si (a) aparece en el
resultado de `SQL_CODIGOS_ACTIVOS`, o (b) es un ancestro real (padre,
abuelo, etc., calculado a partir del propio código de 10 posiciones) de
algún código que aparece ahí — esto último solo para que el árbol llegue
conectado hasta la raíz, no se agrega ninguna otra dependencia. Una
subsecretaría sin personal activo y sin ningún descendiente activo no se
muestra, aunque otra dependencia de la misma secretaría sí tenga gente
asignada. Los ancestros incluidos solo por conectividad (sin personal
propio) se marcan con borde punteado y algo más tenues (clase CSS
`sin-personal`) para distinguirlos de las unidades con personal
asignado directamente.

Si el ancestro directo de una dependencia llegara a faltar por completo
de la tabla (una fila que directamente no existe), se cuelga del
ancestro visible más cercano en vez de quedar como si fuera de nivel 1;
la página de detalle usa el mismo criterio para "Depende de" y para el
listado de dependencias subordinadas, que además se muestra ordenado
por nivel jerárquico (las subsecretarías antes que las direcciones, por
ejemplo) y no por orden alfabético/numérico de código.

El árbol se arma sin usar referencias de PHP (`&`) en ningún punto:
ordenar (`usort`) un array que contiene referencias es una combinación
que puede perder o corromper elementos según la versión de PHP, algo
que conviene evitar del todo en un proyecto pensado para poder correr
desde PHP 5.1.

## Seguridad

- Todas las consultas usan sentencias preparadas (PDO) — no hay
  concatenación de SQL con datos de entrada.
- El código recibido por GET se valida contra el formato esperado antes de
  usarse en cualquier consulta.
- Toda salida a HTML pasa por `htmlspecialchars()`.
