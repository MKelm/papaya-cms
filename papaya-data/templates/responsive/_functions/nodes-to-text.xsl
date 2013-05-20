<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:exsl="http://exslt.org/common"
  extension-element-prefixes="exsl"
  exclude-result-prefixes="#default"
>

  <xsl:template name="nodes-to-text">
    <xsl:param name="content" />
    <xsl:if test="not(function-available('exsl:node-set'))">
      <xsl:message terminate="yes">This template requires EXSLT!</xsl:message>
    </xsl:if>
    <xsl:for-each select="exsl:node-set($content)/node()">
      <xsl:choose>
        <xsl:when test="not(name() = '')">
          <xsl:text disable-output-escaping="yes">&lt;</xsl:text>
          <xsl:value-of select="name()" />
          <xsl:for-each select="@*">
            <xsl:text> </xsl:text>
            <xsl:value-of select="name()" />
            <xsl:text>="</xsl:text>
            <xsl:value-of select="." />
            <xsl:text>"</xsl:text>
          </xsl:for-each>
          <xsl:choose>
            <xsl:when test="node()">
              <xsl:text disable-output-escaping="yes">&gt;</xsl:text>
              <xsl:call-template name="nodes-to-text">
                <xsl:with-param name="content" select="." />
              </xsl:call-template>
              <xsl:text disable-output-escaping="yes">&lt;/</xsl:text>
              <xsl:value-of select="name()" />
              <xsl:text disable-output-escaping="yes">&gt;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text disable-output-escaping="yes">/&gt;</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="." />
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
