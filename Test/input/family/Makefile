# ============================================
# $Id: Makefile,v 1.1 2008/08/06 15:11:52 eric Exp $
#
# ============================================
SUBDIR=view control rapports

pages_csv += $(patsubst %.csv,$(pubdir)/$(style)/$(appname)/%.csv,$(filter-out $(pages_not_csv),$(wildcard *.csv)))



$(pubdir)/$(style)/$(appname)/%.csv : %.csv $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@

include $(utildir)/PubRule
publish: $(pages_csv) 