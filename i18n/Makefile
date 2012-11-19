# $Id: Makefile 2012-11-19 10:45:07Z eliovir $
EDITOR=lokalize
LANG=eo
DOMAIN=default

all:   update-po mo

$(DOMAIN).pot:
	find ../ -name "*.php" ! -path "*.svn*" > ../i18n/potfiles.txt ;
	# Generate POT File (Template).
	# # `--keyword=tr' tells xgettext not to use the default _() function but your own tr() function.
	xgettext --keyword=__ --from-code=utf-8 --default-domain=$(DOMAIN) --output=$(DOMAIN).pot --files-from=../i18n/potfiles.txt ;
	#
	rm -f ../i18n/potfiles.txt

mo:
	@for PO in `ls ../i18n/*/LC_MESSAGES/$(DOMAIN).po` ; do \
		msgfmt $$PO --output `echo $$PO | sed -e "s/\.po$$/.mo/"` ; \
	done ;

update-po: $(DOMAIN).pot
	for PO in `ls ../i18n/*/LC_MESSAGES/$(DOMAIN).po` ; do \
		echo -n "$$PO : "; \
		msgmerge --update $$PO ../i18n/$(DOMAIN).pot ; \
	done ;
	rm -f ../i18n/$(DOMAIN).pot

edit: update-po
	$(EDITOR) ../i18n/$(LANG)/LC_MESSAGES/$(DOMAIN).po

stat:
	@for PO in `ls ../i18n/*/LC_MESSAGES/$(DOMAIN).po` ; do \
		echo -n "$$PO : "; \
		msgfmt --statistics $$PO ; \
	done ;
