<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:func="http://exslt.org/functions"
  xmlns:exsl="http://exslt.org/common"
  xmlns:papaya-fn="http://www.papaya-cms.com/ns/functions"
  extension-element-prefixes="func"
  exclude-result-prefixes="#default papaya-fn"
>

<xsl:param name="PAGE_BASE_URL" />

<func:function name="papaya-fn:transform-url-absolute">
  <xsl:param name="url"></xsl:param>
  <xsl:param name="baseUrl" select="$PAGE_BASE_URL" />
  <func:result>
    <xsl:call-template name="transform-url-absolute">
      <xsl:with-param name="url" select="$url"/>
      <xsl:with-param name="baseUrl" select="$PAGE_BASE_URL" />
    </xsl:call-template>
  </func:result>
</func:function>

<xsl:template name="transform-url-absolute">
  <xsl:param name="url"></xsl:param>
  <xsl:param name="baseUrl" select="$PAGE_BASE_URL" />
  <xsl:choose>
    <xsl:when test="contains($url, '://')">
      <xsl:value-of select="$url"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat($baseUrl, $url)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>