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

<xsl:import href="../../_lang/language.xsl" />

<xsl:output method="xml" encoding="UTF-8" standalone="no" indent="yes" omit-xml-declaration="yes" />

<!-- adds numbers to the navigation list items -->
<xsl:param name="ACCESSIBILITY_BULLET_NUMBERS" select="true()" />

<xsl:param name="LANGUAGE_TEXTS_CURRENT" select="document(concat('../', $PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_TEXTS_FALLBACK" select="document('../en-US.xml')"/>

<xsl:template name="navigation">
  <xsl:if test="count(mapitem) &gt; 0">
    <ul class="navigation">
      <xsl:for-each select="mapitem">
        <xsl:call-template name="navigation-item" />
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="navigation-item">
	<xsl:param name="level"/>
  <!-- generate a css class depending on position and status -->
  <xsl:variable name="position-class">
    <xsl:choose>
      <xsl:when test="@focus and (position() = 1)">active first</xsl:when>
      <xsl:when test=".//@focus and (position() = 1)">selected first</xsl:when>
      <xsl:when test="@focus and (position() = last())">active last</xsl:when>
      <xsl:when test=".//@focus and (position() = last())">selected last</xsl:when>
      <xsl:when test="position() = 1">first</xsl:when>
      <xsl:when test="position() = last()">last</xsl:when>
      <xsl:when test="@focus">active</xsl:when>
      <xsl:when test=".//@focus">selected</xsl:when>
      <xsl:otherwise></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <li>
    <xsl:if test="$position-class != ''">
      <xsl:attribute name="class"><xsl:value-of select="$position-class" /></xsl:attribute>
    </xsl:if>
	  <a href="{@href}">
      <!-- add position and link class to link tag -->
      <xsl:if test="$position-class != '' or @class != ''">
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="$position-class != '' and @class != ''">
              <xsl:value-of select="@class" />
              <xsl:text> </xsl:text>
              <xsl:value-of select="$position-class" />
            </xsl:when>
            <xsl:when test="@class != ''">
              <xsl:value-of select="@class" />
            </xsl:when>
            <xsl:when test="$position-class != ''">
              <xsl:value-of select="$position-class" />
            </xsl:when>
          </xsl:choose>
        </xsl:attribute>
      </xsl:if>
      <!-- copy data attributes -->
      <xsl:copy-of select="@*[starts-with(name(), 'data-')]"/>
      <!-- copy target and onclick attributes -->
      <xsl:copy-of select="@target|@onclick"/>
      <!-- bullet numbers for screenreaders -->
      <xsl:call-template name="accessibility-bullet-number">
        <xsl:with-param name="number">
          <xsl:number level="multiple" count="mapitem" format="1" />
        </xsl:with-param>
      </xsl:call-template>
      <span><xsl:value-of select="@title" /></span>
      <xsl:call-template name="language-accessiblity-separator" />
    </a>
    <xsl:if test="count(mapitem) &gt; 0">
      <ul>
        <xsl:for-each select="mapitem">
          <xsl:call-template name="navigation-item" />
        </xsl:for-each>
      </ul>
    </xsl:if>
  </li>
</xsl:template>

<xsl:template name="accessibility-bullet-number">
  <xsl:param name="number" />
  <xsl:if test="$ACCESSIBILITY_BULLET_NUMBERS and $number and $number != ''">
    <dfn class="accessibilityElement">
      <xsl:value-of select="$number" />
      <xsl:text> </xsl:text>
    </dfn>
  </xsl:if>
</xsl:template>

<!-- a little div to fix floating problems (height of elements) -->
<xsl:template name="float-fix">
  <div class="floatFix"><xsl:text> </xsl:text></div>
</xsl:template>

</xsl:stylesheet>