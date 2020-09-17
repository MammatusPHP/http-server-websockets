# set all to phony
SHELL=bash

.PHONY: *

DOCKER_CGROUP:=$(shell cat /proc/1/cgroup | grep docker | wc -l)
COMPOSER_CACHE_DIR=$(shell composer config --global cache-dir -q || echo ${HOME}/.composer-php/cache)

ifneq ("$(wildcard /.you-are-in-a-wyrihaximus.net-php-docker-image)","")
    IN_DOCKER=TRUE
else
    IN_DOCKER=FALSE
endif

ifeq ("$(IN_DOCKER)","TRUE")
	DOCKER_RUN=
else
	DOCKER_RUN=docker run --rm -i \
		-v "`pwd`:`pwd`" \
		-v "${COMPOSER_CACHE_DIR}:/home/app/.composer/cache" \
		-w "`pwd`" \
		"wyrihaximusnet/php:7.4-zts-alpine3.12-dev"
endif

all: ## Runs everything ###
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -v "###" | awk 'BEGIN {FS = ":.*?## "}; {printf "%s\n", $$1}' | xargs --open-tty $(MAKE)

syntax-php: ## Lint PHP syntax
	$(DOCKER_RUN) vendor/bin/parallel-lint --exclude vendor .

cs-fix: ## Fix any automatically fixable code style issues
	$(DOCKER_RUN) vendor/bin/phpcbf --parallel=$(shell nproc) || $(DOCKER_RUN) vendor/bin/phpcbf --parallel=$(shell nproc) || $(DOCKER_RUN) vendor/bin/phpcbf --parallel=$(shell nproc) -vvvv

cs: ## Check the code for code style issues
	$(DOCKER_RUN) vendor/bin/phpcs --parallel=$(shell nproc)

stan: ## Run static analysis (PHPStan)
	$(DOCKER_RUN) vendor/bin/phpstan analyse src tests --level max --ansi -c phpstan.neon

psalm: ## Run static analysis (Psalm)
	$(DOCKER_RUN) vendor/bin/psalm --threads=$(shell nproc) --shepherd --stats

unit-testing: ## Run tests
	$(DOCKER_RUN) vendor/bin/phpunit --colors=always -c phpunit.xml.dist --coverage-text --coverage-html covHtml --coverage-clover ./build/logs/clover.xml
	$(DOCKER_RUN) test -n "$(COVERALLS_REPO_TOKEN)" && test -n "$(COVERALLS_RUN_LOCALLY)" && test -f ./build/logs/clover.xml && vendor/bin/php-coveralls -v --coverage_clover ./build/logs/clover.xml --json_path ./build/logs/coveralls-upload.json || true

mutation-testing: ## Run mutation testing
	$(DOCKER_RUN) vendor/bin/roave-infection-static-analysis-plugin --ansi --min-msi=100 --min-covered-msi=100 --threads=$(shell nproc)

composer-require-checker: ## Ensure we require every package used in this package directly
	$(DOCKER_RUN) vendor/bin/composer-require-checker --ignore-parse-errors --ansi -vvv --config-file=composer-require-checker.json

composer-unused: ## Ensure we don't require any package we don't use in this package directly
	$(DOCKER_RUN) composer unused --ansi

composer-install: ## Install dependencies
	$(DOCKER_RUN) composer install --no-progress --ansi --no-interaction --prefer-dist -o

backward-compatibility-check: ## Check code for backwards incompatible changes
	$(MAKE) backward-compatibility-check-raw || true

backward-compatibility-check-raw: ## Check code for backwards incompatible changes, doesn't ignore the failure ###
	$(DOCKER_RUN) vendor/bin/roave-backward-compatibility-check

shell: ## Provides Shell access in the expected environment ###
	$(DOCKER_RUN) ash

task-list-ci: ## CI: Generate a JSON array of jobs to run, matches the commands run when running `make (|all)` ###
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -v "###" | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "%s\n", $$1}' | jq --raw-input --slurp -c 'split("\n")| .[0:-1]'

help: ## Show this help ###
	@printf "\033[33mUsage:\033[0m\n  make [target]\n\n\033[33mTargets:\033[0m\n"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-32s\033[0m %s\n", $$1, $$2}' | tr -d '#'
