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
<xsl:param name="MULTIPLE_COLUMNS_MAXIMUM" select="3" />
<xsl:param name="MULTIPLE_COLUMNS_TABLE" select="false()" />

<xsl:template name="multiple-columns">
  <xsl:param name="items" select="./*" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="columnCount">2</xsl:param>
  <xsl:param name="columnCountMax" select="$MULTIPLE_COLUMNS_MAXIMUM" />
  <xsl:param name="useTable" select="$MULTIPLE_COLUMNS_TABLE"/>
  <xsl:choose>
    <xsl:when test="$useTable">
      <xsl:call-template name="multiple-columns-table">
        <xsl:with-param name="items" select="$items"/>
        <xsl:with-param name="itemType" select="$itemType" />
        <xsl:with-param name="columnCount" select="$columnCount" />
        <xsl:with-param name="columnCountMax" select="$columnCountMax" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="multiple-columns-div">
        <xsl:with-param name="items" select="$items"/>
        <xsl:with-param name="itemType" select="$itemType" />
        <xsl:with-param name="columnCount" select="$columnCount" />
        <xsl:with-param name="columnCountMax" select="$columnCountMax" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- multiple columns template, based on <div>s, calls "multiple-columns-div-line" for each line with n columns-->
<xsl:template name="multiple-columns-div">
  <xsl:param name="items" select="./*" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="columnCount">2</xsl:param>
  <xsl:param name="columnCountMax" select="$MULTIPLE_COLUMNS_MAXIMUM" />
  <xsl:if test="$items and (count($items) &gt; 0)">
    <div class="multipleColumns {$itemType}List">
      <xsl:choose>
        <xsl:when test="number($columnCount) &gt; number($columnCountMax)">
          <xsl:for-each select="$items[(position() mod $columnCountMax) = 1]">
            <xsl:variable name="indexMin" select="position() * $columnCountMax - $columnCountMax" />
            <xsl:variable name="indexMax" select="position() * $columnCountMax" />
            <xsl:call-template name="multiple-columns-div-line">
              <xsl:with-param name="items" select="$items[(position() &gt; $indexMin) and (position() &lt;= $indexMax)]"/>
              <xsl:with-param name="itemType" select="$itemType" />
              <xsl:with-param name="columnCount" select="$columnCountMax" />
            </xsl:call-template>
          </xsl:for-each>
        </xsl:when>
        <xsl:when test="number($columnCount) &gt; 1">
          <xsl:for-each select="$items[(position() mod $columnCount) = 1]">
            <xsl:variable name="indexMin" select="position() * $columnCount - $columnCount" />
            <xsl:variable name="indexMax" select="position() * $columnCount" />
            <xsl:call-template name="multiple-columns-div-line">
              <xsl:with-param name="items" select="$items[(position() &gt; $indexMin) and (position() &lt;= $indexMax)]"/>
              <xsl:with-param name="itemType" select="$itemType" />
              <xsl:with-param name="columnCount" select="$columnCount" />
            </xsl:call-template>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="$items">
            <xsl:call-template name="multiple-columns-div-line">
              <xsl:with-param name="items" select="."/>
              <xsl:with-param name="itemType" select="$itemType" />
              <xsl:with-param name="columnCount" select="1" />
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
  <xsl:param name="columnCount" />
  <xsl:if test="$items and count($items) &gt; 0">
    <div class="line">
      <xsl:for-each select="$items">
        <xsl:variable name="itemWidth">
          <xsl:choose>
            <xsl:when test="$columnCount &gt; 1">
              <xsl:value-of select="format-number((100 div $columnCount) - 0.1, '##0.##')" />
            </xsl:when>
            <xsl:otherwise>100</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <div class="frame">
          <xsl:if test="number($itemWidth) &gt; 0">
            <xsl:attribute name="style">width: <xsl:value-of select="$itemWidth" />%;</xsl:attribute>
          </xsl:if>
          <xsl:variable name="borderPosition">
            <xsl:choose>
              <xsl:when test="position() = 1 and position() = $columnCount">lineFirst lineLast</xsl:when>
              <xsl:when test="position() = 1">lineFirst</xsl:when>
              <xsl:when test="position() = $columnCount">lineLast</xsl:when>
              <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
          </xsl:variable>
          <div>
            <xsl:attribute name="class">
              <xsl:choose>
                <xsl:when test="$borderPosition != ''">
                  <xsl:value-of select="$itemType" />
                  <xsl:text> item </xsl:text>
                  <xsl:value-of select="$borderPosition" />
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="$itemType" />
                  <xsl:text> item</xsl:text>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
            <xsl:call-template name="multiple-columns-item">
              <xsl:with-param name="item" select="." />
              <xsl:with-param name="itemType" select="$itemType" />
            </xsl:call-template>
          </div>
        </div>
      </xsl:for-each>
      <xsl:call-template name="float-fix" />
    </div>
  </xsl:if>
