<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:func="http://exslt.org/functions"
  extension-element-prefixes="func"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_categteaserthumb
-->

<xsl:import href="./base/columns.xsl" />
<xsl:import href="../_lang/functions.xsl" />

<xsl:param name="SHOW_TEASER_MORE_LINK" select="false()" />
<xsl:param name="MULTIPLE_COLUMNS_MAXIMUM" select="4" />

<xsl:template match="/*">
  <xsl:call-template name="multiple-columns">
    <xsl:with-param name="items" select="subtopics/subtopic"/>
    <xsl:with-param name="columnCount">
      <xsl:choose>
        <xsl:when test="@columns &gt; 0 and @columns &lt; $MULTIPLE_COLUMNS_MAXIMUM">
          <xsl:value-of select="@columns"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$MULTIPLE_COLUMNS_MAXIMUM"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:with-param>
    <xsl:with-param name="balanceMode" select="@balance-mode"/>
    <xsl:with-param name="itemType">teaser</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">teaser</xsl:param>
  <xsl:variable name="subTopicImages" select="//subtopicthumbs/thumb"/>
  <xsl:if test="$subTopicImages[@topic = $item/@no]/img">
    <img src="{$subTopicImages[@topic = $item/@no]/img/@src}" class="teaserImage" alt=""/>
  </xsl:if>
  <h2>
    <xsl:choose>
      <xsl:when test="$item/@href and $item/@href != ''">
        <a href="{$item/@href}" class="more">
          <xsl:value-of select="$item/title"/>
        </a>
      </xsl:when>
      <xsl:otherwise>
         <xsl:value-of select="$item/title"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="$item/subtitle/text() != ''"><xsl:text> </xsl:text>
      <span class="subTitle"><xsl:value-of select="$item/subtitle"/></span>
    </xsl:if>
  </h2>
  <div class="{$itemType}Data">
    <xsl:apply-templates select="$item/text/node()" mode="richtext"/>
    <xsl:if test="$SHOW_TEASER_MORE_LINK and $item/@href and $item/@href != ''">
      <xsl:text> </xsl:text>
      <a href="{$item/@href}" class="more">
        <xsl:value-of select="func:language-text('MORE')"/>
      </a>
    </xsl:if>
  </div>
</xsl:template>

</xsl:stylesheet>
