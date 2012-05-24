# ============================================
# $Id: Makefile,v 1.1 2006/12/22 14:38:45 eric Exp $
# ============================================

appname = ckeditor/plugins/quicksave
export appname

include $(utildir)/PubRule

DISTFILES +=  $(SUBDIR) $(wildcard *.js) 


$(pubdir)/$(appname)/plugin.js : plugin.js
	(cd $(pubdir)/$(appname);ln -s  Layout/$< $<)

$(pubdir)/$(appname)/floppy.png : floppy.png
	(cd $(pubdir)/$(appname);ln -s  Images/$< $<)

publish:$(pubdir)/$(appname)/plugin.js $(pubdir)/$(appname)/floppy.png