pages_sql = $(patsubst %.sql,$(pubdir)/$(style)/$(appname)/%.sql,$(filter-out $(pages_not_sql),$(wildcard *.sql)))
$(pubdir)/$(style)/$(appname)/%.sql : %.sql $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@

DISTFILES += $(wildcard *.sql)

publish: $(pages_sql)