SHELL := /bin/bash
BUNDLE := bundle
YARN := npm
VENDOR_DIR = assets/js/
JEKYLL := $(BUNDLE) exec jekyll

PROJECT_DEPS := Gemfile package.json

.PHONY: all clean install update

all : serve

check:
	$(JEKYLL) doctor
	$(HTMLPROOF) --check-html \
		--http-status-ignore 999 \
		--internal-domains localhost:4000 \
		--assume-extension \
		_site

install: $(PROJECT_DEPS)
	#$(BUNDLE) install
	$(YARN) install

update: $(PROJECT_DEPS)
	$(BUNDLE) update
	$(YARN) upgrade

include-yarn-deps:
	mkdir -p $(VENDOR_DIR)
	cp node_modules/jquery/dist/jquery.min.js $(VENDOR_DIR)
	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js $(VENDOR_DIR)

build: install include-yarn-deps
	node script/index.js
	$(JEKYLL) build
	$(JEKYLL) build --config _config.yml,_config-amp.yml

serve: install include-yarn-deps
	node script/index.js
	JEKYLL_ENV=production $(JEKYLL) serve