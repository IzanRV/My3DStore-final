#!/bin/bash
set -e
# Un solo MPM (evitar "More than one MPM loaded") — se aplica en cada arranque
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf 2>/dev/null || true
a2enmod -q mpm_prefork 2>/dev/null || true
# Railway inyecta PORT; Apache debe escuchar en ese puerto
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
exec apache2-foreground
