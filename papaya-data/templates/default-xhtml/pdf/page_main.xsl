<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<!--
  @papaya:modules content_tagcateg, content_categimg
-->

<xsl:import href="../_lang/language.xsl" />

<xsl:import href="./base/tags.xsl"/>

<xsl:output method="xml" encoding="UTF-8" standalone="yes" indent="no" omit-xml-declaration="yes" />

<xsl:param name="PAGE_THEME"></xsl:param>
<xsl:param name="PAGE_LANGUAGE"></xsl:param>

<xsl:param name="COVER_PAGE" select="true()" />
<xsl:param name="FINAL_PAGE" select="false()"/>
<xsl:param name="TOC_PAGE" select="false()"/>

<xsl:template match="/">
  <article lang="{$PAGE_LANGUAGE}" charset="UTF-8">
    <layout>
      <fonts />
      <templates>
        <xsl:variable name="fileName" select="substring-before($PAGE_LANGUAGE, '-')" />
        <template name="cover" file="files/{$fileName}.pdf" page="1"/>
        <template name="vertical" file="files/{$fileName}.pdf" page="2" />
      </templates>
      <pages font="helvetica 9" margin="30 10 30 10" align="left">
        <page name="default" template="vertical">
          <column margin="35 110 20 8.4" align="left"/>
          <column margin="35 8.4 20 110" align="left"/>
          <footer margin="280 8.4 10 8.4" align="left" font="helvetica 7"/>
        </page>
        <page name="landscape" orientation="horizontal">
          <column margin="35 8.4 20 8.4" align="left"/>
          <footer margin="200 8.4 10 8.4" align="right" font="helvetica 7"/>
        </page>
        <xsl:if test="$COVER_PAGE">
          <page name="cover" mode="elements" template="cover">
            <element for="title" margin="90 50 115 10" align="right" font="times 20 bold italic"/>
            <element for="subtitle" margin="100 50 90 10" align="right" font="helvetica 11"/>
          </page> 
        </xsl:if>
        <xsl:if test="$TOC_PAGE">
          <page name="toc" template="vertical" orientation="vertical">
            <column margin="35 8.4 20 8.4" align="left"/>
            <footer margin="280 8.4 10 8.4" align="right" font="helvetica 7"/>
          </page>
        </xsl:if>
        <xsl:if test="$FINAL_PAGE">
          <page name="final" mode="elements" template="cover" />
        </xsl:if>
      </pages>
      <tags>
        <tag name="h1" font="helvetica 13 bold" fgcolor="#000000"/>
        <tag name="h2" font="helvetica 12 bold" fgcolor="#000000"/>
        <tag name="h3" font="helvetica 10 bold" fgcolor="#000000"/>
      </tags>
    </layout>
    <xsl:variable name="pageContent" select="/page/content/topic"/>
    <xsl:if test="$COVER_PAGE">
      <xsl:call-template name="cover">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:if test="$TOC_PAGE">
      <xsl:call-template name="toc">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:call-template name="page-header">
      <xsl:with-param name="pageContent" select="$pageContent"/>
    </xsl:call-template>
    <xsl:call-template name="page-content">
      <xsl:with-param name="pageContent" select="$pageContent"/>
    </xsl:call-template>
    <xsl:call-template name="page-footer">
      <xsl:with-param name="pageContent" select="$pageContent"/>
    </xsl:call-template>
    <xsl:if test="$FINAL_PAGE">
      <xsl:call-template name="final">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:if>
  </article>
</xsl:template>

<xsl:template name="cover">
  <xsl:param name="pageContent" />
  <cover>
    <title><xsl:value-of select="$pageContent/title" /></title>
    <subtitle><xsl:value-of select="$pageContent/subtitle" /></subtitle>
  </cover>
</xsl:template>

<xsl:template name="toc">
  <xsl:param name="pageContent" />
  <toc title="Table Of Contents" line="#C0C0C0">
    <h1>Table of contents</h1>
  </toc>
</xsl:template>

<xsl:template name="final">
  <xsl:param name="pageContent" />
  <final/>
</xsl:template>

<xsl:template name="page-header">
  <xsl:param name="pageContent" />
  <header>
    <h1><xsl:value-of select="$pageContent/title" /></h1>
  </header>
</xsl:template>

<xsl:template name="page-footer">
  <xsl:param name="pageContent" />
  <footer>
    <p align="left">
      <xsl:call-template name="language-text">
        <xsl:with-param name="text">PAGE</xsl:with-param>
      </xsl:call-template>
      <xsl:text>: </xsl:text>
      <pdf-page /> / <pdf-pagecount />
    </p>
  </footer>
</xsl:template>

<xsl:template name="page-content">
  <xsl:param name="pageModule" select="/page/content/topic/@module"/>
  <xsl:param name="pageContent" />
  <content>
    <xsl:choose>
      <xsl:when test="$pageModule = 'content_tagcateg'">
        <xsl:call-template name="content-module-category">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageModule = 'content_categimg'">
        <xsl:call-template name="content-module-category">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="content-module-topic">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </content>
</xsl:template>

<xsl:template name="content-module-topic">
  <xsl:param name="pageContent" />
  <section page="default">
    <bookmark title="{$pageContent/title}" page-start="yes" />
    <xsl:apply-templates select="$pageContent/text/*|$pageContent/text/text()" mode="tags"/>
  </section>
</xsl:template>

<xsl:template name="content-module-category">
  <xsl:param name="pageContent" />
  <xsl:call-template name="content-module-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>
