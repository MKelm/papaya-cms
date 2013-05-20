<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="sheet">
  <xsl:param name="sheet"/>
  <div class="sheetBackground">
    <div class="sheetShadow">
      <xsl:if test="$sheet/@width">
        <xsl:attribute name="style">width: <xsl:value-of select="$sheet/@width"/></xsl:attribute>
      </xsl:if>
      <div class="sheet">
        <xsl:call-template name="sheet-header">
          <xsl:with-param name="header" select="$sheet/header"/>
        </xsl:call-template>
        <xsl:choose>
          <xsl:when test="count($sheet/text//p) &gt; 0"><xsl:apply-templates select="$sheet/text/*|$sheet/text/text()" mode="allowHTML" /></xsl:when>
          <xsl:when test="text">
            <div class="teletype">
              <xsl:apply-templates select="$sheet/text/*|$sheet/text/text()" mode="allowHTML" />
            </div>
          </xsl:when>
        </xsl:choose>
      </div>
    </div>
  </div>
</xsl:template>

<xsl:template name="sheet-header">
  <xsl:param name="header"/>
  <xsl:if test="$header">
    <div class="header">
      <xsl:for-each select="$header/lines/line">
        <div class="{@class}"><xsl:apply-templates/></div>
      </xsl:for-each>
      <xsl:if test="count($header/infos/line) &gt; 0">
        <div class="infos">
          <xsl:for-each select="$header/infos/line">
            <span class="info"><xsl:apply-templates/></span>
          </xsl:for-each>
        </div>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template match="sheet">
  <xsl:call-template name="sheet">
    <xsl:with-param name="sheet" select="."/>
  </xsl:call-template>
</xsl:template>


</xsl:stylesheet>