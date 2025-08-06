#!/bin/bash

CONFIG_PATH="/etc/kolejka_gorska/ci.env.config"
ENVIRONMENT="dev"

# Domyślne wartości wagonu
ILOSC_MIEJSC=25
KOLOR="czerwony"
TYP="standard"
COASTER_ID=""

while getopts "e:i:m:c:t:" opt; do
	case $opt in
		e) ENVIRONMENT="$OPTARG" ;;
		i) COASTER_ID="$OPTARG" ;;
		m) ILOSC_MIEJSC="$OPTARG" ;;
		c) KOLOR="$OPTARG" ;;
		t) TYP="$OPTARG" ;;
		*) echo "Nieprawidłowy parametr"; exit 1 ;;
	esac
done

if [[ -z "$COASTER_ID" ]]; then
	echo "❌ Musisz podać ID kolejki za pomocą -i"
	exit 1
fi

get_config_value() {
	SECTION=$1
	KEY=$2
	awk -F '=' -v section="[$SECTION]" -v key="$KEY" '
		$0 == section { in_section=1; next }
		/^\[.*\]/ { in_section=0 }
		in_section && $1 ~ key { gsub(/^[ \t]+|[ \t]+$/, "", $2); print $2; exit }
	' "$CONFIG_PATH"
}

HOST=$(get_config_value "$ENVIRONMENT" "host")
PORT=$(get_config_value "$ENVIRONMENT" "port")

if [[ -z "$HOST" || -z "$PORT" ]]; then
	echo "Nie można znaleźć konfiguracji dla środowiska: $ENVIRONMENT"
	exit 1
fi

curl -X POST "http://$HOST:$PORT/api/coasters/$COASTER_ID/wagons" \
	-H "Content-Type: application/json" \
	-d "{
		\"ilosc_miejsc\": $ILOSC_MIEJSC
	}"