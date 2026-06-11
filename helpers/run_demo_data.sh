#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   bash helpers/run_demo_data.sh <mysql_user> <database_name> [mysql_host] [mysql_port]
# Example:
#   bash helpers/run_demo_data.sh root erpaccount_db

if [[ $# -lt 2 ]]; then
  echo "Usage: bash helpers/run_demo_data.sh <mysql_user> <database_name> [mysql_host] [mysql_port]"
  exit 1
fi

MYSQL_USER="$1"
DB_NAME="$2"
MYSQL_HOST="${3:-127.0.0.1}"
MYSQL_PORT="${4:-3306}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MASTER_SQL="$SCRIPT_DIR/00_run_all_demo_data.sql"

if [[ ! -f "$MASTER_SQL" ]]; then
  echo "Master SQL file not found: $MASTER_SQL"
  exit 1
fi

echo "Running demo data SQL on database '$DB_NAME' as user '$MYSQL_USER' ..."
mysql \
  --host="$MYSQL_HOST" \
  --port="$MYSQL_PORT" \
  --user="$MYSQL_USER" \
  --password \
  --database="$DB_NAME" \
  < "$MASTER_SQL"

echo "Demo data import completed successfully."
