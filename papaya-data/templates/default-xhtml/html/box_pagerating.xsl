<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  @papaya:modules actionbox_pagerating
-->

<xsl:import href="./base/match.xsl" />
<xsl:import href="./base/boxes.xsl" />

<xsl:template match="links">
  <div class="pagerating">
    <ul>
      <xsl:for-each select="link">
        <li>
          <a href="{@href}"><xsl:value-of select="@value"/></a>
        </li>
      </xsl:for-each>
    </ul>
  </div>
</xsl:template>

<xsl:template match="pageranking">
  <xsl:variable name="position">
    <xsl:value-of select="rating/@src"/>
    <xsl:text>?img[position]=</xsl:text>
    <xsl:value-of select="rating/@value"/>
  </xsl:variable>
  <xsl:variable name="value">
    <xsl:value-of select="rating/@value"/>
    <xsl:text> %</xsl:text>
  </xsl:variable>
  <div class="ranking">
    <img alt="{$value}" src="{$position}" title="{$value}" />
  </div>
</xsl:template>

</xsl:stylesheet>