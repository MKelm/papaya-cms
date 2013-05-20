<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="repeat-string">
  <xsl:param name="str"/>
  <xsl:param name="count">1</xsl:param>
  <xsl:if test="$count &gt; 0">
    <xsl:value-of select="$str"/>
    <xsl:if test="$count &gt; 1">
      <xsl:call-template name="repeat-string">
        <xsl:with-param name="str" select="$str" />
        <xsl:with-param name="count" select="$count - 1" />
      </xsl:call-template>
    </xsl:if>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
