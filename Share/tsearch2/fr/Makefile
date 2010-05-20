# ============================================
# $Id: Makefile,v 1.1 2007/04/27 08:19:46 eric Exp $
# ============================================

appname = locale/fr/tsearch2
export appname

include $(utildir)/PubRule

DISTFILES +=  $(wildcard french.*) 

$(pubdir)/$(appname) : 
	mkdir -p $@

$(pubdir)/$(appname)/french.aff : french.aff $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/french.dict : french.dict $(pubdir)/$(appname)
	cp $< $@
$(pubdir)/$(appname)/french.stop : french.stop $(pubdir)/$(appname)
	cp $< $@


publish:$(pubdir)/$(appname)/french.aff $(pubdir)/$(appname)/french.dict $(pubdir)/$(appname)/french.stop
