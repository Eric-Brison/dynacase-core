# ============================================
# $Id: Makefile,v 1.3 2007/11/14 15:12:11 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname

SUBDIR= mime

include $(utildir)/PubRule

$(pubdir)/$(appname)/dynacase.ico : dynacase.ico
	cp $< $(pubdir)/$(appname)/Images

$(pubdir)/$(appname)/logo32.ico : logo32.ico
	cp $< $(pubdir)/$(appname)/Images

publish: $(pubdir)/$(appname)/logo32.ico $(pubdir)/$(appname)/dynacase.ico
