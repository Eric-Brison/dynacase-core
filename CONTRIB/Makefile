# ============================================
# $Id: Makefile,v 1.2 2008/05/29 05:54:07 marc Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname

include $(utildir)/PubRule

DISTFILES += Makefile  \
	  freedom-cleanattr.sh

$(pubdir)/$(appname) : 
	mkdir $(pubdir)/$(appname)

$(pubdir)/$(appname)/freedom-cleanattr.sh : freedom-cleanattr.sh
	/bin/cp $< $@
	chmod +x $(pubdir)/$(appname)/freedom-cleanattr.sh


publish: $(pubdir)/$(appname)  \
	$(pubdir)/$(appname)/freedom-cleanattr.sh
