<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  get an excerpt of the given text - from text start to last whitespace before the given length
-->
<xsl:template name="text-excerpt">
  <xsl:param name="text" />
  <xsl:param name="length" />
  <!-- Do not give the following parameter -->
  <xsl:param name="recursionString" select="''"/>
  <xsl:if test="$text and $length">
    <xsl:variable name="start" select="string-length($recursionString)"/>
    <xsl:variable name="nextWord" select="substring-before(normalize-space(substring-after($text, $recursionString)), ' ')"/>
    <xsl:choose>
      <xsl:when test="string-length($text) &lt;= $length">
        <xsl:value-of select="$text" />
      </xsl:when>
      <xsl:when test="not(contains($text, ' '))">
        <xsl:value-of select="substring($text, 0, $length)" />
      </xsl:when>
      <xsl:when test="(string-length($nextWord) + $start) &lt; $length">
        <xsl:call-template name="text-excerpt">
          <xsl:with-param name="text" select="$text"/>
          <xsl:with-param name="length" select="$length"/>
          <xsl:with-param name="recursionString">
            <xsl:value-of select="$recursionString" />
            <xsl:if test="$recursionString != ''">
              <xsl:text> </xsl:text>
            </xsl:if>
            <xsl:value-of select="$nextWord" />
          </xsl:with-param>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$recursionString" />
        <xsl:text> </xsl:text>
        <xsl:value-of select="$nextWord" />
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
