<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_catalog_navigation, actionbox_catalog_azlist, actionbox_catalog_related_categories, actionbox_catalog_limited_content
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./base/boxes.xsl" />

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/free/catalog/actionbox_catalog.xsl"/>

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>
