#!/bin/bash
# Descarga Chart.js para uso offline (sin CDN).
# Ejecutar una sola vez en el servidor.

DEST="$(dirname "$0")/chart.min.js"

echo "Descargando Chart.js..."
curl -fsSL "https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" -o "$DEST"

if [ $? -eq 0 ]; then
  echo "OK: Chart.js guardado en $DEST"
else
  echo "ERROR: no se pudo descargar. Descargá manualmente:"
  echo "  https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"
  echo "y guardalo como chart.min.js en la misma carpeta que los archivos PHP."
fi
