<?php
/**
 * ejemplo_boton.php
 * Fragmento de ejemplo: cómo agregar el botón "Generar Gráfico"
 * a la pantalla de descarga existente de tu sistema.
 *
 * Buscá en tu código el lugar donde generás los botones
 * "Descargar CSV", "Descargar PDF" y "Ver en pantalla",
 * y agregá el botón de abajo junto a ellos.
 *
 * $csv_path debe contener la ruta absoluta o relativa al archivo
 * CSV generado por el informe.
 */

// Ejemplo: $csv_path ya existe en tu código como la ruta del CSV generado
$csv_path = '/var/www/html/rrhh/temp/informe_20240115.csv'; // ejemplo

// Ruta relativa o absoluta a la carpeta donde instalaste chart_config.php
$charts_url = '/rrhh/charts/chart_config.php';
?>

<!-- ===== BOTÓN A AGREGAR EN TU PANTALLA DE DESCARGA ===== -->
<a href="<?= htmlspecialchars($charts_url) ?>?csv=<?= urlencode($csv_path) ?>"
   class="btn btn-grafico"
   title="Generar gráfico a partir de este informe">
  Generar Gráfico
</a>

<!-- CSS sugerido (adaptá al estilo de tu sistema) -->
<style>
.btn-grafico {
  display: inline-block;
  padding: 8px 18px;
  background-color: #8e44ad;
  color: #ffffff;
  text-decoration: none;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: bold;
}
.btn-grafico:hover {
  background-color: #7d3c98;
}
</style>
<!-- ===== FIN DEL FRAGMENTO ===== -->
