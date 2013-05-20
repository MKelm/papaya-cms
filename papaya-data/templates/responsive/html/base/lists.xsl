<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  IMPORTANT! DO NOT CHANGE THIS FILE!

  If you need to change one of the templates just define a template with the
  same name in your xsl file. This will override the imported template from
  this file.
-->

<xsl:template name="list">
  <xsl:param name="items" select="./*" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="listClass" />
  <xsl:param name="isRecursive" select="true()" />
  <xsl:if test="$items and count($items) &gt; 0">
    <ul>
      <xsl:choose>
        <xsl:when test="$listClass and $listClass != ''">
          <xsl:attribute name="class"><xsl:value-of select="$listClass"/></xsl:attribute>
        </xsl:when>
        <xsl:when test="not($listClass)">
          <xsl:attribute name="class"><xsl:value-of select="$itemType"/>List</xsl:attribute>
        </xsl:when>
      </xsl:choose>
      <xsl:for-each select="$items">
        <li>
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="position() = 1 and position() = last()">first last odd</xsl:when>
              <xsl:when test="position() = 1">first odd</xsl:when>
              <xsl:when test="position() = last() and not(position() mod 2)">last odd</xsl:when>
              <xsl:when test="position() = last()">last even</xsl:when>
              <xsl:when test="not(position() mod 2)">odd</xsl:when>
              <xsl:otherwise>even</xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:call-template name="list-item">
            <xsl:with-param name="item" select="."/>
            <xsl:with-param name="itemType" select="$itemType"/>
          </xsl:call-template>
          <xsl:if test="$isRecursive">
            <xsl:variable name="currentTagName" select="name()"/>
            <xsl:call-template name="list">
              <xsl:with-param name="items" select="./*[name() = $currentTagName]"/>
              <xsl:with-param name="itemType" select="$itemType"/>
              <xsl:with-param name="listClass" select="''" />
              <xsl:with-param name="isRecursive" select="true()" />
            </xsl:call-template>
          </xsl:if>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="list-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:call-template name="list-item-default">
    <xsl:with-param name="item" select="$item" />
    <xsl:with-param name="itemType" select="$itemType" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="list-item-default">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:choose>
    <xsl:when test="$item/@href">
      <a href="{$item/@href}">
        <xsl:if test="$item/@target">
          <xsl:attribute name="target"><xsl:value-of select="$item/@target" /></xsl:attribute>
        </xsl:if>
        <xsl:choose>
          <xsl:when test="$item/@title">
            <xsl:value-of select="$item/@title" />
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates select="$item/node()" mode="richtext"/>
          </xsl:otherwise>
        </xsl:choose>
      </a>
    </xsl:when>
    <xsl:otherwise>
      <xsl:choose>
        <xsl:when test="$item/@title">
          <xsl:value-of select="$item/@title" />
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates select="$item/node()" mode="richtext"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>