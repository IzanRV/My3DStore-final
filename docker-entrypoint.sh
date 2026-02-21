#!/bin/bash
set -e
# Railway inyecta PORT; Apache debe escuchar en ese puerto
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
exec apache2-foreground
