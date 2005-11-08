# ============================================
# $Id: Makefile,v 1.1 2005/11/08 17:16:34 eric Exp $
# ============================================



appname = admin
export appname

include $(utildir)/PubRule
DISTFILES += winit.html

$(pubdir)/admin/winit.html: winit.html $(pubdir)/admin
	cp $< $@

publish:   $(pubdir)/admin/winit.html
