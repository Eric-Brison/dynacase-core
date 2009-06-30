# ============================================
# $Id: Makefile,v 1.4 2008/12/31 14:36:20 jerome Exp $
# ============================================



appname = admin
export appname
SUBDIR= Images
include $(utildir)/PubRule
DISTFILES += $(SUBDIR) winit.php shttpd htaccess htaccess.in htpasswd

$(pubdir)/admin/winit.php: winit.php $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/shttpd: shttpd $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/.htaccess: htaccess
	cp $< $@
$(pubdir)/admin/.htaccess.in: htaccess.in
	cp $< $@
$(pubdir)/admin/.htpasswd: htpasswd
	cp $< $@

publish:   $(pubdir)/admin/winit.php $(pubdir)/admin/shttpd $(pubdir)/admin/.htaccess $(pubdir)/admin/.htaccess.in $(pubdir)/admin/.htpasswd
