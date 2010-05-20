# ============================================
# $Id: Makefile,v 1.8 2006/08/15 13:56:11 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

rootprefix=$(RPM_BUILD_ROOT)
export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in usercard.ods init.sql userinit.ods personnldap.ods slapd_anakeen.conf


$(pubdir)/$(appname)/usercard.ods : usercard.ods 
	cp $< $@
$(pubdir)/$(appname)/%.ods : %.ods
	cp $< $@
$(pubdir)/$(appname)/%.csv : %.csv
	cp $< $@

#$(rootprefix)/etc/openldap:
#	mkdir -p $@
#
#$(rootprefix)/etc/openldap/slapd_anakeen.conf:slapd_anakeen.conf $(rootprefix)/etc/openldap
#	cp slapd_anakeen.conf $@

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/EXTERNALS/usercard.php: $(pubdir)/EXTERNALS
	cp $(appname)_external.php $(pubdir)/EXTERNALS/usercard.php

publish: $(pubdir)/$(appname)/usercard.ods $(pubdir)/EXTERNALS/usercard.php $(pubdir)/$(appname)/userinit.ods $(pubdir)/$(appname)/personnldap.ods 
#$(rootprefix)/etc/openldap/slapd_anakeen.conf