</xsl:template>

<!-- multiple columns template, based on <table>, calls "multiple-columns-table-line" for each line with n columns-->
<xsl:template name="multiple-columns-table">
  <xsl:param name="items" select="./*" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="columnCount">2</xsl:param>
  <xsl:param name="columnCountMax" select="$MULTIPLE_COLUMNS_MAXIMUM" />
  <xsl:if test="$items and (count($items) &gt; 0)">
    <table class="multipleColumns {$itemType}List">
      <tbody>
        <xsl:choose>
          <xsl:when test="number($columnCount) &gt; number($columnCountMax)">
            <xsl:for-each select="$items[(position() mod $columnCountMax) = 1]">
              <xsl:variable name="indexMin" select="position() * $columnCountMax - $columnCountMax" />
              <xsl:variable name="indexMax" select="position() * $columnCountMax" />
              <xsl:call-template name="multiple-columns-table-line">
                <xsl:with-param name="items" select="$items[(position() &gt; $indexMin) and (position() &lt;= $indexMax)]"/>
                <xsl:with-param name="itemType" select="$itemType" />
                <xsl:with-param name="columnCount" select="$columnCountMax" />
              </xsl:call-template>
            </xsl:for-each>
          </xsl:when>
          <xsl:when test="number($columnCount) &gt; 1">
            <xsl:for-each select="$items[(position() mod $columnCount) = 1]">
              <xsl:variable name="indexMin" select="position() * $columnCount - $columnCount" />
              <xsl:variable name="indexMax" select="position() * $columnCount" />
              <xsl:call-template name="multiple-columns-table-line">
                <xsl:with-param name="items" select="$items[(position() &gt; $indexMin) and (position() &lt;= $indexMax)]"/>
                <xsl:with-param name="itemType" select="$itemType" />
                <xsl:with-param name="columnCount" select="$columnCount" />
              </xsl:call-template>
            </xsl:for-each>
          </xsl:when>
          <xsl:otherwise>
            <xsl:for-each select="$items">
              <xsl:call-template name="multiple-columns-table-line">
                <xsl:with-param name="items" select="."/>
                <xsl:with-param name="itemType" select="$itemType" />
                <xsl:with-param name="columnCount" select="1" />
              </xsl:call-template>
            </xsl:for-each>
          </xsl:otherwise>
        </xsl:choose>
      </tbody>
    </table>
  </xsl:if>
</xsl:template>

<!-- multiple columns line template, based on <div>s, called by "multiple-columns-div" for each line-->
<xsl:template name="multiple-columns-table-line">
  <xsl:param name="items" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:param name="columnCount" />
  <xsl:if test="$items and count($items) &gt; 0">
    <tr>
      <xsl:for-each select="$items">
        <xsl:variable name="itemWidth">
          <xsl:choose>
            <xsl:when test="$columnCount &gt; 1">
              <xsl:value-of select="format-number((100 div $columnCount), '##0.##')" />
            </xsl:when>
            <xsl:otherwise>100</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:variable name="borderPosition">
          <xsl:choose>
            <xsl:when test="position() = 1 and position() = $columnCount">lineFirst lineLast</xsl:when>
            <xsl:when test="position() = 1">lineFirst</xsl:when>
            <xsl:when test="position() = $columnCount">lineLast</xsl:when>
            <xsl:otherwise></xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <td>
          <xsl:if test="number($itemWidth) &gt; 0">
            <xsl:attribute name="style">width: <xsl:value-of select="$itemWidth" />%;</xsl:attribute>
          </xsl:if>
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="$borderPosition != ''">
                <xsl:value-of select="$itemType" />
                <xsl:text> </xsl:text>
                <xsl:value-of select="$borderPosition" />
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="$itemType" />
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:call-template name="multiple-columns-item">
            <xsl:with-param name="item" select="." />
            <xsl:with-param name="itemType" select="$itemType" />
          </xsl:call-template>
        </td>
      </xsl:for-each>
      <xsl:variable name="spacerWidth" select="$columnCount - count($items)" />
      <xsl:if test="$spacerWidth &gt; 0">
        <td>
          <xsl:if test="$spacerWidth &gt; 1">
            <xsl:attribute name="colspan"><xsl:value-of select="$spacerWidth"/></xsl:attribute>
          </xsl:if>
          <xsl:text>&#160;</xsl:text>
        </td>
      </xsl:if>
    </tr>
  </xsl:if>
</xsl:template>


<!-- generic multiple columns item template, called by "multiple-columns-line" for each item,
     overload for own items, use "itemType" for different item types -->
<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
</xsl:template>

</xsl:stylesheet>