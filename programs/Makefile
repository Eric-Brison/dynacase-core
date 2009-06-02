# ============================================
# $id: Makefile.in,v 1.14 2002/01/04 16:04:35 eric Exp $
# ============================================

progdir=$(pubdir)/programs
appname = programs
include $(utildir)/PubRule

pages_sh = $(patsubst %.bash,$(progdir)/%,$(wildcard *.bash))
pages_php = $(patsubst %.php,$(progdir)/%,$(wildcard *.php))
DISTFILES += $(wildcard *.bash)


$(progdir)/%: %.bash $(progdir)
	cp $< $@
	chmod +x $@
$(progdir)/%: %.php $(progdir)
	cp $< $@
	chmod +x $@
	rm -f $@.php


publish: $(pages_sh) $(pages_php)
