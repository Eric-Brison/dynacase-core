# ============================================
# $Id: Makefile,v 1.1 2003/08/11 15:41:37 eric Exp $
# ============================================

appname = expire

export appname

include $(utildir)/PubRule

pages_html = $(patsubst %html,$(pubdir)/$(appname)/%html,$(wildcard *html))

$(pubdir)/$(appname)/%html : %html $(pubdir)/$(appname)
	cp $< $@

publish : $(pages_html)
