# ============================================
# $Id: Makefile,v 1.1 2006/12/22 14:38:45 eric Exp $
# ============================================

appname = fckeditor/editor/plugins/quicksave/lang
export appname

SUBDIR= 
include $(utildir)/PubRule

DISTFILES +=  $(SUBDIR) $(wildcard *.js) 


$(pubdir)/$(appname)/fr.js : fr.js
	(cd $(pubdir)/$(appname);ln -s  Layout/$< $<)

$(pubdir)/$(appname)/en.js : en.js
	(cd $(pubdir)/$(appname);ln -s  Layout/$< $<)

publish:$(pubdir)/$(appname)/fr.js $(pubdir)/$(appname)/en.js