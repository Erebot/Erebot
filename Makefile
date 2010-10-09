# vim: noet ts=8 sts=8 sw=8

EREBOT_CONFIG_SCHEMA	:= src/config/config.xsd
EREBOT_MODULES		:= $(shell find modules/ -mindepth 1 -maxdepth 1 \
				-type d -exec basename '{}' ';' | \
				grep -v -E '(\.svn|user)' | sort)
MODULES_HTML		:= doc/html/modules.html

DOXYFILE		:= Doxyfile.in
PACKAGE_NAME		:= Erebot
PACKAGE_VERSION		:= $(shell php -f src/version.php)
GETTEXT_ARGS		:= Erebot

TARGET_EXTRACT_MESSAGES	:= extract_messages
TARGET_INIT_CATALOG	:= init_catalog
TARGET_UPDATE_CATALOG	:= update_catalog
TARGET_COMPILE_CATALOG  := compile_catalog
TARGET_TEST		:= test
TARGET_DOC		:= doc
TARGET_CLEAN_DOC	:= clean_doc

all: $(EREBOT_CONFIG_SCHEMA) compile_catalog

include Makefile.generic

define target_modules
	@for mod in $(EREBOT_MODULES);					\
	do								\
		makefile=`find modules/$$mod/ -maxdepth 1 '('		\
			-name GNUmakefile -o				\
			-name makefile -o				\
			-name Makefile ')' -exec basename '{}' ';'`;	\
		if [ x$$makefile = x ];					\
		then							\
			makefile=../../Makefile.generic;		\
		fi;							\
		if [ -f "modules/$$mod/Doxyfile" ];			\
		then							\
			doxyfile="Doxyfile";				\
		else							\
			doxyfile=../../Doxyfile.in;			\
		fi;							\
		PACKAGE_NAME="$$mod"					\
		PACKAGE_VERSION="$(PACKAGE_VERSION)"			\
		DOXYFILE="$$doxyfile"					\
		PHPUNIT_ARGS="--bootstrap ../../tests/bootstrap.php"	\
		GETTEXT_ARGS="$$mod.php"				\
			$(MAKE) -C modules/$$mod/ -f $$makefile $(1);	\
	done
endef

run: all
	php -f Erebot

$(EREBOT_CONFIG_SCHEMA):
$(EREBOT_CONFIG_SCHEMA): %.xsd: %.rnc
	@echo Generating config validation schema.
	java -jar utils/trang.jar $< $@
	sed -i -e "s/@EREBOT_VERSION@/$(PACKAGE_VERSION)/" $@

clean: clean_doc
	$(RM) $(EREBOT_CONFIG_SCHEMA)

extract_messages: default-extract_messages
	$(call target_modules,extract_messages)

init_catalog: default-init_catalog
	$(call target_modules,init_catalog)

update_catalog: default-update_catalog
	$(call target_modules,update_catalog)

compile_catalog: default-compile_catalog
	$(call target_modules,compile_catalog)

set-test-args:
	export PHPUNIT_ARGS="--bootstrap ./tests/bootstrap.php"

test: set-test-args default-test
	$(call target_modules,test)

doc: default-doc
	@echo "Creating links to modules' documentations"
	@echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'		>  $(MODULES_HTML)
	@echo '<html lang="en">'							>> $(MODULES_HTML)
	@echo '<head>'									>> $(MODULES_HTML)
	@echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'	>> $(MODULES_HTML)
	@echo '<title>Modules</title>'							>> $(MODULES_HTML)
	@echo '<link rel="stylesheet" type="text/css" href="modules.css">'		>> $(MODULES_HTML)
	@echo '</head>'									>> $(MODULES_HTML)
	@echo '<body>'									>> $(MODULES_HTML)
	@echo '<ul>'									>> $(MODULES_HTML)
	@for mod in $(EREBOT_MODULES);										\
	do													\
		link=`php -r "echo rawurlencode('$$mod');";`;							\
		text=`php -r "echo htmlspecialchars('$$mod');";`;						\
		echo "<li><a href=\"../../modules/$$link/doc/html/index.html\" "				\
			"target=\"_blank\">$$text</a></li>"				>> $(MODULES_HTML);	\
	done
	@echo '</ul>'									>> $(MODULES_HTML)
	@echo '</body>'									>> $(MODULES_HTML)
	@echo '</html>'									>> $(MODULES_HTML)
	$(call target_modules,doc)

clean_doc: default-clean_doc
	$(call target_modules,clean_doc)

.PHONY: all run
.PHONY: test
.PHONY: clean

