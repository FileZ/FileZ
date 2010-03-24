PKG=filez-2.0.0-BETA2
PKGPATH=/tmp/$(PKG)

all:
	-rm -rf $(PKGPATH) $(PKGPATH).tgz
	svn export http://subversion.cru.fr/filez/trunk $(PKGPATH)
	cd $(PKGPATH) && cd .. && tar cvzf $(PKG).tgz $(PKG)
	rm -rf $(PKGPATH) 
	@echo	
	@echo "$(PKG).tgz release is out ! (in /tmp)"
	@echo

