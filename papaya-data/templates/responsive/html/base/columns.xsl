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

<!-- maximum columns for multiple column outputs of items (like subtopics) -->
<xsl:param name="MULTIPLE_COLUMNS_MAXIMUM" select="4" />

<xsl:template name="multiple-columns">
  <xsl:param name="items" select="./*" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="columnCount">2</xsl:param>
  <xsl:param name="groupCount" select="2"/>
  <xsl:param name="balanceMode">top</xsl:param>
  <xsl:call-template name="multiple-columns-div">
    <xsl:with-param name="items" select="$items"/>
    <xsl:with-param name="itemType" select="$itemType" />
    <xsl:with-param name="columnCountMax">
      <xsl:choose>
        <xsl:when test="$MULTIPLE_COLUMNS_MAXIMUM &gt; $columnCount">
          <xsl:value-of select="$columnCount"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$MULTIPLE_COLUMNS_MAXIMUM"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:with-param>
    <xsl:with-param name="groupCountMax" select="$groupCount"/>
    <xsl:with-param name="balanceMode" select="$balanceMode"/>
  </xsl:call-template>
</xsl:template>

<!-- multiple columns template, based on <div>s, calls "multiple-columns-div-line" for each line with n columns-->
<xsl:template name="multiple-columns-div">
  <xsl:param name="items" select="./*" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="columnCountMax">2</xsl:param>
  <xsl:param name="groupCountMax">2</xsl:param>
  <xsl:param name="balanceMode">top</xsl:param>
  <xsl:if test="$items and (count($items) &gt; 0)">
    <xsl:variable name="itemCount" select="count($items)"/>
    <xsl:variable name="rowCount" select="ceiling($itemCount div $columnCountMax)" />
    <xsl:variable name="balanceCount" select="$itemCount - ($rowCount * $columnCountMax)" />
    <xsl:variable name="balanceClass">
      <xsl:choose>
        <xsl:when test="$balanceMode = 'top'">
          <xsl:text>balanceTop</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>balanceBottom</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <div class="multipleColumns {$itemType}List {$balanceClass}">
      <xsl:choose>
        <xsl:when test="$columnCountMax = 1">
          <xsl:for-each select="$items">
            <xsl:call-template name="multiple-columns-div-line">
              <xsl:with-param name="items" select="."/>
              <xsl:with-param name="itemType" select="$itemType" />
            </xsl:call-template>
          </xsl:for-each>
        </xsl:when>
        <xsl:when test="$balanceMode = 'top'">
          <xsl:for-each select="$items[(position() mod $columnCountMax) = 1]">
            <xsl:variable name="indexRow" select="position()"/>
            <xsl:variable name="indexMax" select="($indexRow * $columnCountMax) + $balanceCount" />
            <xsl:variable name="indexMin" select="$indexMax - $columnCountMax" />
            <xsl:variable name="rowItems" select="$items[(position() &gt; $indexMin) and (position() &lt;= $indexMax)]"/>
            <xsl:call-template name="multiple-columns-div-line">
              <xsl:with-param name="items" select="$rowItems"/>
              <xsl:with-param name="itemType" select="$itemType" />
            </xsl:call-template>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="$items[(position() mod $columnCountMax) = 1]">
            <xsl:variable name="indexRow" select="position()"/>
            <xsl:variable name="indexMin" select="$indexRow * $columnCountMax - $columnCountMax" />
            <xsl:variable name="indexMax" select="$indexMin + $columnCountMax" />
            <xsl:variable name="rowItems" select="$items[(position() &gt; $indexMin) and (position() &lt;= $indexMax)]"/>
            <xsl:call-template name="multiple-columns-div-line">
              <xsl:with-param name="items" select="$rowItems"/>
              <xsl:with-param name="itemType" select="$itemType" />
            </xsl:call-template>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
    </div>
  </xsl:if>
</xsl:template>

<!-- multiple columns line template, based on <div>s, called by "multiple-columns-div" for each line-->
<xsl:template name="multiple-columns-div-line">
  <xsl:param name="items" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="groupCountMax" select="2"/>
  <xsl:variable name="columnCount" select="count($items)" />
  <xsl:if test="$items and $columnCount &gt; 0">
    <div>
      <xsl:attribute name="class">
        <xsl:text>line clearfix columnCount</xsl:text>
        <xsl:value-of select="$columnCount"/>
      </xsl:attribute>
      <xsl:for-each select="$items[(position() mod $groupCountMax) = 1]">
        <xsl:variable name="indexRow" select="position()"/>
        <xsl:variable name="indexMin" select="$indexRow * $groupCountMax - $groupCountMax" />
        <xsl:variable name="indexMax" select="$indexMin + $groupCountMax" />
        <xsl:variable name="rowItems" select="$items[(position() &gt; $indexMin) and (position() &lt;= $indexMax)]"/>
        <xsl:call-template name="multiple-columns-div-group">
          <xsl:with-param name="items" select="$rowItems"/>
          <xsl:with-param name="itemType" select="$itemType" />
          <xsl:with-param name="groupCountMax" select="$groupCountMax" />
          <xsl:with-param name="firstGroup" select="$indexMin = 0" />
          <xsl:with-param name="lastGroup" select="$indexMax &gt;= $columnCount" />
        </xsl:call-template>
      </xsl:for-each>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="multiple-columns-div-group">
  <xsl:param name="items" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="groupCount" select="count($items)"/>
  <xsl:param name="firstGroup" select="false()"/>
  <xsl:param name="lastGroup" select="false()"/>
  <xsl:if test="$items and $groupCount &gt; 0">
    <div>
      <xsl:attribute name="class">
        <xsl:text>frameGroup itemCount</xsl:text>
        <xsl:value-of select="$groupCount"/>
      </xsl:attribute>
      <xsl:for-each select="$items">
        <div class="frame">
          <xsl:variable name="borderPosition">
            <xsl:if test="$firstGroup and position() = 1">
              <xsl:text> lineFirst</xsl:text>
            </xsl:if>
            <xsl:if test="$lastGroup and position() = $groupCount">
              <xsl:text> lineLast</xsl:text>
            </xsl:if>
          </xsl:variable>
          <div>
            <xsl:attribute name="class">
              <xsl:text>item</xsl:text>
              <xsl:choose>
                <xsl:when test="$itemType != 'item'">
                  <xsl:text> </xsl:text>
                  <xsl:value-of select="$itemType"/>
                </xsl:when>
              </xsl:choose>
              <xsl:if test="$borderPosition != ''">
                <xsl:value-of select="$borderPosition" />
              </xsl:if>
            </xsl:attribute>
            <xsl:call-template name="multiple-columns-item">
              <xsl:with-param name="item" select="." />
              <xsl:with-param name="itemType" select="$itemType" />
            </xsl:call-template>
          </div>
        </div>
      </xsl:for-each>
    </div>
  </xsl:if>
</xsl:template>


<!-- generic multiple columns item template, called by "multiple-columns-line" for each item,
     overload for own items, use "itemType" for different item types -->
<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
</xsl:template>

</xsl:stylesheet>