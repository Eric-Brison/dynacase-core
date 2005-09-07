# ============================================
# $Id: Makefile,v 1.3 2005/09/07 14:56:13 yannick Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in $(appname)_post.in
