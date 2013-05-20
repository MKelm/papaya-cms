<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

  <xsl:template name="blockquote">
    <xsl:param name="content" />
    <xsl:param name="prefix" select="'„'" />
    <xsl:param name="suffix" select="'”'" />
    <xsl:variable name="textNodes" select="$content//text()" />
    <blockquote>
      <xsl:call-template name="blockquote-recursion">
        <xsl:with-param name="nodes" select="$content/node()" />
        <xsl:with-param name="firstNodeId" select="generate-id($textNodes[1])"/>
        <xsl:with-param name="lastNodeId" select="generate-id($textNodes[position() = last()])"/>
        <xsl:with-param name="prefix" select="$prefix" />
        <xsl:with-param name="suffix" select="$suffix" />
      </xsl:call-template>
    </blockquote>
  </xsl:template>

  <xsl:template name="blockquote-recursion">
    <xsl:param name="nodes" />
    <xsl:param name="firstNodeId"></xsl:param>
    <xsl:param name="lastNodeId"></xsl:param>
    <xsl:param name="prefix" select="'„'" />
    <xsl:param name="suffix" select="'”'" />
    <xsl:for-each select="$nodes">
      <xsl:choose>
        <xsl:when test="self::text()">
          <xsl:if test="generate-id() = $firstNodeId">
            <xsl:value-of select="$prefix" />
          </xsl:if>
          <xsl:copy-of select="." />
          <xsl:if test="generate-id() = $lastNodeId">
            <xsl:value-of select="$suffix" />
          </xsl:if>
        </xsl:when>
        <xsl:otherwise>
          <xsl:element name="{name()}">
            <xsl:copy-of select="@*"/>
            <xsl:call-template name="blockquote-recursion">
              <xsl:with-param name="nodes" select="./node()" />
              <xsl:with-param name="firstNodeId" select="$firstNodeId"/>
              <xsl:with-param name="lastNodeId" select="$lastNodeId"/>
              <xsl:with-param name="prefix" select="$prefix" />
              <xsl:with-param name="suffix" select="$suffix" />
            </xsl:call-template>
          </xsl:element>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>