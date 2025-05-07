# Makefile for Passive CAPTCHA Plugin

.DEFAULT_GOAL := help

PHPUNIT = docker-compose run --rm phpunit
DC       = docker-compose

help:
	@echo "Usage:"
	@echo "  make build             Build all Docker images"
	@echo "  make up                Start WordPress & DB (nginx+PHP can come up too)"
	@echo "  make down              Stop & remove containers"
	@echo "  make logs              Tail logs for all services"
	@echo "  make install-tests     Bootstrap WP PHPUnit tests"
	@echo "  make db-reset          Reset the test database"
	@echo "  make activate-plugin   Activate plugin in test site"
	@echo "  make test              Run PHPUnit tests"
	@echo "  make lint              Run PHP & JS linters"

build:
	$(DC) build

up:
	$(DC) up -d nginx wordpress db

down:
	$(DC) down

logs:
	$(DC) logs -f

install-tests:
	$(PHPUNIT) bash bin/install-wp-tests.sh wordpress_test wp_test password db

db-reset:
	@echo ">>> Dropping & recreating wordpress_test database"
	docker-compose exec db \
	  mysql -uroot -prootpassword -e "DROP DATABASE IF EXISTS wordpress_test; CREATE DATABASE wordpress_test;"

activate-plugin:
	$(PHPUNIT) bash -lc "wp plugin activate passive-captcha"

test: install-tests db-reset activate-plugin
	$(PHPUNIT) bash -lc "vendor/bin/phpunit --configuration phpunit.xml"

lint:
	@echo ">>> PHP lint (PHPCS)..."
	composer run-script phpcs
	@echo ">>> JS lint (ESLint)..."
	npm --prefix js run lint
