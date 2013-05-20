<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="replace-substring">
  <xsl:param name="original"/>
  <xsl:param name="substring"/>
  <xsl:param name="replacement" select="''" />
  <xsl:choose>
    <xsl:when test="contains($original, $substring)">
      <xsl:variable name="firstPart" select="substring-before($original, $substring)" />
      <xsl:value-of select="concat($firstPart, $replacement)" />
      <xsl:variable name="lastPart" select="substring($original, string-length($firstPart) + 2)" />
      <xsl:call-template name="replace-substring">
        <xsl:with-param name="original" select="$lastPart" />
        <xsl:with-param name="substring" select="$substring" />
        <xsl:with-param name="replacement" select="$replacement" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$original" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
