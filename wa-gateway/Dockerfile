FROM node:20-slim

# Install Nginx and build dependencies for better-sqlite3
RUN apt-get update && apt-get install -y \
    nginx \
    gettext-base \
    python3 \
    make \
    g++ \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci

# Copy source code
COPY . .

# Copy nginx config (it will be processed by start.sh)
COPY nginx.conf /app/nginx.conf

# Make start script executable
RUN chmod +x start.sh

# Create directory for auth persistence if needed
RUN mkdir -p wa-gateway/auth

# Expose the port (Railway will override this but good for documentation)
EXPOSE 8080

# Start command
CMD ["./start.sh"]
