# ============================================
# $Id: Makefile,v 1.1 2006/12/22 14:38:45 eric Exp $
# ============================================


appname = lib/ckeditor/3/plugins/quicksave
plug4 = lib/ckeditor/4/plugins
export appname

include $(utildir)/PubRule

DISTFILES +=  $(SUBDIR) $(wildcard *.js) 


$(pubdir)/$(appname)/plugin.js : plugin.js
	(cd $(pubdir)/$(appname);ln -s  Layout/$< $<)

$(pubdir)/$(appname)/floppy.png : floppy.png
	(cd $(pubdir)/$(appname);ln -s  Images/$< $<)

$(pubdir)/$(plug4):
	mkdir -p $@

$(pubdir)/$(plug4)/quicksave : $(pubdir)/$(plug4)
	(cd $(pubdir)/$(plug4) ; ln -sf  ../../../ckeditor/3/plugins/quicksave .)

publish:$(pubdir)/$(appname)/plugin.js $(pubdir)/$(appname)/floppy.png $(pubdir)/$(plug4)/quicksave
