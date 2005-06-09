# ============================================
# $Id: Makefile,v 1.2 2005/06/09 16:43:56 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


$(pubdir)/$(appname)/wversion.js.in: wversion.js.in
	cp $< $@

include $(utildir)/PubRule



publish:$(pubdir)/$(appname)/wversion.js.in
