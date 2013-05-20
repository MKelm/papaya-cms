<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="repeat-string.xsl" />

<xsl:template name="format-bytes">
  <xsl:param name="bytes" select="0" />
  <xsl:param name="digits" select="3" />
  <xsl:param name="mode">decimal</xsl:param>
  <xsl:variable name="factor">
    <xsl:choose>
      <xsl:when test="$mode = 'binary'">1024</xsl:when>
      <xsl:otherwise>1000</xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:variable name="buffer">
    <xsl:call-template name="round-number-by-digits">
      <xsl:with-param name="bytes" select="$bytes"/>
      <xsl:with-param name="factor" select="$factor"/>
      <xsl:with-param name="digits" select="$digits"/>
    </xsl:call-template>
  </xsl:variable>
  <xsl:variable name="number" select="number(substring-before($buffer, '#'))" />
  <xsl:variable name="unitLevel" select="number(substring-after($buffer, '#'))" />
  <xsl:variable name="floatingDigits" select="$digits - string-length(round($number))" />
  <xsl:variable name="numberPattern">
    <xsl:text>0</xsl:text>
    <xsl:if test="$floatingDigits &gt; 0">
      <xsl:text>.</xsl:text>
      <xsl:call-template name="repeat-string">
        <xsl:with-param name="str">0</xsl:with-param>
        <xsl:with-param name="count" select="$floatingDigits" />
      </xsl:call-template>
    </xsl:if>
  </xsl:variable>
  <xsl:call-template name="format-bytes-output">
    <xsl:with-param name="numberString">
      <xsl:call-template name="format-number">
        <xsl:with-param name="float" select="$number" />
        <xsl:with-param name="pattern" select="$numberPattern" />
      </xsl:call-template>
    </xsl:with-param>
    <xsl:with-param name="numberUnit">
      <xsl:choose>
        <xsl:when test="$mode = 'binary'">
          <xsl:choose>
            <xsl:when test="$unitLevel = 8">Yi</xsl:when>
            <xsl:when test="$unitLevel = 7">Zi</xsl:when>
            <xsl:when test="$unitLevel = 6">Ei</xsl:when>
            <xsl:when test="$unitLevel = 5">Pi</xsl:when>
            <xsl:when test="$unitLevel = 4">Ti</xsl:when>
            <xsl:when test="$unitLevel = 3">Gi</xsl:when>
            <xsl:when test="$unitLevel = 2">Mi</xsl:when>
            <xsl:when test="$unitLevel = 1">Ki</xsl:when>
          </xsl:choose>
        </xsl:when>
        <xsl:otherwise>
          <xsl:choose>
            <xsl:when test="$unitLevel = 8">Y</xsl:when>
            <xsl:when test="$unitLevel = 7">Z</xsl:when>
            <xsl:when test="$unitLevel = 6">E</xsl:when>
            <xsl:when test="$unitLevel = 5">P</xsl:when>
            <xsl:when test="$unitLevel = 4">T</xsl:when>
            <xsl:when test="$unitLevel = 3">G</xsl:when>
            <xsl:when test="$unitLevel = 2">M</xsl:when>
            <xsl:when test="$unitLevel = 1">k</xsl:when>
          </xsl:choose>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:text>B</xsl:text>
    </xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="round-number-by-digits">
  <xsl:param name="bytes" select="0" />
  <xsl:param name="digits" select="3" />
  <xsl:param name="factor" select="1024" />
  <xsl:param name="recursions" select="0" />
  <xsl:choose>
    <xsl:when test="string-length(round($bytes)) &gt; $digits">
      <xsl:call-template name="round-number-by-digits">
        <xsl:with-param name="bytes" select="$bytes div $factor"/>
        <xsl:with-param name="factor" select="$factor"/>
        <xsl:with-param name="digits" select="$digits"/>
        <xsl:with-param name="recursions" select="$recursions + 1"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$bytes" />
      <xsl:text>#</xsl:text>
      <xsl:value-of select="$recursions" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="format-bytes-output">
  <xsl:param name="numberString"></xsl:param>
  <xsl:param name="numberUnit"></xsl:param>
  <xsl:value-of select="$numberString" />
  <xsl:text> </xsl:text>
  <xsl:value-of select="$numberUnit"/>
</xsl:template>

</xsl:stylesheet>
