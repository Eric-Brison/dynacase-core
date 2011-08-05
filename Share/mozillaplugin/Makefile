# ============================================
# $Id: Makefile,v 1.7 2007/08/16 17:21:42 eric Exp $
# ============================================

appname=$(applib)

include $(utildir)/PubRule
DISTFILES += Makefile freedom.xml freedom-os.xml  freedom.src freedom.gif

plugin=$(pubdir)/moz-searchplugin

$(plugin):
	[ ! -d $(plugin) ] && mkdir -p $(plugin)

$(plugin)/freedom.src : $(plugin) freedom.src
	cp freedom.src $(plugin)

$(plugin)/freedom.gif :  $(plugin) freedom.gif
	cp freedom.gif $(plugin)

$(plugin)/freedom.xml : $(plugin) freedom.xml
	cp freedom.xml $(plugin)

$(plugin)/freedom-os.xml : $(plugin) freedom-os.xml
	cp freedom-os.xml $(plugin)


publish: $(plugin) $(plugin)/freedom.src $(plugin)/freedom.gif $(plugin)/freedom.xml $(plugin)/freedom-os.xml  
