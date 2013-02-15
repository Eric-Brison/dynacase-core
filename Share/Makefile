# ============================================
# $Id: Makefile,v 1.2 2003/03/06 18:03:54 eric Exp $
# ============================================

appname = WHAT

export appname

include $(utildir)/PubRule
include $(rootdir)/corePubRule.mk

SUBDIR= ckeditor-quicksave ckeditor-docattr jscalendar jsXMLParser mozillaplugin jscolor

publish: $(pubdir)/log.sh $(pubdir)/WHAT/Lib.Prefix.php.in $(pubdir)/jscalendar $(pubdir)/php2V2 $(pubdir)/FDL/geticon.php

$(pubdir)/log.sh: log.sh
	cp $< $@

$(pubdir)/WHAT/Lib.Prefix.php.in: Lib.Prefix.php.in
	cp $< $@

$(pubdir)/FDL/geticon.php: geticon.php
	ln -s ../WHAT/geticon.php $@

$(pubdir)/jscalendar :
	cp -r jscalendar $(pubdir)

$(pubdir)/php2V2 :
	cp -f php2V2 $(pubdir)/API
