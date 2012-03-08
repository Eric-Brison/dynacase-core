# ============================================
service=$(shell basename `pwd`)
pubfiles = $(patsubst %,$(pubdir)/admin/service.d/$(service)/%,$(filter-out %~, $(filter-out Makefile%,$(wildcard *))))

DISTFILES = $(pubfiles) Makefile

$(pubdir)/admin/service.d/$(service):
	mkdir -p $@

$(pubdir)/admin/service.d/$(service)/% : % $(pubdir)/admin/service.d/$(service)
	cp $< $@

publish : $(pubfiles)
