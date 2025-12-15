#!/bin/bash

DB_FILE="$1"
echo "Creating database: $DB_FILE"

# Apply all migrations in order
for migration in migrations/*.sql; do
    echo "Applying: $migration"
    sqlite3 "$DB_FILE" < "$migration"
done

# Apply seed data
echo "Applying seed data..."
sqlite3 "$DB_FILE" < seed.sql

echo "✅ Database created successfully!"
echo ""
echo "=== Tables ==="
sqlite3 "$DB_FILE" ".tables"
echo ""
echo "=== Users ==="
sqlite3 "$DB_FILE" "SELECT id, email, name, role FROM users;"
echo ""
echo "=== Creators ==="
sqlite3 "$DB_FILE" "SELECT id, user_id, youtube_channel_name, subtag, is_approved FROM creators;"
