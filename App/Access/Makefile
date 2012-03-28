# ============================================
# $Id: Makefile,v 1.1 2002/01/08 12:41:34 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname

include $(utildir)/PubRule

DISTFILES = Makefile  $(appname).app $(appname)_init.php.in

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/EXTERNALS/haccess.php: $(pubdir)/EXTERNALS 
	cd $(pubdir)/EXTERNALS; \
	ln -sf ../ACCESS/haccess.php haccess.php

publish:$(pubdir)/EXTERNALS/haccess.php