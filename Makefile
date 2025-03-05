SHELL := /usr/bin/env bash
CWD := $(shell pwd)
DOCKER_BIN ?= $(shell command -v docker 2>/dev/null)

ifeq ($(shell [ -t 0 ] && echo 1),1)
	DOCKER_DEFAULT_OPTIONS ?= -it --rm
else
	DOCKER_DEFAULT_OPTIONS ?= --rm
endif

ifeq ($(shell uname),Linux)
	DOCKER_RUN_USER := -u $(shell id -u):$(shell id -g)
else
	DOCKER_RUN_USER :=
endif

PHP_VERSION ?= 8.1
COMPOSER_VERSION ?= 2.8.6
COMPOSER_PHAR_URL ?= https://github.com/composer/composer/releases/download/$(COMPOSER_VERSION)/composer.phar

IMAGE_NAME ?= ghcr.io/sndsgd/php
IMAGE ?= $(IMAGE_NAME):$(PHP_VERSION)
DOCKER_RUN ?= $(DOCKER_BIN) run \
	$(DOCKER_DEFAULT_OPTIONS) \
	$(DOCKER_RUN_USER) \
	--volume $(CWD):$(CWD) \
	--workdir $(CWD) \
	$(IMAGE)

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[33m%s\033[0m~%s\n", $$1, $$2}' \
	| column -s "~" -t

IMAGE_ARGS ?= --quiet

.PHONY: image
image:
	@docker pull $(IMAGE)

.PHONY: prepare-build-directory
prepare-build-directory:
	rm -rf $(CWD)/build; mkdir $(CWD)/build

.PHONY: prepare-generated-directory
prepare-generated-directory:
	if [[ -e "$(CWD)/generated" ]]; then \
		rm -rf $(CWD)/generated; \
	fi; \
	mkdir $(CWD)/generated

.PHONY: build
build: composer-install cs test-coverage analyze

###############################################################################
# composer ####################################################################
###############################################################################

COMPOSER_ARGS ?= --help
.PHONY: composer
composer: ## Run an arbitrary composer command
composer: image
	$(DOCKER_RUN) /bin/composer $(COMPOSER_ARGS)

.PHONY: composer-install
composer-install: ## Install dependencies
composer-install: override COMPOSER_ARGS = install --no-cache
composer-install: composer

.PHONY: composer-update
composer-update: ## Update dependencies
composer-update: override COMPOSER_ARGS = update --no-cache
composer-update: composer

.PHONY: composer-autoload
composer-autoload: ## Update dependencies
composer-autoload: override COMPOSER_ARGS = dump-autoload --optimize
composer-autoload: composer

###############################################################################
# lint ########################################################################
###############################################################################

PHPLINT_ARGS ?= --help
.PHONY: phplint
phplint: image
	$(DOCKER_RUN) vendor/bin/parallel-lint $(PHPLINT_ARGS)

.PHONY: lint
lint: override PHPLINT_ARGS = src tests
lint: phplint

##############################################################################
# unit tests ##################################################################
###############################################################################

PHP_ARGS ?=
PHPUNIT_ARGS ?= --help
.PHONY: phpunit
phpunit: image lint prepare-build-directory
	$(DOCKER_RUN) php $(PHP_ARGS) vendor/bin/phpunit $(PHPUNIT_ARGS)

.PHONY: test
test: ## Run unit tests
test: override PHPUNIT_ARGS = --do-not-cache-result --no-coverage
test: phpunit

.PHONY: test-coverage
test-coverage: ## Run unit tests with code coverage
test-coverage: override PHPUNIT_ARGS = --do-not-cache-result
test-coverage: prepare-build-directory phpunit

.PHONY: test-coverage-submit
test-coverage-submit:
test-coverage-submit:
	wget https://github.com/php-coveralls/php-coveralls/releases/download/v2.2.0/php-coveralls.phar
	chmod +x php-coveralls.phar
	./php-coveralls.phar --coverage_clover=build/coverage/clover.xml --json_path=build/coverage/coveralls.json -v

.DEFAULT_GOAL := help
