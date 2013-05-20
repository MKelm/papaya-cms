<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_prevnext
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:param name="ACCESSIBILITY_BULLET_NUMBERS" select="true()" />

<xsl:template match="sitemap">
  <xsl:if test="count(mapitem) &gt; 0">
	  <ul class="navigationSiblings">
	    <xsl:call-template name="module-sitemap-mapitems">
	      <xsl:with-param name="items" select="mapitem"/>
	    </xsl:call-template>
	  </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-sitemap-mapitem">
  <xsl:param name="item"/>
  <xsl:param name="caption"/>
  <xsl:param name="class"/>
  <li class="{$class}">
    <a href="{$item/@href}" title="{$item/@title}">
      <xsl:value-of select="$caption"/>
    </a>
  </li>
</xsl:template>

<xsl:template name="module-sitemap-mapitems">
  <xsl:param name="items"/>
  <xsl:if test="mapitem[@position = 'first' and @visible = '1']">
    <xsl:call-template name="module-sitemap-mapitem">
      <xsl:with-param name="item" select="mapitem[@position = 'first']" />
      <xsl:with-param name="class">siblingFirst</xsl:with-param>
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">PAGE_FIRST</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="mapitem[@position = 'prev' and @visible = '1']">
    <xsl:call-template name="module-sitemap-mapitem">
      <xsl:with-param name="item" select="mapitem[@position = 'prev']" />
      <xsl:with-param name="class">siblingPrevious</xsl:with-param>
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">PAGE_PREVIOUS</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="mapitem[@position = 'next' and @visible = '1']">
    <xsl:call-template name="module-sitemap-mapitem">
      <xsl:with-param name="item" select="mapitem[@position = 'next']" />
      <xsl:with-param name="class">siblingNext</xsl:with-param>
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">PAGE_NEXT</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="mapitem[@position = 'last' and @visible = '1']">
    <xsl:call-template name="module-sitemap-mapitem">
      <xsl:with-param name="item" select="mapitem[@position = 'last']" />
      <xsl:with-param name="class">siblingLast</xsl:with-param>
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">PAGE_LAST</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
