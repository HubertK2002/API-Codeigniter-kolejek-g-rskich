#!/bin/bash

if [[ "$1" != "prod" && "$1" != "dev" ]]; then
	echo "Użycie: $0 [prod|dev]"
	exit 1
fi

PID_FILE="/tmp/codeigniter.$1.pid"
PORT_FILE="/etc/kolejka_gorska/ci.env.config"

PORT=$(awk -F "=" -v env="[$1]" '$0 == env {found=1} found && $1 ~ /port/ {gsub(/ /,"",$2); print $2; exit}' "$PORT_FILE")

PID=$(lsof -ti:$PORT)

if [ -z "$PID" ]; then
    echo "Nie znaleziono procesu dla portu $PORT"
    exit 1
else
	kill -SIGINT $PID
	rm $PID_FILE
fi

