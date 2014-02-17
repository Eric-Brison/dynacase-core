# ============================================
# $Id: Makefile,v 1.1 2002/02/13 14:31:59 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname
SUBDIR=Schemas
include $(utildir)/PubRule
pages_sql = $(patsubst %.sql,$(pubdir)/$(appname)/Layout/%.sql,$(wildcard *.sql))


$(pubdir)/$(appname)/Layout/%.sql : %.sql $(pubdir)/$(appname)/Layout
	$(installcp) $< $@

publish: $(pages_sql)
