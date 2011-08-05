# ============================================
# $Id: Makefile,v 1.1 2006/11/24 09:40:06 eric Exp $
# ============================================

appname = dav/Windows
export appname


include $(utildir)/PubRule

DISTFILES +=   $(wildcard opendav*)

$(pubdir)/$(appname):
	mkdir -p $@

$(pubdir)/$(appname)/open%: open% $(pubdir)/$(appname)
	cp $< $@

$(pubdir)/$(appname)/.htaccess: windows.htaccess
	cp $< $@

publish:$(pubdir)/$(appname)/opendav.reg $(pubdir)/$(appname)/opendav.vbs $(pubdir)/$(appname)/.htaccess