# ============================================
# $Id: Makefile,v 1.3 2003/09/17 15:33:04 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule




$(pubdir)/authent.php: authent.php
	cd $(pubdir);ln -sf  ./$(appname)/$< .

publish: $(pubdir)/authent.php
