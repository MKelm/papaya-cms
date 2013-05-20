<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_include.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_frame'">
      <xsl:call-template name="module-content-frame">
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

<xsl:template name="module-content-frame">
  <xsl:param name="pageContent" />
  <iframe class="embedPage" height="{$pageContent/height}" src="{$pageContent/url}">
    <div>
      <xsl:call-template name="language-text">
        <xsl:with-param name="text">MSG_NO_IFRAME</xsl:with-param>
      </xsl:call-template>
      <a href="{$pageContent/url}"><xsl:value-of select="$pageContent/url"/></a>
    </div>
  </iframe>
</xsl:template>

</xsl:stylesheet>