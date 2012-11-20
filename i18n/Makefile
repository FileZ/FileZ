# $Id: Makefile 2012-11-19 10:45:07Z eliovir $
EDITOR=lokalize
LANG=eo
DOMAIN=default

all:   update-po mo

help:
	cat README.mkd

pot: $(DOMAIN).pot

$(DOMAIN).pot: $(SRC)
	find ../ -name "*.php" ! -path "*.svn*" ! -path "*.bzr*" > ./potfiles.txt ;
	@# Generate POT File (Template).
	@# # `--keyword=tr' tells xgettext not to use the default _() function but your own tr() function.
	xgettext --keyword=__ --from-code=utf-8 --default-domain=$(DOMAIN) --output=$(DOMAIN).pot --files-from=./potfiles.txt ;
	@#
	rm -f ./potfiles.txt

mo:
	@for PO in `ls ./*/LC_MESSAGES/$(DOMAIN).po` ; do \
		msgfmt $$PO --output `echo $$PO | sed -e "s/\.po$$/.mo/"` ; \
	done ;

clean:
	rm -f ./*/LC_MESSAGES/$(DOMAIN).po~

create: pot
	@mkdir -p $(LANG)/LC_MESSAGES/
	msginit --no-translator --locale $(LANG) --output-file $(LANG)/LC_MESSAGES/$(DOMAIN).po --input ./$(DOMAIN).pot
	@rm -f ./$(DOMAIN).pot

edit: update-po
	$(EDITOR) ./$(LANG)/LC_MESSAGES/$(DOMAIN).po

update-po: $(DOMAIN).pot
	@for PO in `ls ./*/LC_MESSAGES/$(DOMAIN).po` ; do \
		echo -n "$$PO : "; \
		msgmerge --update $$PO ./$(DOMAIN).pot ; \
	done ;
	@rm -f ./$(DOMAIN).pot

stat:
	@for PO in `ls ./*/LC_MESSAGES/$(DOMAIN).po` ; do \
		echo -n "$$PO : "; \
		msgfmt --statistics $$PO ; \
	done ;
	@rm -f messages.mo
