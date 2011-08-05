# ============================================
# $Id: Makefile,v 1.1 2006/06/21 10:14:26 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
dirname = $(shell basename `pwd` | tr [a-z] [A-Z])
appname = STYLE/$(dirname)
export appname


include $(utildir)/PubRule


DISTFILES +=  $(dirname).sty

$(pubdir)/$(appname)/$(dirname).sty : $(dirname).sty
	cp $< $@

publish: $(pubdir)/$(appname)/$(dirname).sty
