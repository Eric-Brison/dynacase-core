# ============================================
# $Id: Makefile,v 1.2 2002/01/08 17:52:03 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES +=  logo16.ico logo32.ico 

$(pubdir)/$(appname)/logo16.ico : logo16.ico
	cp $< $(pubdir)/$(appname)/Images
$(pubdir)/$(appname)/logo32.ico : logo32.ico
	cp $< $(pubdir)/$(appname)/Images
publish: $(pubdir)/$(appname)/logo16.ico $(pubdir)/$(appname)/logo32.ico
