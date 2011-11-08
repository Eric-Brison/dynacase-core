# ============================================
# $Id: Makefile,v 1.1 2008/08/06 15:11:52 eric Exp $
#
# ============================================



pages_fdl = $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard Method*.php))
pages_fdl += $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard Class*.php))

$(pubdir)/$(applib):
	mkdir $@

$(pubdir)/$(applib)/%.php: %.php $(pubdir)/$(applib)
	cd $(pubdir)/$(applib); \
	ln -sf ../$(appname)/$< .


include $(utildir)/PubRule
publish:$(pubdir)/$(applib)    $(pages_fdl)