FROM nginx:latest

# Install build dependencies for ModSecurity and NGINX
RUN apt-get update && apt-get install -y \
    bison \
    build-essential \
    ca-certificates \
    curl \
    dh-autoreconf \
    doxygen \
    git \
    libcurl4-openssl-dev \
    liblmdb-dev \
    libxml2-dev \
    libyajl-dev \
    pkg-config \
    wget \
    zlib1g-dev \
    libpcre2-dev \
    libtool \
    automake \
    autoconf \
    && rm -rf /var/lib/apt/lists/*

# Build and install libmodsecurity (ModSecurity v3)
RUN set -eux; \
    cd /tmp; \
    git clone --depth 1 https://github.com/owasp-modsecurity/ModSecurity; \
    cd ModSecurity; \
    git submodule init; \
    git submodule update; \
    ./build.sh; \
    ./configure; \
    make; \
    make install; \
    ldconfig

# Download ModSecurity NGINX connector
RUN set -eux; \
    cd /tmp; \
    git clone --depth 1 https://github.com/SpiderLabs/ModSecurity-nginx.git

# Build dynamic module against the exact NGINX version from the base image
RUN set -eux; \
    ver=$(nginx -v 2>&1 | sed -E 's#.*nginx/([0-9.]+).*#\1#'); \
    cd /tmp; \
    wget "https://nginx.org/download/nginx-${ver}.tar.gz"; \
    tar -xzf "nginx-${ver}.tar.gz"; \
    cd "nginx-${ver}"; \
    ./configure --with-compat --add-dynamic-module=/tmp/ModSecurity-nginx; \
    make modules; \
    cp objs/ngx_http_modsecurity_module.so /etc/nginx/modules/

# Configure ModSecurity and OWASP CRS
RUN set -eux; \
    mkdir -p /etc/nginx/modsec /var/log/modsecurity; \
    cd /etc/nginx/modsec; \
    git clone --depth 1 https://github.com/coreruleset/coreruleset owasp-crs; \
    cp owasp-crs/crs-setup.conf.example owasp-crs/crs-setup.conf; \
    sed -i 's/^\s*SecDefaultAction/# SecDefaultAction (disabled by image build)/' /etc/nginx/modsec/owasp-crs/crs-setup.conf; \
    printf '%s\n' \
      'SecRuleEngine On' \
      'SecRequestBodyAccess On' \
      'SecResponseBodyAccess Off' \
      'SecAuditEngine RelevantOnly' \
      'SecAuditLog /var/log/modsecurity/audit.log' \
      'Include /etc/nginx/modsec/modsecurity.conf' \
      'Include /etc/nginx/modsec/owasp-crs/crs-setup.conf' \
      'Include /etc/nginx/modsec/owasp-crs/rules/*.conf' \
      > /etc/nginx/modsec/main.conf

# Copy ModSecurity base config
COPY modsecurity.conf /etc/nginx/modsec/modsecurity.conf

# Copy custom nginx.conf that loads the ModSecurity module
COPY nginx.conf /etc/nginx/nginx.conf

# Expose ports (already mapped via docker-compose)
EXPOSE 80 8080

CMD ["nginx", "-g", "daemon off;"]