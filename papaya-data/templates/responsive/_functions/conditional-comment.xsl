<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:import href="nodes-to-text.xsl" />

  <xsl:template name="conditional-comment">
    <xsl:param name="content" />
    <xsl:param name="condition" select="'IE'" />
    <xsl:choose>
      <xsl:when test="starts-with($condition, '!')">
        <xsl:comment>
          <xsl:value-of select="concat('[if ', $condition, ']&gt; ')"/>
        </xsl:comment>
        <xsl:copy-of select="$content" />
        <xsl:comment>
          <xsl:text disable-output-escaping="yes"> &lt;![endif]</xsl:text>
        </xsl:comment>
      </xsl:when>
      <xsl:otherwise>
        <xsl:comment>
          <xsl:value-of select="concat('[if ', $condition, ']&gt;')"/>
          <xsl:call-template name="nodes-to-text">
            <xsl:with-param name="content" select="$content" />
          </xsl:call-template>
          <xsl:text disable-output-escaping="yes">&lt;![endif]</xsl:text>
        </xsl:comment>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
