# ============================================
# $Id: Makefile,v 1.4 2008/12/31 14:36:20 jerome Exp $
# ============================================



appname = supervisor
export appname
SUBDIR= Images Services
include $(utildir)/PubRule
DISTFILES += $(SUBDIR) index.html supervisor.css supervisor.js getServices.php shttpd htaccess htaccess.in

$(pubdir)/$(appname)/index.html: index.html $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/supervisor.css: supervisor.css $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/supervisor.js: supervisor.js $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/getServices.php: getServices.php $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/shttpd: shttpd $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/.htaccess: htaccess
	cp $< $@
$(pubdir)/$(appname)/.htaccess.in: htaccess.in
	cp $< $@
$(pubdir)/$(appname)/mime.conf: mime.conf $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/mime-user.conf.sample: mime-user.conf.sample $(pubdir)/$(appname)
	cp $< $@

publish:  $(pubdir)/$(appname)/index.html $(pubdir)/$(appname)/supervisor.css $(pubdir)/$(appname)/supervisor.js $(pubdir)/$(appname)/getServices.php $(pubdir)/$(appname)/shttpd $(pubdir)/$(appname)/.htaccess $(pubdir)/$(appname)/.htaccess.in $(pubdir)/$(appname)/mime.conf $(pubdir)/$(appname)/mime-user.conf.sample
