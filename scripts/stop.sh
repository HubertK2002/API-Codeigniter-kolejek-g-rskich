#!/bin/bash

if [[ "$1" != "prod" && "$1" != "dev" ]]; then
	echo "Użycie: $0 [prod|dev]"
	exit 1
fi

PID_FILE="/tmp/codeigniter.$1.pid"

if [[ -f "$PID_FILE" ]]; then
	PID=$(cat "$PID_FILE")
	if kill "$PID" > /dev/null 2>&1; then
		echo "Zatrzymano CodeIgniter ($1) – PID $PID"
		rm "$PID_FILE"
	else
		echo "Nie udało się zabić procesu (PID $PID)"
	fi
else
	echo "Brak pliku PID: $PID_FILE – serwer $1 prawdopodobnie nie działa"
fi

