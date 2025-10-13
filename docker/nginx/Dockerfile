FROM nginx:latest

# Install zip and unzip in the Nginx container
RUN apt-get update && \
    apt-get install -y zip unzip && \
    rm -rf /var/lib/apt/lists/*