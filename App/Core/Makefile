# ============================================
# $Id: Makefile,v 1.4 2005/10/05 14:38:07 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in $(appname)_post file.htaccess

$(pubdir)/file:
	mkdir -p $@

$(pubdir)/file/.htaccess: file.htaccess $(pubdir)/file
	cp $< $@

publish:  $(pubdir)/file/.htaccess