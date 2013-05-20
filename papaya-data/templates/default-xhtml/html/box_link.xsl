<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_pagelink_extended
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:template match="extended_pagelink">
  <xsl:apply-templates select="text-before/node()" />
  <xsl:text> </xsl:text>
  <a href="{link/@href}">
    <xsl:if test="popup">
      <xsl:attribute name="onclick">
        <xsl:text>return openPopup('</xsl:text>
          <xsl:value-of select="popup/@href" />
          <xsl:text>', '</xsl:text>
          <xsl:value-of select="popup/@name" />
          <xsl:text>', </xsl:text>
          <xsl:value-of select="popup/@width" />
          <xsl:text>, </xsl:text>
          <xsl:value-of select="popup/@height" />
          <xsl:text>, '</xsl:text>
          <xsl:value-of select="popup/@scrollbars" />
          <xsl:text>', '</xsl:text>
          <xsl:value-of select="popup/@resizeable" />
          <xsl:text>', '</xsl:text>
          <xsl:value-of select="popup/@toolbar" />
        <xsl:text>'); return false;</xsl:text>
      </xsl:attribute>
      <xsl:attribute name="target">_blank</xsl:attribute>
    </xsl:if>
    <xsl:apply-templates select="link/node()" />
  </a>
  <xsl:text> </xsl:text>
  <xsl:apply-templates select="text-after/node()" />
</xsl:template>

</xsl:stylesheet>
