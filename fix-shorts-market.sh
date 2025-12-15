#!/bin/bash

echo "=== Shorts Market Database Fix Script ==="
echo "Connecting to server 115.91.5.140..."

# Stop wrangler processes
echo "Stopping wrangler processes..."
pkill -f "wrangler pages dev" || true

# Apply migrations to local D1 database
echo "Applying database migrations..."
cd ~/shorts-market

# Run migrations in order
for migration in migrations/*.sql; do
    echo "Applying $migration..."
    npx wrangler d1 execute shorts-market-db --local --file="$migration"
done

# Restart wrangler dev
echo "Starting wrangler dev..."
nohup npx wrangler pages dev dist --port 3003 --local-protocol http --d1 DB > wrangler-dev.log 2>&1 &

# Wait for startup
sleep 5

# Test the API
echo "Testing API..."
curl -s http://localhost:3003/api/shorts | jq '.'

echo "=== Fix Complete ==="
