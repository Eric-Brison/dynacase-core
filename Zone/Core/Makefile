# ============================================
# $Id: Makefile,v 1.4 2007/11/12 14:55:57 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname

$(pubdir)/$(appname)/wversion.js.in: wversion.js.in
	cp $< $@

include $(utildir)/PubRule


DISTFILES += html2odt.xsl

$(pubdir)/$(appname)/Layout/html2odt.xsl: html2odt.xsl
	cp $< $@

publish:$(pubdir)/$(appname)/wversion.js.in $(pubdir)/$(appname)/Layout/html2odt.xsl
