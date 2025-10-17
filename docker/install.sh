#!/usr/bin/env bash
# ==========================================================
# Universal Docker Static Binary Installer (with --skip-compose)
# ==========================================================

set -e

green() { echo -e "\033[1;32m$*\033[0m"; }
yellow() { echo -e "\033[1;33m$*\033[0m"; }
red() { echo -e "\033[1;31m$*\033[0m"; }

if [ "$EUID" -ne 0 ]; then
  red "❌ Please run as root or with sudo."
  exit 1
fi

# ==========================================================
# 🧑‍💻 Tambahkan user nginx dengan nologin
# ==========================================================

if ! getent group nginx >/dev/null; then
  yellow "Creating group 'nginx'..."
  groupadd --system nginx
  green "✅ Group 'nginx' created."
else
  green "ℹ️ Group 'nginx' already exists."
fi

if ! id nginx &>/dev/null; then
  yellow "Adding user 'nginx' with no-login shell..."
  useradd --system --no-create-home --shell /usr/sbin/nologin nginx
  green "✅ User 'nginx' created (nologin)."
else
  green "ℹ️ User 'nginx' already exists."
fi

# ==========================================================
# Lanjut instalasi Docker
# ==========================================================

VERSION="latest"
SKIP_COMPOSE=false

for arg in "$@"; do
  case "$arg" in
    --skip-compose) SKIP_COMPOSE=true ;;
    *) VERSION="$arg" ;;
  esac
done

ARCH=$(uname -m)
case "$ARCH" in
  x86_64)   DOCKER_ARCH="x86_64" ;;
  aarch64)  DOCKER_ARCH="aarch64" ;;
  armv7l)   DOCKER_ARCH="armhf" ;;
  *) red "❌ Unsupported architecture: $ARCH"; exit 1 ;;
esac

green "Detected architecture: $DOCKER_ARCH"

if [ "$VERSION" = "latest" ]; then
  yellow "Fetching latest Docker version..."
  VERSION=$(curl -fsSL https://download.docker.com/linux/static/stable/$DOCKER_ARCH/ \
    | grep -Eo 'docker-[0-9]+\.[0-9]+\.[0-9]+' \
    | sort -V | tail -n1 | sed 's/docker-//')
fi
green "Installing Docker version: $VERSION"

URL="https://download.docker.com/linux/static/stable/$DOCKER_ARCH/docker-$VERSION.tgz"
yellow "Downloading from: $URL"
curl -fsSL "$URL" -o /tmp/docker.tgz

tar -xzf /tmp/docker.tgz -C /tmp/
install -m 0755 /tmp/docker/* /usr/local/bin/

echo
echo '🚀 Starting Docker daemon in background...'
nohup dockerd > /var/log/dockerd.log 2>&1 &
sleep 5

echo
echo '📦 Installing Docker Compose plugin...'
mkdir -p ~/.docker/cli-plugins/
curl -sSL https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s | tr '[:upper:]' '[:lower:]')-$(uname -m) -o ~/.docker/cli-plugins/docker-compose
chmod +x ~/.docker/cli-plugins/docker-compose
docker compose version && echo '✅ Docker Compose installed successfully!'

echo
echo "🔍 Detecting architecture..."
ARCH=$(uname -m)
case "$ARCH" in
  x86_64|amd64) BX_ARCH="amd64" ;;
  aarch64|arm64) BX_ARCH="arm64" ;;
  armv7l|armv7) BX_ARCH="arm-v7" ;;
  *) echo "❌ Unsupported architecture: $ARCH"; exit 1 ;;
esac
echo "Detected architecture: $BX_ARCH"

echo
echo "⬇️  Fetching latest Buildx release info..."
BUILDX_VERSION=$(curl -s https://api.github.com/repos/docker/buildx/releases/latest | grep tag_name | cut -d '"' -f 4)
BUILDX_URL="https://github.com/docker/buildx/releases/download/${BUILDX_VERSION}/buildx-${BUILDX_VERSION}.linux-${BX_ARCH}"

echo "📦 Downloading Buildx binary: ${BUILDX_URL}"
mkdir -p ~/.docker/cli-plugins/
curl -fsSL "$BUILDX_URL" -o ~/.docker/cli-plugins/docker-buildx
chmod +x ~/.docker/cli-plugins/docker-buildx

echo
echo "🔎 Checking binary type..."
file ~/.docker/cli-plugins/docker-buildx

echo
echo "🧩 Testing Buildx..."
docker buildx version || echo "⚠️ Buildx still not executable – check architecture."

if command -v systemctl >/dev/null 2>&1; then
  yellow "Setting up systemd service..."
  cat >/etc/systemd/system/docker.service <<'EOF'
[Unit]
Description=Docker Application Container Engine
Documentation=https://docs.docker.com
After=network-online.target firewalld.service
Wants=network-online.target

[Service]
ExecStart=/usr/local/bin/dockerd -H fd://
ExecReload=/bin/kill -s HUP $MAINPID
LimitNOFILE=1048576
LimitNPROC=infinity
LimitCORE=infinity
TasksMax=infinity
TimeoutStartSec=0
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF

  systemctl daemon-reload
  systemctl enable docker --now
else
  yellow "Systemd not found. You can start Docker manually with:"
  echo "   dockerd &"
fi

groupadd -f docker
usermod -aG docker "${SUDO_USER:-$USER}" || true

rm -rf /tmp/docker /tmp/docker.tgz
green "✅ Docker static binaries installed successfully!"

if [ "$SKIP_COMPOSE" = "true" ]; then
  echo "⏭️  Skipping Docker Compose build/up step (--skip-compose used)."
  exit 0
fi

echo
echo '⬇️  Downloading docker.tar.gz from basoro.id...'
curl --insecure -sSLo docker.tar.gz https://basoro.id/downloads/docker.tar.gz && echo '📦 docker.tar.gz downloaded successfully.'
echo
echo '📂 Extracting docker.tar.gz...'
tar -xzf docker.tar.gz && echo '✅ docker.tar.gz extracted successfully.'

if [ -d docker ]; then
  echo
  echo '🚀 Entering docker/ directory and running compose commands...'
  cd docker || exit 1
  docker compose -f docker-compose.yml build panel
  docker compose -f docker-compose.yml up -d panel
  echo '✅ Docker Compose panel service started successfully!'
else
  echo '⚠️  docker/ directory not found after extraction.'
fi

# ==========================================================
# 🎉 Final Info Section
# ==========================================================

address=""
n=0
while [ "$address" == '' ]; do
  address=$(curl -s ifconfig.me || echo "")
  let n++
  sleep 0.1
  if [ $n -gt 5 ]; then
    address="SERVER_IP"
  fi
done

# ==========================================================
# 🔍 Baca variabel dari docker/.env
# ==========================================================
ENV_FILE="docker/.env"
if [ -f "$ENV_FILE" ]; then
  yellow "Loading environment variables from $ENV_FILE..."
  export $(grep -v '^#' "$ENV_FILE" | xargs)
else
  red "⚠️  File $ENV_FILE not found. Using default values."
fi

port=${PORT:-7788}
username=${USERNAME:-admin}
password=${PASSWORD:-admin}

echo -e "=================================================================="
echo -e "\033[32mCongratulations! Install succeeded!\033[0m"
echo -e "=================================================================="
echo "mLITE-Panel: http://$address:$port"
echo -e "username: $username"
echo -e "password: $password"
echo -e "\033[33mWarning:\033[0m"
echo -e "\033[33mIf you cannot access the panel,\033[0m"
echo -e "\033[33mrelease the following ports (7788|80|443) in the firewall.\033[0m"
echo -e "=================================================================="
