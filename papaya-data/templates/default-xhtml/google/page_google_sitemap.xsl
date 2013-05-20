<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.google.com/schemas/sitemap/0.84">
<!--
  @papaya:modules content_sitemap
-->

<xsl:output method="xml" encoding="UTF-8" standalone="yes" indent="yes" omit-xml-declaration="no" />

<xsl:template match="/page">
  <xsl:variable name="module" select="content/topic/@module"/>
  <xsl:choose>
    <xsl:when test="$module = 'content_sitemap'">
      <xsl:call-template name="module-content-sitemap">
        <xsl:with-param name="topic" select="content/topic" />
      </xsl:call-template>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-sitemap">
  <xsl:param name="topic" />
  <xsl:variable name="host">
    <xsl:choose>
      <xsl:when test="$topic/host">
        <xsl:text>http://</xsl:text>
        <xsl:value-of select="$topic/host"/>
        <xsl:text>/</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>/</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <urlset>
    <xsl:call-template name="module-content-sitemap-items">
      <xsl:with-param name="items" select="$topic/sitemap/mapitem" />
      <xsl:with-param name="host" select="$host" />
    </xsl:call-template>
  </urlset>
</xsl:template>

<xsl:template name="module-content-sitemap-items">
  <xsl:param name="items" />
  <xsl:param name="host" />
  <xsl:for-each select="$items">
    <xsl:call-template name="module-content-sitemap-item">
      <xsl:with-param name="item" select="."/>
      <xsl:with-param name="host" select="$host" />
    </xsl:call-template>
    <xsl:if test="mapitem">
      <xsl:call-template name="module-content-sitemap-items">
       <xsl:with-param name="items" select="mapitem" />
       <xsl:with-param name="host" select="$host" />
      </xsl:call-template>
    </xsl:if>
  </xsl:for-each>
</xsl:template>

<xsl:template name="module-content-sitemap-item">
  <xsl:param name="item" />
  <xsl:param name="host" />
  <url>
    <loc><xsl:value-of select="$host" /><xsl:value-of select="$item/@href"/></loc>
    <lastmod><xsl:value-of select="substring($item/@lastmod,0,11)"/></lastmod>
    <changefreq><xsl:value-of select="$item/@changefreq"/></changefreq>
    <priority><xsl:value-of select="$item/@priority"/></priority>
  </url>
</xsl:template>

</xsl:stylesheet>