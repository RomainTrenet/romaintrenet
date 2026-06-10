# Styles
YELLOW=$(shell echo "\033[00;33m")
RED=$(shell echo "\033[00;31m")
RESTORE=$(shell echo "\033[0m")

# Variables
THEME_DIR := web/themes/custom/dsfr_specific
SITE_URL  := https://opensearch.ddev.site

.DEFAULT_GOAL := list

.PHONY: list
list:
	@echo "*********************"
	@echo "${YELLOW}Available targets${RESTORE}:"
	@echo "*********************"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "[32m%-15s[0m %s\n", $$1, $$2}'

.PHONY: install
install: ## Install the local project, only if you have already built your recipe site.
	@ddev composer install
	@ddev drush deploy

.PHONY: upgrade
upgrade: ## Upgrade composer, conf, database and locale. Make a first cim to be sure to point to the right config_split.
	@ddev composer install -n
	@ddev drush cr
	@ddev drush cim
	@ddev drush deploy
	@ddev drush locale-check
	@ddev drush locale-update
	@ddev drush cr

.PHONY: locale
locale: ## Update locale.
	@ddev drush locale-check
	@ddev drush locale-update
	@ddev drush cr

.PHONY: importdb
import-db: ## Import default dump/opensearch.sql.gz database
	ddev import-db --file=dump/opensearch.sql.gz

.PHONY: exportdb
export-db: ## Export default dump/opensearch.sql.gz database
	ddev export-db --file=dump/opensearch.sql.gz

.PHONY: ddev-watch-assets ddev-assets ddev-install-assets
ddev-watch-assets: ## DDEV : Watch theme assets
	@ddev exec bash -lc 'cd $(THEME_DIR) && PROXY_URL=$(SITE_URL) yarn watch'

ddev-assets: ## DDEV : Install and build theme assets
	@ddev exec bash -lc 'cd $(THEME_DIR) && export NODE_ENV= && yarn install --frozen-lockfile --production=false && yarn build'

ddev-install-assets: ## DDEV : Install theme assets
	@ddev exec bash -lc 'cd $(THEME_DIR) && yarn install'

# Version Yarn (hors DDEV) — idem que ddev-assets mais en local
assets: ## Server : Install and build theme assets with yarn.
	@echo "Build (Yarn) dans $(THEME_DIR)"
	@yarn --cwd $(THEME_DIR) install --frozen-lockfile
	@yarn --cwd $(THEME_DIR) build

# Variante si la machine n'a pas Yarn mais a npm
assets-npm: ## Server : Install and build theme assets with npm.
	@echo "Build (npm) dans $(THEME_DIR)"
	@cd $(THEME_DIR) && npm ci
	@cd $(THEME_DIR) && npm run build
