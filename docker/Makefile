# mLITE Docker Makefile
# Compatible with bash/zsh/macOS/Linux

PROJECT_NAME = mlite
COMPOSE_FILE = docker-compose.yml
DB_CONTAINER = mlite_mysql
PHP_CONTAINER = mlite_php
ORTHANC_CONTAINER = mlite_orthanc
NGINX_CONTAINER = mlite_nginx
FLASK_APP_CONTAINER = mlite_panel
DNS_CONTAINER = mlite_dns

DB_NAME = mlite_db
DB_USER = mlite
DB_PASSWORD = mlite123
BACKUP_DIR = ./backups

ORTHANC_URL=http://localhost:8042/instances
ORTHANC_API=http://localhost:8042
ORTHANC_USER=orthanc
ORTHANC_PASS=orthanc

# Colors
RED = \033[0;31m
GREEN = \033[0;32m
YELLOW = \033[1;33m
BLUE = \033[0;34m
NC = \033[0m

.PHONY: build up down restart logs logs-follow ps status php mysql orthanc nginx panel dns db-backup db-restore clean help orthanc-test orthanc-upload-dummy orthanc-status nginx-status nginx-reload nginx-logs dns-status dns-logs dns-test

## Build all services
build:
	@echo "$(BLUE)Building all services...$(NC)"
	@DOCKER_BUILDKIT=1 docker compose -f $(COMPOSE_FILE) build --progress=plain
	@echo "$(GREEN)Build completed!$(NC)"

## Start all containers
up:
	@echo "$(BLUE)Starting all containers...$(NC)"
	@docker compose -f $(COMPOSE_FILE) up -d
	@echo "$(GREEN)All containers started!$(NC)"
	@make status

## Stop and remove containers
down:
	@echo "$(YELLOW)Stopping and removing containers...$(NC)"
	@docker compose -f $(COMPOSE_FILE) down
	@echo "$(GREEN)All containers stopped!$(NC)"

## Restart containers
restart:
	@echo "$(YELLOW)Restarting containers...$(NC)"
	@docker compose -f $(COMPOSE_FILE) restart
	@echo "$(GREEN)Containers restarted!$(NC)"

## Logs
logs:
	@docker compose -f $(COMPOSE_FILE) logs

logs-follow:
	@docker compose -f $(COMPOSE_FILE) logs -f

## Show container status
status:
	@docker compose -f $(COMPOSE_FILE) ps

## Shell access
php:
	@docker compose -f $(COMPOSE_FILE) exec $(PHP_CONTAINER) /bin/bash

mysql:
	@docker compose -f $(COMPOSE_FILE) exec $(DB_CONTAINER) mysql -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)

orthanc:
	@docker compose -f $(COMPOSE_FILE) exec $(ORTHANC_CONTAINER) /bin/bash

nginx:
	@docker compose -f $(COMPOSE_FILE) exec $(NGINX_CONTAINER) /bin/bash

panel:
	@docker compose -f $(COMPOSE_FILE) exec panel /bin/bash

dns:
	@docker compose -f $(COMPOSE_FILE) exec $(DNS_CONTAINER) /bin/bash

## Nginx Manager Commands
nginx-status:
	@echo "$(BLUE)Checking Nginx status...$(NC)"
	@curl -s http://localhost:5001/api/nginx-status | jq . || echo "$(RED)Nginx Manager not accessible$(NC)"

nginx-reload:
	@echo "$(YELLOW)Reloading Nginx configuration...$(NC)"
	@curl -s http://localhost:5001/api/reload-nginx | jq . || echo "$(RED)Failed to reload nginx$(NC)"

nginx-logs:
	@echo "$(BLUE)Nginx Manager logs:$(NC)"
	@docker compose -f $(COMPOSE_FILE) logs panel

## Database backup
db-backup:
	@echo "$(BLUE)Creating database backup...$(NC)"
	@mkdir -p $(BACKUP_DIR)
	@BACKUP_FILE="$(BACKUP_DIR)/$(DB_NAME)_$$(date +%Y%m%d_%H%M%S).sql"; \
	docker compose -f $(COMPOSE_FILE) exec -T $(DB_CONTAINER) mysqldump -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME) > $$BACKUP_FILE && \
	echo "$(GREEN)Backup created: $$BACKUP_FILE$(NC)" || echo "$(RED)Backup failed!$(NC)"

## Database restore
db-restore:
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)Usage: make db-restore FILE=backup.sql$(NC)"; \
		exit 1; \
	fi; \
	if [ ! -f "$(FILE)" ]; then \
		echo "$(RED)Error: $(FILE) not found$(NC)"; \
		exit 1; \
	fi; \
	echo "$(YELLOW)Restoring database from $(FILE)...$(NC)"; \
	docker compose -f $(COMPOSE_FILE) exec -T $(DB_CONTAINER) mysql -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME) < $(FILE); \
	echo "$(GREEN)Database restored successfully!$(NC)"

## Clean up Docker images and containers
clean:
	@echo "$(YELLOW)Cleaning up containers and images...$(NC)"
	@docker compose -f $(COMPOSE_FILE) down --rmi all
	@docker system prune -f --volumes
	@echo "$(GREEN)Cleanup completed!$(NC)"

## Orthanc Testing
orthanc-test:
	@echo "$(BLUE)Testing Orthanc connection...$(NC)"
	@if docker compose -f $(COMPOSE_FILE) ps $(ORTHANC_CONTAINER) | grep -q "Up"; then \
		echo "$(GREEN)Orthanc container is running$(NC)"; \
		curl -s -u "$(ORTHANC_USER):$(ORTHANC_PASS)" $(ORTHANC_API)/system | jq .Version || echo "$(RED)Orthanc API not reachable$(NC)"; \
	else \
		echo "$(RED)Orthanc container is not running$(NC)"; \
	fi

orthanc-upload-dummy:
	@echo "📤 Uploading dummy DICOM files to Orthanc..."
	@find ./orthanc/dummy-dicom -type f -name "*.dcm" -print0 | while IFS= read -r -d '' f; do \
		echo "➡️  Upload $$f"; \
		curl -s -X POST "$(ORTHANC_URL)" --user "$(ORTHANC_USER):$(ORTHANC_PASS)" --data-binary @"$$f" > /dev/null || echo "❌ Failed: $$f"; \
	done; \
	echo "$(GREEN)✅ Done. Listing studies:$(NC)"; \
	curl -s -u "$(ORTHANC_USER):$(ORTHANC_PASS)" "$(ORTHANC_API)/studies" | jq .

orthanc-status:
	@echo "$(YELLOW)Checking Orthanc system status...$(NC)"
	@curl -s -u "$(ORTHANC_USER):$(ORTHANC_PASS)" "$(ORTHANC_API)/system" | jq .

## Panel rebuild
panel-rebuild:
	@echo "$(YELLOW)Rebuilding panel service...$(NC)"
	@DOCKER_BUILDKIT=1 docker compose -f $(COMPOSE_FILE) build --progress=plain panel
	@docker compose -f $(COMPOSE_FILE) up -d --force-recreate panel
	@echo "$(GREEN)Panel rebuilt and restarted!$(NC)"
	@make status

## Help
help:
	@echo "$(GREEN)mLITE Docker Commands$(NC)"
	@echo "  make build               - Build all services"
	@echo "  make up                  - Start containers"
	@echo "  make down                - Stop containers"
	@echo "  make restart             - Restart containers"
	@echo "  make status              - Show running containers"
	@echo "  make logs                - Show logs"
	@echo "  make logs-follow         - Follow logs"
	@echo "  make php/mysql/nginx/... - Access containers"
	@echo "  make db-backup           - Backup database"
	@echo "  make db-restore FILE=x   - Restore from backup"
	@echo "  make orthanc-test        - Test Orthanc connection"
	@echo "  make orthanc-upload-dummy- Upload dummy DICOM files"
	@echo "  make orthanc-status      - Show Orthanc system info"
	@echo "  make panel               - Access Panel container"
	@echo "  make dns                 - Access DNS container"
	@echo "  make nginx-status        - Check Nginx Manager status"
	@echo "  make nginx-reload        - Reload Nginx via Manager"
	@echo "  make nginx-logs          - Show Nginx Manager logs"
	@echo "  make dns-status          - Check DNS status"
	@echo "  make dns-logs            - Show DNS logs"
	@echo "  make dns-test            - Test DNS resolution"
	@echo "  make clean               - Remove all containers/images"

## DNS Commands
dns-status:
	@echo "$(BLUE)Checking DNS status...$(NC)"
	@curl -s http://localhost:5001/api/dns-status | jq . || echo "$(RED)DNS Manager not accessible$(NC)"

dns-logs:
	@echo "$(BLUE)DNS logs:$(NC)"
	@docker compose -f $(COMPOSE_FILE) logs dns

dns-test:
	@echo "$(BLUE)Testing DNS resolution...$(NC)"
	@if docker compose -f $(COMPOSE_FILE) ps $(DNS_CONTAINER) | grep -q "Up"; then \
		echo "$(GREEN)DNS container is running$(NC)"; \
		echo "Testing DNS server at $(DNS_SERVER_IP)..."; \
		nslookup panel.local $(DNS_SERVER_IP) || echo "$(RED)DNS test failed$(NC)"; \
	else \
		echo "$(RED)DNS container is not running$(NC)"; \
	fi
