<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="strong" mode="tags">
  <b><xsl:apply-templates mode="tags"/></b>
</xsl:template>

<xsl:template match="em" mode="tags">
  <i><xsl:apply-templates mode="tags"/></i>
</xsl:template>

<xsl:template match="img" mode="tags">
  <xsl:element name="{local-name()}">
    <xsl:copy-of select="@src"/>
  </xsl:element>
</xsl:template>

<xsl:template match="table" mode="tags">
  <xsl:element name="{local-name()}">
    <xsl:copy-of select="@*[name() != 'align']"/>
    <xsl:attribute name="align">left</xsl:attribute>
    <xsl:apply-templates select="./tr|./*/tr" mode="tags"/>
  </xsl:element>
</xsl:template>

<xsl:template match="tr|td" mode="tags">
  <xsl:element name="{local-name()}">
    <xsl:copy-of select="@*"/>
    <xsl:apply-templates select="node()" mode="tags"/>
  </xsl:element>
</xsl:template>

<xsl:template match="th" mode="tags">
  <xsl:element name="{local-name()}">
    <xsl:copy-of select="@*[name() != 'align']"/>
    <xsl:attribute name="align">center</xsl:attribute>
    <b><xsl:apply-templates select="node()" mode="tags"/></b>
  </xsl:element>
</xsl:template>

<xsl:template match="ul" mode="tags">
  <ul bullet-chars="&#xE01E;">
    <xsl:for-each select="li">
      <li><xsl:apply-templates select="node()" mode="tags"/></li>
    </xsl:for-each>
  </ul>
</xsl:template>

<xsl:template match="h1|h2|h3" mode="tags">
  <xsl:element name="{local-name()}">
    <xsl:if test="not(@align)">
      <xsl:attribute name="align">left</xsl:attribute>
    </xsl:if>
    <xsl:copy-of select="@*[name() != 'title']"/>
    <xsl:attribute name="title">
      <xsl:choose>
        <xsl:when test="@title and @title != ''"><xsl:value-of select="@title"/></xsl:when>
        <xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
    <xsl:apply-templates select="node()" mode="tags"/>
  </xsl:element>
</xsl:template>

<xsl:template match="br|p|b|i" mode="tags">
  <xsl:element name="{local-name()}">
    <xsl:copy-of select="@*"/>
    <xsl:apply-templates select="node()" mode="tags"/>
  </xsl:element>
</xsl:template>

<xsl:template match="a[@href]" mode="tags">
  <xsl:element name="{local-name()}">
    <a href="{@href}">
      <xsl:if test="@target and (@target != '_self')">
        <xsl:attribute name="target"><xsl:value-of select="@target" /></xsl:attribute>
      </xsl:if>
      <xsl:apply-templates select="node()" mode="tags"/>
    </a>
  </xsl:element>
</xsl:template>

<xsl:template match="a[@name]" mode="tags">
  <xsl:element name="{local-name()}">
    <xsl:copy-of select="@*[name() = 'name']"/>
    <xsl:apply-templates select="node()" mode="tags"/>
  </xsl:element>
</xsl:template>

</xsl:stylesheet>