#!/bin/bash
# production_setup.sh - configure .env for saltenas deployment
# This script should be run on the production server in the project root (/home/sites/22b/7/7d0b238556/public_html/saltenas)

ENV_FILE=".env"
APP_URL="https://saltenas.alloggibolivia.com"

if [ -f "$ENV_FILE" ]; then
  echo "Updating APP_URL in $ENV_FILE..."
  # If APP_URL line exists, replace it; otherwise add it.
  if grep -q '^APP_URL=' "$ENV_FILE"; then
    sed -i.bak "s|^APP_URL=.*|APP_URL=$APP_URL|" "$ENV_FILE"
  else
    echo "APP_URL=$APP_URL" >> "$ENV_FILE"
  fi
  echo "Done."
else
  echo "$ENV_FILE not found. Creating with default values."
  cat <<EOF > $ENV_FILE
APP_NAME=Saltenas
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_ON_SERVER
APP_DEBUG=false
APP_URL=$APP_URL

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_user
DB_PASSWORD=your_password
EOF
  echo "Created $ENV_FILE. Please edit DB credentials and generate APP_KEY with 'php artisan key:generate'."
fi

# Ensure correct permissions (web server user typically www-data on Linux)
chown www-data:www-data $ENV_FILE $ENV_FILE.bak 2>/dev/null || true
chmod 640 $ENV_FILE
