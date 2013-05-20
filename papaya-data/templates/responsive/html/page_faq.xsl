<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules content_faq
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./page_main.xsl"/>

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/free/faq/content_faq.xsl"/>

<!-- template parameters for module faq -->
<xsl:param name="FAQ_SHOW_LINK_SEARCH" select="true()" />
<xsl:param name="FAQ_SHOW_LINK_BACK" select="true()" />

<xsl:param name="FAQ_GROUP_COLUMN_COUNT" select="2" />
<xsl:param name="FAQ_ENTRY_COLUMN_COUNT" select="1" />
<xsl:param name="FAQ_SEARCH_COLUMN_COUNT" select="1" />

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>
