<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="BOXGRID_COLUMNS_TABLE" select="false()" />

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_boxgrid'">
      <xsl:call-template name="module-content-boxgrid">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-default">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-boxgrid">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
    <xsl:with-param name="withText" select="false()"/>
  </xsl:call-template>
  <xsl:if test="count(boxes/box[@group = $pageContent/boxgroup/text()]) &gt; 0">
    <!-- adapted from the box-group template -->
    <xsl:variable name="boxes" select="boxes/box[@group = $pageContent/boxgroup/text()]"/>
    <xsl:variable name="groupId" />
    <xsl:variable name="withTitles" select="true()"/>
    <xsl:variable name="withModuleNames" select="$BOX_MODULE_CSSCLASSES"/>
    <xsl:variable name="withNoIndex" select="$BOX_DISABLE_INDEX"/>
    <xsl:if test="count($boxes) &gt; 0">
      <xsl:if test="$withNoIndex">
        <xsl:comment><xsl:text> noindex </xsl:text></xsl:comment>
      </xsl:if>
      <div class="boxGroup">
        <xsl:if test="$groupId and ($groupId != '')">
          <xsl:attribute name="id"><xsl:value-of select="$groupId"/></xsl:attribute>
        </xsl:if>

        <xsl:call-template name="multiple-columns">
          <xsl:with-param name="items" select="$boxes"/>
          <xsl:with-param name="itemType">box</xsl:with-param>
          <xsl:with-param name="columnCount" select="number($pageContent/cols/text())"/>
          <xsl:with-param name="useTable" select="$BOXGRID_COLUMNS_TABLE"/>
        </xsl:call-template>

      </div>
      <xsl:if test="$withNoIndex">
        <xsl:comment><xsl:text> /noindex </xsl:text></xsl:comment>
      </xsl:if>
    </xsl:if>
  </xsl:if>
</xsl:template>

<!-- overload the multiple columns item template to add own item types with different tag structures -->
<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:choose>
    <xsl:when test="$itemType = 'box'">
      <xsl:call-template name="module-content-boxgrid-box-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-category-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-boxgrid-box-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">box</xsl:param>
  <!-- adapted from the box-group template -->
  <xsl:param name="withTitles" select="true()"/>
  <xsl:param name="withModuleNames" select="$BOX_MODULE_CSSCLASSES"/>
  <xsl:for-each select="$item">
    <div>
      <xsl:attribute name="class">
        <xsl:text>box</xsl:text>
        <xsl:if test="$withModuleNames">
          <xsl:value-of select="concat(' module_', @module)"/>
        </xsl:if>
      </xsl:attribute>
      <xsl:if test="$withTitles and (@title != '')">
        <div class="boxTitle"><xsl:value-of select="@title" /></div>
      </xsl:if>
      <div class="boxData"><xsl:value-of select="data" disable-output-escaping="yes"/></div>
    </div>
  </xsl:for-each>
</xsl:template>

</xsl:stylesheet>
