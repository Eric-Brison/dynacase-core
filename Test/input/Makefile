# ============================================
# $Id: Makefile,v 1.1 2008/08/06 15:11:52 eric Exp $
#
# ============================================
SUBDIR=family
pages_zip += $(patsubst %.zip,$(pubdir)/$(style)/$(appname)/%.zip,$(filter-out $(pages_not_zip),$(wildcard *.zip)))
$(pubdir)/$(style)/$(appname)/%.zip : %.zip $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@

pages_tgz += $(patsubst %.tgz,$(pubdir)/$(style)/$(appname)/%.tgz,$(filter-out $(pages_not_tgz),$(wildcard *.tgz)))
$(pubdir)/$(style)/$(appname)/%.tgz : %.tgz $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@


include $(utildir)/PubRule
publish: $(pages_zip)  $(pages_tgz)

