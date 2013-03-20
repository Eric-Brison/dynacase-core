# ============================================
# $Id: Makefile,v 1.1 2004/08/09 08:07:06 eric Exp $
# ============================================

pages_mo = $(patsubst %.po,$(localedir)/en/LC_MESSAGES/%.mo,$(wildcard *.po))

include $(utildir)/PubRule

$(localedir)/en/LC_MESSAGES/%.mo:  /var/tmp/%.gmo  $(localedir)/en/LC_MESSAGES $(wildcard *.po)
	 $(installcp) $< $@









































