# ============================================
# $Id: Makefile,v 1.3 2007/12/04 09:58:07 marc Exp $
# ============================================



appname = admin
export appname
SUBDIR= Images
include $(utildir)/PubRule
DISTFILES += $(SUBDIR) winit.php shttpd

$(pubdir)/admin/winit.php: winit.php $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/shttpd: shttpd $(pubdir)/admin
	cp $< $@

publish:   $(pubdir)/admin/winit.php $(pubdir)/admin/shttpd
