#!/bin/bash

CONFIG_FILE="/etc/kolejka_gorska/ci.env.config"

# 🔍 Walidacja parametru
if [[ "$1" != "prod" && "$1" != "dev" ]]; then
	echo "Użycie: $0 [prod|dev]"
	exit 1
fi

ENV="$1"
ENVIRONMENT=$([[ "$ENV" == "prod" ]] && echo "production" || echo "development")
PID_FILE="/tmp/codeigniter.$ENV.pid"

# 🔐 Sprawdzenie, czy proces już działa
if [[ -f "$PID_FILE" ]]; then
	PID=$(cat "$PID_FILE")
	if ps -p "$PID" > /dev/null 2>&1; then
		echo "❗ Serwer ($ENVIRONMENT) już działa (PID $PID)"
		exit 1
	else
		echo "⚠️  Znaleziono nieaktywny PID – czyszczę $PID_FILE"
		rm "$PID_FILE"
	fi
fi

HOST=$(awk -F "=" -v env="[$ENV]" '$0 == env {found=1} found && $1 ~ /host/ {gsub(/ /,"",$2); print $2; exit}' "$CONFIG_FILE")
PORT=$(awk -F "=" -v env="[$ENV]" '$0 == env {found=1} found && $1 ~ /port/ {gsub(/ /,"",$2); print $2; exit}' "$CONFIG_FILE")


if [[ -z "$HOST" || -z "$PORT" ]]; then
	echo "Błąd: Nie znaleziono konfiguracji dla środowiska $ENV"
	exit 1
fi

cd ../
sudo sh -c "CI_ENVIRONMENT=$ENVIRONMENT php spark serve --host $HOST --port $PORT > /dev/null 2>&1 & echo \$! > $PID_FILE"

echo "CodeIgniter ($ENVIRONMENT) uruchomiony na http://$HOST:$PORT (PID zapisany w $PID_FILE)"
