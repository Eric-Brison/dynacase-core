# ============================================
# $Id: Makefile,v 1.1 2006/08/02 10:19:33 marc Exp $
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
