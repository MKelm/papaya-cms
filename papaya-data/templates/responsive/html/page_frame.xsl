<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules content_frame
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./page_main.xsl"/>

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/free/include/content_frame.xsl"/>

<!-- iframes need space - so disable the additional content column (right column) -->
<xsl:param name="DISABLE_ADDITIONAL_COLUMN" select="true()" />

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>
