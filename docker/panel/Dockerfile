FROM python:3.11-slim

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    docker.io \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements and install Python dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application code
COPY . .

# Create nginx conf directory
RUN mkdir -p /etc/nginx/conf.d

# Expose port
EXPOSE 5000

# Run the application
CMD ["python", "app.py"]