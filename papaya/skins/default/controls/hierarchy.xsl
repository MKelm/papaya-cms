<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="hierarchy-menu">
  <xsl:param name="menu" />
  <div class="toolBar">
    <div class="hierarchyMenu">
      <ul>
        <xsl:for-each select="$menu/items/item">
          <xsl:call-template name="hierarchy-menu-item">
            <xsl:with-param name="menu" select="$menu"/>
            <xsl:with-param name="item" select="."/>
            <xsl:with-param name="isFirst" select="position() = 1"/>
            <xsl:with-param name="isLast" select="position() = last()"/>
          </xsl:call-template>
        </xsl:for-each>
      </ul>
      <xsl:call-template name="float-fix"/>
    </div>
  </div>
</xsl:template>

<xsl:template name="hierarchy-menu-item">
  <xsl:param name="menu"/>
  <xsl:param name="item"/>
  <xsl:param name="isFirst"/>
  <xsl:param name="isLast"/>
  <li>
    <xsl:attribute name="class">
      <xsl:text>item</xsl:text>
      <xsl:if test="$isFirst">
        <xsl:text> first</xsl:text>
      </xsl:if>
      <xsl:if test="$isLast">
        <xsl:text> last</xsl:text>
      </xsl:if>
    </xsl:attribute>
    <xsl:if test="$item/@image != '' and $item/@mode != 'text'">
      <xsl:variable name="imageUrl">
        <xsl:call-template name="icon-url">
          <xsl:with-param name="icon-src" select="$item/@image"/>
        </xsl:call-template>
      </xsl:variable>
      <xsl:variable name="imageText">
        <xsl:choose>
          <xsl:when test="$item/@mode = 'image'">
            <xsl:value-of select="$item/@caption"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text></xsl:text>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:choose>
        <xsl:when test="$item/@href">
          <a href="{$item/@href}" class="icon"><img src="{$imageUrl}" alt="{$imageText}" title="{$imageText}"/></a>
        </xsl:when>
        <xsl:otherwise>
          <span class="icon"><img src="{$imageUrl}"  alt="{$imageText}" title="{$imageText}"/></span>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:if>
    <xsl:if test="$item/@mode != 'image'">
      <xsl:choose>
        <xsl:when test="$item/@href">
          <a href="{$item/@href}" class="caption">
            <xsl:value-of select="$item/@caption" />
          </a>
        </xsl:when>
        <xsl:otherwise>
          <span class="caption"><xsl:value-of select="$item/@caption" /></span>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:text> </xsl:text>
    </xsl:if>
  </li>
</xsl:template>

<xsl:template match="hierarchy-menu">
  <xsl:call-template name="hierarchy-menu">
    <xsl:with-param name="menu" select="."/>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>