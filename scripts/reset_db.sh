#!/bin/bash

DB_HOST="127.0.0.1"
DUMP_FILE="tests/Support/Data/dump.sql"

source .env

echo "Resetting database..."

TABLES=$(mysql -h ${DB_HOST} -u ${DB_USERNAME} -p${DB_PASSWORD} -e "USE ${DB_DATABASE}; SHOW TABLES;" | grep -v "Tables_in_")

echo "SET FOREIGN_KEY_CHECKS=0;" > reset_db.sql

for TABLE in $TABLES; do
  echo "DROP TABLE \`$TABLE\`;" >> reset_db.sql
done

echo "SET FOREIGN_KEY_CHECKS=1;" >> reset_db.sql

echo "Dropping all tables..."
mysql -h ${DB_HOST} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} < reset_db.sql

echo "Importing data from ${DUMP_FILE}..."
mysql -h ${DB_HOST} -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} < ${DUMP_FILE}

echo "Database reset complete!"
rm reset_db.sql
