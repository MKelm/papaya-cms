<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="max">
  <xsl:param name="values"/>
  <xsl:choose>
    <xsl:when test="function-available('max')"><xsl:value-of select="max($values)"/></xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="emulateMinMax">
        <xsl:with-param name="values" select="$values"/>
        <xsl:with-param name="mode">max</xsl:with-param>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="min">
  <xsl:param name="values"/>
  <xsl:choose>
    <xsl:when test="function-available('min')"><xsl:value-of select="min($values)"/></xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="emulateMinMax">
        <xsl:with-param name="values" select="$values"/>
        <xsl:with-param name="mode">min</xsl:with-param>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="emulateMinMax">
  <xsl:param name="values"/>
  <xsl:param name="current">1</xsl:param>
  <xsl:param name="saved">0</xsl:param>
  <xsl:param name="mode">max</xsl:param>
  <xsl:variable name="result">
    <xsl:choose>
      <xsl:when test="$current = 1"><xsl:value-of select="$values[position() = 1]"/></xsl:when>
      <xsl:when test="$mode = 'max' and $saved &gt;= $values[position() = $current]"><xsl:value-of select="$saved"/></xsl:when>
      <xsl:when test="$mode = 'min' and $saved &lt;= $values[position() = $current]"><xsl:value-of select="$saved"/></xsl:when>
      <xsl:otherwise><xsl:value-of select="$values[position() = $current]"/></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:variable name="next" select="$current + 1"/>
  <xsl:choose>
    <xsl:when test="$next &gt; count($values)"><xsl:value-of select="$result"/></xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="emulateMinMax">
        <xsl:with-param name="values" select="$values"/>
        <xsl:with-param name="current" select="$next"/>
        <xsl:with-param name="saved" select="$result"/>
        <xsl:with-param name="mode" select="$mode"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
