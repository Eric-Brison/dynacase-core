# ============================================
# $Id: Makefile,v 1.2 2003/08/11 15:41:37 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule




$(pubdir)/authent.php: authent.php
	ln -sf  $(pubdir)/$(appname)/$< $@

publish: $(pubdir)/authent.php
