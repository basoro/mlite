#!/bin/sh

# Set default PORT if not set (for local testing)
export PORT=${PORT:-8080}

# Replace ${PORT} in nginx.conf
envsubst '${PORT}' < /app/nginx.conf > /etc/nginx/nginx.conf

# Start Node.js app on port 4000 (internal)
# We override PORT to 4000 for the node app so it doesn't conflict with Nginx
PORT=4000 node src/server.js &

# Start Nginx in foreground
nginx -g 'daemon off;'
