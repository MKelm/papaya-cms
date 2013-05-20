<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_calendar_tag, actionbox_nextdates_tag
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./base/boxes.xsl" />
<xsl:import href="./base/match.xsl" />

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/free/calendar/actionbox_calendar.xsl"/>

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>