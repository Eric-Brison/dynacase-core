# ============================================
# $Id: Makefile,v 1.2 2003/03/06 18:03:54 eric Exp $
# ============================================

appname = WHAT

export appname

include $(utildir)/PubRule
include $(rootdir)/corePubRule.mk

SUBDIR= ckeditor-quicksave ckeditor-docattr jscalendar jsXMLParser mozillaplugin jscolor PHPMailer Dcp

publish: $(pubdir)/log.sh $(pubdir)/jscalendar $(pubdir)/FDL/geticon.php

$(pubdir)/log.sh: log.sh
	cp $< $@

$(pubdir)/FDL/geticon.php: geticon.php
	ln -s ../WHAT/geticon.php $@

$(pubdir)/jscalendar :
	cp -r jscalendar $(pubdir)

