# ============================================
# $Id: Makefile,v 1.3 2004/02/24 08:39:16 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES +=  logo16.ico

$(pubdir)/$(appname)/logo16.ico : logo16.ico
	cp $< $(pubdir)/$(appname)/Images
publish: $(pubdir)/$(appname)/logo16.ico 
