# ============================================
service=$(shell basename `pwd`)
pubfiles = $(patsubst %,$(pubdir)/$(appname)/service.d/$(service)/%,$(filter-out %~, $(filter-out Makefile%,$(wildcard *))))

DISTFILES = $(pubfiles) Makefile

$(pubdir)/$(appname)/service.d/$(service):
	mkdir -p $@

$(pubdir)/$(appname)/service.d/$(service)/% : % $(pubdir)/$(appname)/service.d/$(service)
	cp $< $@

publish : $(pubfiles)
