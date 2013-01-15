# ============================================
# $Id: Makefile,v 1.4 2008/12/31 14:36:20 jerome Exp $
# ============================================

appname = config
export appname
include $(utildir)/PubRule

$(pubdir)/$(appname)/dbaccess.php.in: dbaccess.php.in $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/local-dbaccess.php.sample: local-dbaccess.php.sample $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/mime.conf: mime.conf $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/mime-user.conf.sample: mime-user.conf.sample $(pubdir)/$(appname)
	cp $< $@

publish:  $(pubdir)/$(appname)/dbaccess.php.in $(pubdir)/$(appname)/local-dbaccess.php.sample $(pubdir)/$(appname)/mime.conf $(pubdir)/$(appname)/mime-user.conf.sample
