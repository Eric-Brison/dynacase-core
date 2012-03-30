# ============================================
# $Id: Makefile,v 1.11 2007/11/14 16:56:13 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in $(appname)_init.sh fdl.ods freeaction.ods \
	 fdl.sql

$(pubdir)/$(appname)/$(appname)_init.sh : $(appname)_init.sh
	cp $< $@

$(pubdir)/$(appname)/fdl.ods : fdl.ods 
	cp fdl.ods $(pubdir)/$(appname)

$(pubdir)/$(appname)/freeaction.ods : freeaction.ods 
	cp freeaction.ods $(pubdir)/$(appname)

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/FDLGEN :
	mkdir -p $@

$(pubdir)/EXTERNALS/fdl.php: $(pubdir)/EXTERNALS $(pubdir)/FDLGEN
	cd $(pubdir)/EXTERNALS; \
	ln -sf ../FDL/$(appname)_external.php fdl.php

$(pubdir)/EXTERNALS/helppage.php: $(pubdir)/EXTERNALS 
	cd $(pubdir)/EXTERNALS; \
	ln -sf ../FDL/helppage.php helppage.php

$(pubdir)/EXTERNALS/usercard.php: $(pubdir)/EXTERNALS 
	cd $(pubdir)/EXTERNALS; \
	ln -sf ../FDL/usercard.php usercard.php

publish: $(pubdir)/$(appname)/$(appname)_init.sh $(pubdir)/$(appname)/fdl.ods $(pubdir)/$(appname)/freeaction.ods $(pubdir)/EXTERNALS/fdl.php $(pubdir)/EXTERNALS/helppage.php $(pubdir)/EXTERNALS/usercard.php


