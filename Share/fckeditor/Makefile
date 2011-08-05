# ============================================
# $Id: Makefile,v 1.1 2006/12/22 14:38:45 eric Exp $
# ============================================

appname = fckeditor/editor/plugins/quicksave
export appname

SUBDIR= lang
include $(utildir)/PubRule

DISTFILES +=  $(SUBDIR) $(wildcard *.js) 


$(pubdir)/$(appname)/fckplugin.js : fckplugin.js
	(cd $(pubdir)/$(appname);ln -s  Layout/$< $<)

$(pubdir)/$(appname)/floppy.png : floppy.png
	(cd $(pubdir)/$(appname);ln -s  Images/$< $<)

publish:$(pubdir)/$(appname)/fckplugin.js $(pubdir)/$(appname)/floppy.png