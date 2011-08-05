# ============================================
# $Id: Makefile,v 1.1 2002/03/20 11:26:03 marc Exp $
# ============================================

export doc = users
export pubdoc = $(pubdir)/USERS/doc

SUBDIR= images

include $(utildir)/doc.mk

$(pubdoc)/helpid.php:
	$(utildir)/create_index.sh $(doc) > $@

publish: $(pubdoc)/helpid.php

DISTFILES= images Makefile $(wildcard *.sgml) 
