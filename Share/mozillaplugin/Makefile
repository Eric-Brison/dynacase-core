# ============================================
# $Id: Makefile,v 1.7 2007/08/16 17:21:42 eric Exp $
# ============================================

appname=$(applib)

include $(utildir)/PubRule
DISTFILES += Makefile freedom.xml freedom-os.xml  freedom.src dynacase-opensearch.gif

plugin=$(pubdir)/moz-searchplugin

$(plugin):
	[ ! -d $(plugin) ] && mkdir -p $(plugin)

$(plugin)/freedom.src : $(plugin) freedom.src
	cp freedom.src $(plugin)

$(plugin)/dynacase-opensearch.gif :  $(plugin) dynacase-opensearch.gif
	cp dynacase-opensearch.gif $(plugin)

$(plugin)/freedom.xml : $(plugin) freedom.xml
	cp freedom.xml $(plugin)

$(plugin)/freedom-os.xml : $(plugin) freedom-os.xml
	cp freedom-os.xml $(plugin)


publish: $(plugin) $(plugin)/freedom.src $(plugin)/dynacase-opensearch.gif $(plugin)/freedom.xml $(plugin)/freedom-os.xml  
