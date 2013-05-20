<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_pagezoom_links
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:param name="PAGE_LANGUAGE">en-US</xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat('modules/free/accessibility/', $PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('modules/free/accessibility/en-US.xml')"/>

<xsl:template match="zoomlinks">
  <span class="fontsize">
    <xsl:for-each select="link">
      <a href="{@href}">
        <xsl:attribute name="title">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">
              <xsl:text>TEXTSIZE_</xsl:text>
              <xsl:choose>
                <xsl:when test="@type = 'default'">DEFAULT</xsl:when>
                <xsl:when test="@type = 'minus'">MINUS</xsl:when>
                <xsl:when test="@type = 'plus'">PLUS</xsl:when>
                <xsl:when test="@type = 'min'">MIN</xsl:when>
                <xsl:when test="@type = 'max'">MAX</xsl:when>
              </xsl:choose>
            </xsl:with-param>
          </xsl:call-template>
        </xsl:attribute>
        <xsl:attribute name="class">
          <xsl:text>size</xsl:text>
          <xsl:choose>
            <xsl:when test="@type = 'default'">default</xsl:when>
            <xsl:when test="@type = 'minus'">minus</xsl:when>
            <xsl:when test="@type = 'plus'">plus</xsl:when>
            <xsl:when test="@type = 'min'">min</xsl:when>
            <xsl:when test="@type = 'max'">max</xsl:when>
          </xsl:choose>
          <xsl:if test="@selected = 'selected'">active</xsl:if>
        </xsl:attribute>
        <xsl:choose>
          <xsl:when test="@type = 'default'">A</xsl:when>
          <xsl:when test="@type = 'minus'">-</xsl:when>
          <xsl:when test="@type = 'plus'">+</xsl:when>
          <xsl:when test="@type = 'min'">A</xsl:when>
          <xsl:when test="@type = 'max'">A</xsl:when>
        </xsl:choose>
      </a>
    </xsl:for-each>
  </span>
</xsl:template>

</xsl:stylesheet>
