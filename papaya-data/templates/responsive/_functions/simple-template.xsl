<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:exsl="http://exslt.org/common"
  extension-element-prefixes="exsl"
  exclude-result-prefixes="#default"
>

<xsl:param name="SIMPLE_TEMPLATE_TOKEN_START">{%</xsl:param>
<xsl:param name="SIMPLE_TEMPLATE_TOKEN_END">%}</xsl:param>

<xsl:template name="simple-template">
  <xsl:param name="text"></xsl:param>
  <xsl:param name="values"/>
  <xsl:choose>
    <xsl:when test="$text/node()">
      <xsl:for-each select="$text/node()">
        <xsl:choose>
          <xsl:when test="self::*">
            <xsl:element name="{local-name()}">
              <xsl:copy-of select="@*"/>
              <xsl:call-template name="simple-template">
                <xsl:with-param name="text" select="."/>
                <xsl:with-param name="values" select="$values"/>
              </xsl:call-template>
            </xsl:element>
          </xsl:when>
          <xsl:when test="self::text()">
            <xsl:call-template name="simple-template-text">
              <xsl:with-param name="text" select="self::text()"/>
              <xsl:with-param name="values" select="$values"/>
            </xsl:call-template>
            <xsl:text> </xsl:text>
          </xsl:when>
        </xsl:choose>
      </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="simple-template-text">
        <xsl:with-param name="text" select="$text"/>
        <xsl:with-param name="values" select="$values"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="simple-template-text">
  <xsl:param name="text"></xsl:param>
  <xsl:param name="values"/>
  <xsl:variable name="hasTokenStart" select="contains($text, $SIMPLE_TEMPLATE_TOKEN_START)" />
  <xsl:choose>
    <xsl:when test="$hasTokenStart">
      <xsl:value-of select="substring-before($text, $SIMPLE_TEMPLATE_TOKEN_START)" />
      <xsl:call-template name="simple-template-token">
        <xsl:with-param name="text" select="substring-after($text, $SIMPLE_TEMPLATE_TOKEN_START)" />
        <xsl:with-param name="values" select="$values"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$text" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="simple-template-token">
  <xsl:param name="text"></xsl:param>
  <xsl:param name="values"/>
  <xsl:variable name="hasTokenEnd" select="contains($text, $SIMPLE_TEMPLATE_TOKEN_END)" />
  <xsl:if test="$hasTokenEnd">
    <xsl:variable name="token" select="substring-before($text, $SIMPLE_TEMPLATE_TOKEN_END)"/>
    <xsl:choose>
      <xsl:when test="function-available('exsl:object-type')">
        <xsl:choose>
          <xsl:when test="exsl:object-type($values) = 'RTF'">
            <xsl:value-of select="exsl:node-set($values)/*[name() = $token]"/>
          </xsl:when>
          <xsl:when test="exsl:object-type($values) = 'node-set'">
            <xsl:value-of select="$values/*[name() = $token]"/>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$values and count($values/*) &gt; 0 and $values/*[name() = $token]">2
        <xsl:value-of select="$values/*[name() = $token]"/>
      </xsl:when>
    </xsl:choose>
    <xsl:call-template name="simple-template-text">
      <xsl:with-param name="text" select="substring-after($text, $SIMPLE_TEMPLATE_TOKEN_END)" />
      <xsl:with-param name="values" select="$values"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
