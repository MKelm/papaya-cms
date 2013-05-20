<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="ascii-to-upper">
  <xsl:param name="text"/>
  <xsl:value-of select="translate(
      $text,
      'abcdefghijklmnopqrstuvwxyz',
      'ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
</xsl:template>

<xsl:template name="calendar-date-format-slash">
  <xsl:param name="date"/>
  <xsl:variable name="year" select="substring-after(substring-after($date,'/'),'/')"/>
  <xsl:variable name="month" select="substring-before($date,'/')"/>
  <xsl:variable name="day" select="substring-before(substring-after($date, '/'),'/')"/>
  <xsl:call-template name="format-date">
    <xsl:with-param name="date">
      <xsl:value-of select="$year"/>
      <xsl:text>-</xsl:text>
      <xsl:value-of select="$month"/>
      <xsl:text>-</xsl:text>
      <xsl:value-of select="$day"/>
    </xsl:with-param>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>