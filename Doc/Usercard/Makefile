# ============================================
# $Id: Makefile,v 1.2 2002/08/19 12:15:34 eric Exp $
# ============================================


export doc = $(shell basename `pwd` | tr [A-Z] [a-z])
export pubdoc = $(pubdir)/$(shell basename `pwd` | tr [a-z] [A-Z])/doc


SUBDIR= images

include $(utildir)/doc.mk

$(pubdoc)/helpid.php: $(doc).sgml
	$(utildir)/create_index.sh $(doc) > $@

publish: $(pubdoc)/helpid.php

DISTFILES= images Makefile $(wildcard *.sgml) 
