# ============================================
# $Id: Makefile,v 1.4 2008/12/31 14:36:20 jerome Exp $
# ============================================



appname = admin
export appname
SUBDIR= Images Services
include $(utildir)/PubRule
DISTFILES += $(SUBDIR) index.html admin.css admin.js getServices.php knockout-2.0.0.js shttpd htaccess htaccess.in

$(pubdir)/admin/index.html: index.html $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/admin.css: admin.css $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/admin.js: admin.js $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/getServices.php: getServices.php $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/knockout-2.0.0.js: knockout-2.0.0.js $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/shttpd: shttpd $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/.htaccess: htaccess
	cp $< $@
$(pubdir)/admin/.htaccess.in: htaccess.in
	cp $< $@
$(pubdir)/admin/mime.conf: mime.conf $(pubdir)/admin
	cp $< $@
$(pubdir)/admin/mime-user.conf.sample: mime-user.conf.sample $(pubdir)/admin
	cp $< $@

publish:  $(pubdir)/admin/index.html $(pubdir)/admin/admin.css $(pubdir)/admin/admin.js $(pubdir)/admin/getServices.php $(pubdir)/admin/knockout-2.0.0.js $(pubdir)/admin/shttpd $(pubdir)/admin/.htaccess $(pubdir)/admin/.htaccess.in $(pubdir)/admin/mime.conf $(pubdir)/admin/mime-user.conf.sample
