generate-templates:
	xsltproc resources/templates/generate-program_management_program.xml \
		-o resources/templates/program_template.xml
	xsltproc resources/templates/generate-program_management_scrum_team.xml \
		-o resources/templates/scrum_team_template.xml
	xsltproc resources/templates/generate-program_management_config.xml \
		-o resources/templates/program_management_config.xml
	cp $(CURDIR)/../../tools/utils/setup_templates/generate-templates/trackers/testmanagement.xml \
		resources/templates/
