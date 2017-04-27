# ============================================
# $Id: Makefile,v 1.1 2006/11/24 09:40:06 eric Exp $
# ============================================

appname = CORE/cleanContext
export appname

include $(utildir)/PubRule

pages_sql = $(patsubst %.sql,$(pubdir)/$(style)/$(appname)/%.sql,$(filter-out $(pages_not_sql),$(wildcard *.sql)))

$(pubdir)/$(style)/$(appname)/%.sql : %.sql $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@

publish: $(pages_sql)
