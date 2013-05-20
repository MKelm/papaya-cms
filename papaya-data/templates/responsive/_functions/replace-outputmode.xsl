<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="PAGE_OUTPUTMODE_CURRENT">php</xsl:param>
<xsl:param name="PAGE_OUTPUTMODE_DEFAULT">html</xsl:param>

<xsl:template name="replace-outputmode">
  <xsl:param name="href" />
  <xsl:param name="replace" select="$PAGE_OUTPUTMODE_CURRENT"/>
  <xsl:param name="with" select="$PAGE_OUTPUTMODE_DEFAULT"/>
  <xsl:param name="attachPreview" select="false()" />
  <xsl:param name="queryString"></xsl:param>
  <xsl:param name="fragment"></xsl:param>

  <xsl:variable name="hrefLength" select="string-length($href)" />
  <xsl:variable name="modeLength" select="string-length($replace)" />

  <xsl:choose>
    <xsl:when test="contains($href, '?')">
      <xsl:call-template name="replace-outputmode">
        <xsl:with-param name="href" select="substring-before($href, '?')" />
        <xsl:with-param name="replace" select="$replace" />
        <xsl:with-param name="with" select="$with" />
        <xsl:with-param name="attachPreview" select="$attachPreview" />
        <xsl:with-param name="queryString" select="concat('?', substring-after($href, '?'))" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="contains($href, '#')">
      <xsl:call-template name="replace-outputmode">
        <xsl:with-param name="href" select="substring-before($href, '#')" />
        <xsl:with-param name="replace" select="$replace" />
        <xsl:with-param name="with" select="$with" />
        <xsl:with-param name="attachPreview" select="$attachPreview" />
        <xsl:with-param name="fragment" select="concat('#', substring-after($href, '#'))" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="substring($href, $hrefLength - 7) = '.preview'">
      <xsl:call-template name="replace-outputmode">
        <xsl:with-param name="href" select="substring($href, 1, $hrefLength - 8)" />
        <xsl:with-param name="replace" select="$replace" />
        <xsl:with-param name="with" select="$with" />
        <xsl:with-param name="attachPreview" select="true()" />
        <xsl:with-param name="queryString" select="$queryString" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="substring($href, $hrefLength - $modeLength) = concat('.', $replace)">
      <xsl:value-of select="substring($href, 1, $hrefLength - $modeLength)" />
      <xsl:value-of select="$with" />
      <xsl:if test="$attachPreview">.preview</xsl:if>
      <xsl:value-of select="$queryString" />
      <xsl:value-of select="$fragment" />
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$href" />
      <xsl:if test="$attachPreview">.preview</xsl:if>
      <xsl:value-of select="$queryString" />
      <xsl:value-of select="$fragment" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
