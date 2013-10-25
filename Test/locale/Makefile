# ============================================
# $Id: Makefile,v 1.1 2004/08/09 08:07:06 eric Exp $
# ============================================

pages_pofr = $(patsubst %.po,$(localedir)/fr/LC_MESSAGES/SRC/%.po,$(wildcard *_fr.po))
pages_poen = $(patsubst %.po,$(localedir)/en/LC_MESSAGES/SRC/%.po,$(wildcard *_en.po))
page_mo =

include $(utildir)/PubRule
page_mo =

$(localedir)/fr/LC_MESSAGES/SRC :
	mkdir -p $@

$(localedir)/fr/LC_MESSAGES/SRC/%.po:  %.po $(localedir)/fr/LC_MESSAGES/SRC 
	 $(installcp) $< $@

$(localedir)/en/LC_MESSAGES/SRC :
	mkdir -p $@

$(localedir)/en/LC_MESSAGES/SRC/%.po:  %.po $(localedir)/en/LC_MESSAGES/SRC 
	 $(installcp) $< $@

publish: $(pages_pofr) $(pages_poen)
