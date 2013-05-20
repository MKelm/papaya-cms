<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="icon-panel">
  <xsl:param name="panel"/>
  <div class="iconPanel">
    <xsl:choose>
      <xsl:when test="$panel/groups">
        <xsl:for-each select="$panel/groups">
          <xsl:if test="@title and @href">
            <div class="iconPanelTitle">
              <a href="{@href}"><xsl:value-of select="@title"/></a>
            </div>
          </xsl:if>
          <xsl:call-template name="icon-panel-group">
            <xsl:with-param name="icons" select="$panel/icon"/>
          </xsl:call-template>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="icon-panel-group">
          <xsl:with-param name="icons" select="$panel/icon"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </div>
</xsl:template>

<xsl:template name="icon-panel-group">
  <xsl:param name="icons"/>
  <ul>
    <xsl:for-each select="$icons">
      <xsl:variable name="src">
        <xsl:call-template name="icon-url">
          <xsl:with-param name="icon-src" select="@src"/>
          <xsl:with-param name="icon-size">48</xsl:with-param>
        </xsl:call-template>
      </xsl:variable>
      <li>
        <a href="{@href}" class="icon"><img class="glyph48" src="{$src}" alt=""/></a>
        <a href="{@href}" class="subtitle"><xsl:value-of select="@subtitle"/></a>
      </li>
    </xsl:for-each>
  </ul>
</xsl:template>

<xsl:template match="iconpanel">
  <xsl:call-template name="icon-panel">
    <xsl:with-param name="panel" select="."/>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>