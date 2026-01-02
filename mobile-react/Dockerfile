FROM node:20-alpine

WORKDIR /app

COPY package*.json ./

# Install all dependencies (including devDependencies for TypeScript)
RUN npm install

COPY . .

ARG EXPO_PUBLIC_API_URL
ENV EXPO_PUBLIC_API_URL=$EXPO_PUBLIC_API_URL

ENV CI=1
ENV EXPO_NO_TELEMETRY=1
ENV PORT=8080

# RAM SAFE
ENV NODE_OPTIONS="--max-old-space-size=256"

EXPOSE 8080

CMD ["npx", "expo", "start", "--no-dev", "--minify", "--port", "8080"]
