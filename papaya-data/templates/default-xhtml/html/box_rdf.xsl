<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_rdf
-->

<xsl:import href="./base/boxes.xsl" />
<xsl:import href="./modules/free/include/box_rdf.xsl" />

<xsl:template match="feed">
  <xsl:if test="rdf/rss[@version = '2.0']">
    <xsl:call-template name="module-rdf-feed-rss2">
      <xsl:with-param name="items" select="rdf/rss[@version = '2.0']/channel/item" />
      <xsl:with-param name="limit" select="@maximum" />
      <xsl:with-param name="linkTitle" select="@headlinelink" />
      <xsl:with-param name="showDescriptions" select="@description" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
