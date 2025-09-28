# Makefile for mLITE Docker Environment
# Usage: make <target>

# Variables
PROJECT_NAME = mlite
COMPOSE_FILE = docker-compose.yml
DB_CONTAINER = mlite_mysql
PHP_CONTAINER = mlite_php
ORTHANC_CONTAINER = orthanc
NGINX_CONTAINER = mlite_nginx
DB_NAME = mlite_db
DB_USER = mlite
DB_PASSWORD = mlite123
BACKUP_DIR = ./backups

ORTHANC_URL=http://localhost:8042/instances
ORTHANC_API=http://localhost:8042
ORTHANC_USER=orthanc
ORTHANC_PASS=orthanc

# Colors for output
RED = \033[0;31m
GREEN = \033[0;32m
YELLOW = \033[1;33m
BLUE = \033[0;34m
NC = \033[0m # No Color

.PHONY: build up down restart logs ps status php mysql orthanc nginx db-backup db-restore clean clean-all dev-setup dev-reset monitor top help orthanc-test orthanc-upload-dummy orthanc-status orthanc-web orthanc-logs orthanc-restart

# Default target
all: help

## Docker Compose Operations
build: ## Build all services
	@echo "$(BLUE)Building all services...$(NC)"
	docker-compose -f $(COMPOSE_FILE) build
	@echo "$(GREEN)Build completed!$(NC)"

up: ## Start all containers
	@echo "$(BLUE)Starting all containers...$(NC)"
	docker-compose -f $(COMPOSE_FILE) up -d
	@echo "$(GREEN)All containers started!$(NC)"
	@make status

down: ## Stop and remove containers
	@echo "$(YELLOW)Stopping and removing containers...$(NC)"
	docker-compose -f $(COMPOSE_FILE) down
	@echo "$(GREEN)All containers stopped!$(NC)"

restart: ## Restart all containers
	@echo "$(YELLOW)Restarting all containers...$(NC)"
	docker-compose -f $(COMPOSE_FILE) restart
	@echo "$(GREEN)All containers restarted!$(NC)"

logs: ## Show logs from all containers
	@echo "$(BLUE)Showing logs from all containers...$(NC)"
	docker-compose -f $(COMPOSE_FILE) logs

logs-follow: ## Follow logs in real-time
	@echo "$(BLUE)Following logs in real-time (Ctrl+C to stop)...$(NC)"
	docker-compose -f $(COMPOSE_FILE) logs -f

ps: ## Show running containers
	@echo "$(BLUE)Running containers:$(NC)"
	docker-compose -f $(COMPOSE_FILE) ps

status: ## Show status of all services
	@echo "$(BLUE)Service Status:$(NC)"
	@docker-compose -f $(COMPOSE_FILE) ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"

## Shell Access
php: ## Access PHP container shell
	@echo "$(BLUE)Accessing PHP container shell...$(NC)"
	docker-compose -f $(COMPOSE_FILE) exec $(PHP_CONTAINER) /bin/bash

mysql: ## Access MySQL container shell
	@echo "$(BLUE)Accessing MySQL container shell...$(NC)"
	docker-compose -f $(COMPOSE_FILE) exec $(DB_CONTAINER) mysql -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)

orthanc: ## Access Orthanc container shell
	@echo "$(BLUE)Accessing Orthanc container shell...$(NC)"
	docker-compose -f $(COMPOSE_FILE) exec $(ORTHANC_CONTAINER) /bin/bash

nginx: ## Access Nginx container shell
	@echo "$(BLUE)Accessing Nginx container shell...$(NC)"
	docker-compose -f $(COMPOSE_FILE) exec $(NGINX_CONTAINER) /bin/bash

## Database Operations
db-backup: ## Backup database
	@echo "$(BLUE)Creating database backup...$(NC)"
	@mkdir -p $(BACKUP_DIR)
	@BACKUP_FILE="$(BACKUP_DIR)/$(DB_NAME)_$$(date +%Y%m%d_%H%M%S).sql"; \
	docker-compose -f $(COMPOSE_FILE) exec -T $(DB_CONTAINER) mysqldump -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME) > $$BACKUP_FILE && \
	echo "$(GREEN)Database backup created: $$BACKUP_FILE$(NC)" || \
	echo "$(RED)Database backup failed!$(NC)"

db-restore: ## Restore database (Usage: make db-restore FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)Error: Please specify backup file. Usage: make db-restore FILE=backup.sql$(NC)"; \
		exit 1; \
	fi
	@if [ ! -f "$(FILE)" ]; then \
		echo "$(RED)Error: Backup file $(FILE) not found!$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)Restoring database from $(FILE)...$(NC)"
	@docker-compose -f $(COMPOSE_FILE) exec -T $(DB_CONTAINER) mysql -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME) < $(FILE)
	@echo "$(GREEN)Database restored successfully!$(NC)"

db-shell: ## Access MySQL shell directly
	@echo "$(BLUE)Accessing MySQL shell...$(NC)"
	docker-compose -f $(COMPOSE_FILE) exec $(DB_CONTAINER) mysql -u$(DB_USER) -p$(DB_PASSWORD)

## Cleanup Operations
clean: ## Clean up containers and images (keep volumes and dummy data)
	@echo "$(YELLOW)Cleaning up containers and images (keeping volumes and dummy-dicom)...$(NC)"
	@docker-compose down --rmi all
	@docker system prune -f --volumes
	@echo "$(GREEN)Cleanup completed (volumes and dummy-dicom preserved)$(NC)"

clean-all: ## Clean up everything except dummy DICOM data
	@echo "$(RED)WARNING: This will remove all containers, images, and volumes except dummy-dicom data!$(NC)"
	@echo "$(YELLOW)Press Ctrl+C to cancel, or wait 5 seconds to continue...$(NC)"
	@sleep 5
	@docker-compose down -v --rmi all
	@docker system prune -af --volumes
	@echo "$(GREEN)Complete cleanup finished (dummy-dicom data preserved)$(NC)"

## Development Helpers
dev-setup: ## Initial development setup
	@echo "$(BLUE)Setting up development environment...$(NC)"
	@make build
	@make up
	@echo "$(GREEN)Development environment ready!$(NC)"
	@echo "$(YELLOW)Access URLs:$(NC)"
	@echo "  - Web Application: http://localhost:8080"
	@echo "  - Orthanc DICOM: http://localhost:8042"
	@echo "  - MySQL: localhost:3306"

dev-reset: ## Reset development environment
	@echo "$(YELLOW)Resetting development environment...$(NC)"
	@make down
	@make clean
	@make dev-setup

## Monitoring
monitor: ## Monitor container resources
	@echo "$(BLUE)Monitoring container resources (Ctrl+C to stop)...$(NC)"
	docker stats

top: ## Show top processes in containers
	@echo "$(BLUE)Top processes in containers:$(NC)"
	@for container in $(PHP_CONTAINER) $(DB_CONTAINER) $(ORTHANC_CONTAINER) $(NGINX_CONTAINER); do \
		echo "\n$(YELLOW)=== $$container ===$(NC)"; \
		docker-compose -f $(COMPOSE_FILE) exec $$container ps aux 2>/dev/null || echo "Container not running"; \
	done

## Help
help: ## Show this help message
	@echo "$(GREEN)mLITE Docker Environment - Available Commands:$(NC)"
	@echo ""
	@echo "$(YELLOW)Docker Compose Operations:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -E '^(build|up|down|restart|logs|logs-follow|ps|status):' | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(YELLOW)Shell Access:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -E '^(php|mysql|orthanc|nginx):' | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(YELLOW)Database Operations:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -E '^db-' | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(YELLOW)Cleanup & Maintenance:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -E '^(clean|monitor|top):' | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(YELLOW)Development:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -E '^dev-' | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(YELLOW)Orthanc Testing Commands:$(NC)"
	@echo "  $(BLUE)orthanc-test$(NC)         - Test Orthanc server connection"
	@echo "  $(BLUE)orthanc-upload-dummy$(NC) - Upload dummy DICOM data to Orthanc"
	@echo "  $(BLUE)orthanc-status$(NC)       - Check Orthanc server status"
	@echo "  $(BLUE)orthanc-web$(NC)          - Open Orthanc web interface"
	@echo "  $(BLUE)orthanc-logs$(NC)         - Show Orthanc container logs"
	@echo "  $(BLUE)orthanc-restart$(NC)      - Restart only Orthanc container"
	@echo ""
	@echo "$(YELLOW)Examples:$(NC)"
	@echo "  make up                          # Start all services"
	@echo "  make logs-follow                 # Follow logs in real-time"
	@echo "  make php                         # Access PHP container"
	@echo "  make db-backup                   # Backup database"
	@echo "  make db-restore FILE=backup.sql  # Restore from backup"
	@echo "  make orthanc-upload-dummy        # Upload test DICOM data"
	@echo ""

## Orthanc Testing Commands
orthanc-test: ## Test Orthanc server connection
	@echo "$(YELLOW)Testing Orthanc server connection...$(NC)"
	@if docker-compose -f $(COMPOSE_FILE) ps orthanc | grep -q "Up"; then \
		echo "$(GREEN)Orthanc container is running$(NC)"; \
		curl -s http://localhost:8042/system 2>/dev/null && echo "$(GREEN)Orthanc API is accessible$(NC)" || echo "$(RED)Orthanc API is not accessible$(NC)"; \
	else \
		echo "$(RED)Orthanc container is not running$(NC)"; \
	fi

orthanc-upload-dummy: ## Upload dummy DICOM data to Orthanc
	@echo "📤 Upload ke Orthanc..."
	@find orthanc/dummy-dicom -type f -name "*.dcm" -print0 | while IFS= read -r -d '' f; do \
	echo "➡️  Upload $$f"; \
	echo "CMD: curl -s -X POST $(ORTHANC_URL) --user $(ORTHANC_USER):$(ORTHANC_PASS) --data-binary @\"$$f\""; \
	curl -s -X POST "$(ORTHANC_URL)" \
		--user "$(ORTHANC_USER):$(ORTHANC_PASS)" \
		--data-binary @"$$f" || echo "❌ gagal upload $$f"; \
	done
	@echo "✅ Selesai. Daftar studies di Orthanc:"
	@curl -s -u "$(ORTHANC_USER):$(ORTHANC_PASS)" "$(ORTHANC_API)/studies"

orthanc-status: ## Check Orthanc server status
	@echo "$(YELLOW)Checking Orthanc server status...$(NC)"
	@curl -s http://localhost:8042/system | python3 -m json.tool 2>/dev/null || echo "$(RED)Failed to get Orthanc status$(NC)"

orthanc-web: ## Open Orthanc web interface
	@echo "$(YELLOW)Opening Orthanc web interface...$(NC)"
	@echo "$(GREEN)Orthanc web interface: http://localhost:8042$(NC)"
	@open http://localhost:8042 2>/dev/null || echo "$(YELLOW)Please open http://localhost:8042 in your browser$(NC)"

orthanc-logs: ## Show Orthanc container logs
	@echo "$(YELLOW)Showing Orthanc container logs...$(NC)"
	docker-compose -f $(COMPOSE_FILE) logs -f orthanc

orthanc-restart: ## Restart only Orthanc container
	@echo "$(YELLOW)Restarting Orthanc container...$(NC)"
	docker-compose -f $(COMPOSE_FILE) restart orthanc
	@echo "$(GREEN)Orthanc container restarted$(NC)"





