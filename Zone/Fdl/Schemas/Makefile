# ============================================
# $Id: Makefile,v 1.1 2002/02/13 14:31:59 eric Exp $
# ============================================


include $(utildir)/PubRule
pages_xsd = $(patsubst %.xsd,$(pubdir)/$(style)/$(appname)/Layout/%.xsd,$(filter-out $(pages_not_xml),$(wildcard *.xsd)))

$(pubdir)/$(style)/$(appname)/Layout/%.xsd : %.xsd $(pubdir)/$(style)/$(appname)/Layout
	$(installcp) $< $@
publish: $(pages_xsd)
