app_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
composer=$(shell which composer 2> /dev/null)

all: dev-setup lint build-js-production open-api test

# Dev env management
dev-setup: clean clean-dev composer npm-init


# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
composer:
ifeq (, $(composer))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)
	php $(build_tools_directory)/composer.phar install --prefer-dist
	php $(build_tools_directory)/composer.phar update --prefer-dist
else
	composer install --prefer-dist
	composer update --prefer-dist
endif

npm-init:
	npm ci


# Updating packages for Vue sub-projects.
npm-update: npm-update-admin npm-update-publicPortal npm-update-attendantPortal npm-update-proceduresView

npm-update-admin:
	cd ./vue/admin && npm update

npm-update-publicPortal:
	cd ./vue/publicPortal && npm update

npm-update-attendantPortal:
	cd ./vue/attendantPortal && npm update

npm-update-proceduresView:
	cd ./vue/proceduresView && npm update


# Installing packages for Vue sub-projects.
npm-install: npm-install-admin npm-install-publicPortal npm-install-attendantPortal npm-install-proceduresView

npm-install-admin:
	cd ./vue/admin && npm install

npm-install-publicPortal:
	cd ./vue/publicPortal && npm install

npm-install-attendantPortal:
	cd ./vue/attendantPortal && npm install

npm-install-proceduresView:
	cd ./vue/proceduresView && npm install


# Building Vue sub-projects for production.
build:
	cd ./vue/admin && npm run build && cd ../publicPortal && npm run build && cd ../attendantPortal && npm run build && cd ../proceduresView && npm run build


# Building Vue sub-projects for development.
build-dev: build-dev-admin build-dev-publicPortal build-dev-attendantPortal build-dev-proceduresView

build-dev-admin:
	cd ./vue/admin && npm run dev

build-dev-publicPortal:
	cd ./vue/publicPortal && npm run dev

build-dev-attendantPortal:
	cd ./vue/attendantPortal && npm run dev

build-dev-proceduresView:
	cd ./vue/proceduresView && npm run dev


# Build with watch for the diferent sub-projects.
watch-admin:
	cd ./vue/admin && npm run watch

watch-publicPortal:
	cd ./vue/publicPortal && npm run watch

watch-attendantPortal:
	cd ./vue/attendantPortal && npm run watch

watch-proceduresView:
	cd ./vue/proceduresView && npm run watch


# Generating the api swagger/open-api definition.
open-api:
	./vendor/bin/openapi -o vtramit-api-v3.yml --format yaml lib/
	npx api-spec-converter --from=openapi_3 --to=swagger_2 --syntax=yaml vtramit-api-v3.yml > vtramit-api-v2.yml


# Linting
lint:
	npm run lint

lint-fix:
	npm run lint:fix


# Style linting
stylelint:
	npm run stylelint

stylelint-fix:
	npm run stylelint:fix


# Cleaning
clean:
	rm -rf js/*

clean-dev:
	rm -rf node_modules

# Tests
run-phpunit:
	make docker-test-up
	make docker-test-wait-for-health
	docker exec -it nextcloud_test_local_nc sh -lc "cd custom_apps/vtramit && vendor/bin/phpunit test/phpunit --configuration test/phpunit/phpunit.xml"
	make docker-test-stop
	make docker-test-clean

run-cypress:
	@echo "***"
	@echo "If during the execution you get a depedencies error, remember to install dependencies by running:"
	@echo "  npm install"
	@echo "***"
	@echo ""
	make docker-test-up
	make docker-test-wait-for-health
	ALSA_CONFIG_PATH=/dev/null npx cypress run 2>&1 | grep -vE "(^ALSA lib|alsa_util\.cc|Unknown PCM|PcmOpen:)"
	make docker-test-stop
	make docker-test-clean
	@echo ""
	@echo "***"
	@echo "Review following paths to get all the execution information:"
	@echo "  test/cypress/downloads"
	@echo "  test/cypress/coverage"
	@echo "  test/cypress/report"
	@echo "  docker/test-local/data/nextcloud.log"

# Wait until MariaDB answers to mysqladmin ping
docker-test-wait-for-health:
	@echo "Esperando a que el contenedor esté healthy..."
	@retries=60; \
	while [ $$retries -gt 0 ]; do \
		status=$$(docker inspect -f '{{.State.Health.Status}}' nextcloud_test_local_nc 2>/dev/null || echo "nohealth"); \
		if [ "$$status" = "healthy" ]; then \
			echo " container is healthy"; \
			exit 0; \
		fi; \
		if [ "$$status" = "unhealthy" ] || [ "$$status" = "nohealth" ]; then \
			echo " ERROR: container status: $$status"; \
			exit 1; \
		fi; \
		printf "."; \
		sleep 5; \
		retries=$$((retries-1)); \
	done; \
	echo " ERROR: timeout waiting container to be healthy"; \
	exit 1

# Creating and running the different dockers for development and test.
# The entry point for the Nextcloud UI is going to be localhost:8080 (you can check docker/test-local/docker-compose.yml for other services)
# Start detached (-d) so we can wait for readiness checks
docker-test-up:
	cd ./docker/test-local && docker compose up -d

docker-test-stop:
	cd ./docker/test-local && docker compose stop

docker-fix-permissions-for-linux:
	sudo chmod -R a+w docker/test-local/config/config.php
	sudo chmod -R a+w docker/test-local/config
	sudo chmod -R a+w docker/test-local/data
	sudo chown www-data:www-data docker/test-local/config/*.php

docker-test-clean:
	cd docker/test-local; docker rm nextcloud_test_local_nc nextcloud_test_local_db nextcloud_phpmyadmin
	cd docker/test-local; docker volume rm test-local_nextcloud_test_local_www test-local_nextcloud_test_local_db_data
