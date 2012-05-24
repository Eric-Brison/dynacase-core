# ============================================
# $Id: Makefile,v 1.1 2006/12/22 14:38:45 eric Exp $
# ============================================

appname = ckeditor/plugins/docattr
export appname

include $(utildir)/PubRule

DISTFILES +=  $(SUBDIR) $(wildcard *.js) 


$(pubdir)/$(appname)/plugin.js : plugin.js
	(cd $(pubdir)/$(appname);ln -s  Layout/$< $<)


publish:$(pubdir)/$(appname)/plugin.js