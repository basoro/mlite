FROM node:20-alpine

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application code
COPY . .

# Environment variables
ARG EXPO_PUBLIC_API_URL
ENV EXPO_PUBLIC_API_URL=$EXPO_PUBLIC_API_URL

# Disable interactive prompts and progress bars to save memory/logs
ENV CI=1
# Disable Expo telemetry
ENV EXPO_NO_TELEMETRY=1
# Set default port to 8080
ENV PORT=8080

# Expose port 8080
EXPOSE 8080

# Start Expo
# Railway will route the public domain to this port
CMD ["npx", "expo", "start", "--port", "8080"]
