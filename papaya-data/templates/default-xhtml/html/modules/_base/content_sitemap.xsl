<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_sitemap.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_sitemap'">
      <xsl:call-template name="module-content-sitemap">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-default">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-sitemap">
  <xsl:param name="pageContent"/>
  <h1><xsl:value-of select="$pageContent/title"/></h1>
  <div class="contentSitemap">
    <xsl:call-template name="module-content-sitemap-items">
      <xsl:with-param name="items" select="$pageContent/sitemap/mapitem"/>
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="module-content-sitemap-items">
  <xsl:param name="items"/>
  <xsl:if test="count($items) &gt; 0">
    <ul>
      <xsl:for-each select="$items">
        <xsl:variable name="lastItem" select="position() = last()" />
        <xsl:call-template name="module-content-sitemap-item">
          <xsl:with-param name="item" select="."/>
          <xsl:with-param name="lastItem" select="$lastItem"/>
        </xsl:call-template>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-sitemap-item">
  <xsl:param name="item"/>
  <xsl:param name="lastItem" select="false()" />
  <li>
    <xsl:if test="$lastItem">
      <xsl:attribute name="class">last</xsl:attribute>
    </xsl:if>
    <xsl:choose>
      <xsl:when test="$item/@href">
        <a href="{$item/@href}">
          <xsl:if
            test="$item/@target and $item/@target != '' and $item/@target != '_self'">
            <xsl:attribute name="target"><xsl:value-of
              select="$item/@target" /></xsl:attribute>
          </xsl:if>
          <xsl:value-of select="$item/@title" />
        </a>
      </xsl:when>
      <xsl:otherwise>
        <span class="noLinkItem">
          <xsl:value-of select="$item/@title" />
        </span>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:call-template name="module-content-sitemap-items">
      <xsl:with-param name="items" select="$item/mapitem"/>
    </xsl:call-template>
  </li>
</xsl:template>

</xsl:stylesheet>
