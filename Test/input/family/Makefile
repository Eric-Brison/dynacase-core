# ============================================
# $Id: Makefile,v 1.1 2008/08/06 15:11:52 eric Exp $
#
# ============================================
SUBDIR=view control rapports

pages_csv += $(patsubst %.csv,$(pubdir)/$(style)/$(appname)/%.csv,$(filter-out $(pages_not_csv),$(wildcard *.csv)))
pages_fdl = $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard Method*.php))
pages_fdl += $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard Class*.php))

$(pubdir)/$(applib):
	mkdir $@

$(pubdir)/$(applib)/%.php: %.php $(pubdir)/$(applib)
	cd $(pubdir)/$(applib); \
	ln -sf ../$(appname)/$< .


$(pubdir)/$(style)/$(appname)/%.csv : %.csv $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@

include $(utildir)/PubRule
publish: $(pages_csv) $(pubdir)/$(applib) $(pages_fdl) 