# ============================================
# $Id: Makefile,v 1.3 2005/06/10 13:05:18 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname

$(pubdir)/$(appname)/wversion.js.in: wversion.js.in
	cp $< $@

include $(utildir)/PubRule



publish:$(pubdir)/$(appname)/wversion.js.in 
