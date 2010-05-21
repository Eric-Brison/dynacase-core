# ============================================
# $Id: Makefile,v 1.2 2003/03/06 18:03:54 eric Exp $
# ============================================

include $(utildir)/PubRule

appname = WHAT

export appname

SUBDIR= htmlarea fckeditor jscalendar mcal jsXMLParser tsearch2 mozillaplugin

publish: $(pubdir)/log.sh $(pubdir)/WHAT/Lib.Prefix.php.in $(pubdir)/htmlarea $(pubdir)/jscalendar $(pubdir)/php2V2

$(pubdir)/log.sh: log.sh
	cp $< $@

$(pubdir)/WHAT/Lib.Prefix.php.in: Lib.Prefix.php.in
	cp $< $@

$(pubdir)/htmlarea :
	cp -r htmlarea $(pubdir)
	mkdir -p $(pubdir)/htmlarea/htmlarea
	ln -s ../images $(pubdir)/htmlarea/htmlarea/images
	ln -s ../popups $(pubdir)/htmlarea/htmlarea/popups

$(pubdir)/jscalendar :
	cp -r jscalendar $(pubdir)

$(pubdir)/php2V2 :
	cp -f php2V2 $(pubdir)/API
