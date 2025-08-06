#!/bin/bash

CONFIG_PATH="/etc/kolejka_gorska/ci.env.config"
ENVIRONMENT="dev"

# Domyślne wartości
LICZBA_PERSONELU=4
LICZBA_KLIENTOW=200
PREDKOSC_WAGONU=32.5
GODZINA_OD="09:00"
GODZINA_DO="17:00"

while getopts "e:p:k:s:f:t:" opt; do
	case $opt in
		e) ENVIRONMENT="$OPTARG" ;;
		p) LICZBA_PERSONELU="$OPTARG" ;;
		k) LICZBA_KLIENTOW="$OPTARG" ;;
		s) PREDKOSC_WAGONU="$OPTARG" ;;
		f) GODZINA_OD="$OPTARG" ;;
		t) GODZINA_DO="$OPTARG" ;;
		*) echo "Nieprawidłowy parametr"; exit 1 ;;
	esac
done

shift $((OPTIND - 1))

COASTER_ID="$1"
if [ -z "$COASTER_ID" ]; then
	echo "Użycie: ./update_queue.sh [opcje] <id_kolejki>"
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

curl -X PUT "http://$HOST:$PORT/api/coasters/$COASTER_ID" \
	-H "Content-Type: application/json" \
	-d "{
		\"liczba_personelu\": $LICZBA_PERSONELU,
		\"liczba_klientow\": $LICZBA_KLIENTOW,
		\"predkosc_wagonu\": $PREDKOSC_WAGONU,
		\"godzina_od\": \"$GODZINA_OD\",
		\"godzina_do\": \"$GODZINA_DO\"
	}"
