# ============================================
# $Id: Makefile,v 1.1 2002/01/08 12:41:34 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule

$(pubdir)/$(appname)/%.csv : %.csv
	cp  $< $@

DISTFILES = Makefile  $(appname).app $(appname)_init.php.in authent.csv

