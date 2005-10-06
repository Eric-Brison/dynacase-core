# ============================================
# $Id: Makefile,v 1.1 2005/10/06 10:13:04 marc Exp $
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
