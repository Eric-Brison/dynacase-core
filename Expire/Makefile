# ============================================
# $Id: Makefile,v 1.1 2003/08/11 15:41:37 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [A-Z] [a-z])

export appname


include $(utildir)/PubRule


DISTFILES += $(wildcard *html)

pages_html = $(patsubst %html,$(pubdir)/$(appname)/%html,$(wildcard *html))


$(pubdir)/$(appname)/%html : %html $(pubdir)/$(appname)
	cp $< $@

publish : $(pages_html)
