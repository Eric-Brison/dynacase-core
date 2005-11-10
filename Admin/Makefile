# ============================================
# $Id: Makefile,v 1.2 2005/11/10 15:43:56 eric Exp $
# ============================================



appname = admin
export appname
SUBDIR= Images
include $(utildir)/PubRule
DISTFILES += $(SUBDIR) winit.html shttpd

$(pubdir)/admin/winit.html: winit.html $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/shttpd: shttpd $(pubdir)/admin
	cp $< $@

publish:   $(pubdir)/admin/winit.html $(pubdir)/admin/shttpd
