#!/bin/bash

CONFIG_PATH="../../config"
ENVIRONMENT="dev"

#DEFAULT
LICZBA_PERSONELU=4
LICZBA_KLIENTOW=200
DL_TRASY=1200
PREDKOSC_WAGONU=32.5
GODZINA_OD="09:00"
GODZINA_DO="17:00"

while getopts "e:p:k:d:s:f:t:" opt; do
	case $opt in
		e) ENVIRONMENT="$OPTARG" ;;
		p) LICZBA_PERSONELU="$OPTARG" ;;
		k) LICZBA_KLIENTOW="$OPTARG" ;;
		d) DL_TRASY="$OPTARG" ;;
		s) PREDKOSC_WAGONU="$OPTARG" ;;
		f) GODZINA_OD="$OPTARG" ;;
		t) GODZINA_DO="$OPTARG" ;;
		*) echo "Nieprawidłowy parametr"; exit 1 ;;
	esac
done

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

curl -X POST "http://$HOST:$PORT/api/coasters" \
	-H "Content-Type: application/json" \
	-d "{
		\"liczba_personelu\": $LICZBA_PERSONELU,
		\"liczba_klientow\": $LICZBA_KLIENTOW,
		\"dl_trasy\": $DL_TRASY,
		\"predkosc_wagonu\": $PREDKOSC_WAGONU,
		\"godzina_od\": \"$GODZINA_OD\",
		\"godzina_do\": \"$GODZINA_DO\"
	}"
echo ""
