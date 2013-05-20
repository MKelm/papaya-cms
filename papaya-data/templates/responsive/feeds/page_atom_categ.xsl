<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/2005/Atom"
>
<!--
  @papaya:modules content_categimg, content_tagcateg
-->

<xsl:import href="../_functions/replace-outputmode.xsl" />

<xsl:output method="xml" encoding="UTF-8" standalone="yes" indent="yes" />

<xsl:param name="PAGE_WEB_PATH">/</xsl:param>
<xsl:param name="PAGE_URL"></xsl:param>
<xsl:param name="PAGE_BASE_URL"></xsl:param>
<xsl:param name="PAGE_TITLE"></xsl:param>
<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="SYSTEM_TIME_OFFSET">+0000</xsl:param>

<xsl:template match="/page">
  <feed lang="{$PAGE_LANGUAGE}">
    <id><xsl:value-of select="$PAGE_URL"/></id>
    <updated>
      <xsl:call-template name="format-datetime">
        <xsl:with-param name="datetime" select="content/topic/@published" />
        <xsl:with-param name="datetime-alternative" select="content/topic/@created" />
      </xsl:call-template>
    </updated>
    <xsl:for-each select="views/viewmode[@type = 'page']">
      <link rel="alternate" type="{@contenttype}" href="{$PAGE_BASE_URL}{@href}" />
    </xsl:for-each>
    <title><xsl:value-of select="content/topic/title" /></title>
    <xsl:if test="content/topic/subtitle != ''">
      <subtitle><xsl:value-of select="content/topic/subtitle" /></subtitle>
    </xsl:if>
    <author>
      <name><xsl:value-of select="content/topic/@author" /></name>
    </author>
    <xsl:for-each select="content/topic/subtopics/subtopic">
      <entry>
        <id><xsl:value-of select="concat($PAGE_BASE_URL, @href)" /></id>
        <title><xsl:value-of select="title" /></title>
        <author>
          <name><xsl:value-of select="@author" /></name>
        </author>
        <content type="html"><xsl:apply-templates select="text/text()|text/*"  mode="html"/></content>
        <link rel="alternate" type="text/html">
          <!-- We need to replace the ouput mode in the links -->
          <xsl:attribute name="href">
            <xsl:call-template name="replace-outputmode">
              <xsl:with-param name="href" select="concat($PAGE_BASE_URL, @href)" />
            </xsl:call-template>
          </xsl:attribute>
        </link>
        <updated>
          <xsl:call-template name="format-datetime">
            <xsl:with-param name="datetime" select="@published" />
            <xsl:with-param name="datetime-alternative" select="@created" />
          </xsl:call-template>
        </updated>
      </entry>
    </xsl:for-each>
  </feed>
</xsl:template>

<xsl:template match="*" mode="html">
  <xsl:text>&lt;</xsl:text>
  <xsl:value-of select="local-name()" />
  <xsl:for-each select="@*">
    <xsl:text> </xsl:text>
    <xsl:value-of select="local-name()" />
    <xsl:text>="</xsl:text>
    <xsl:value-of select="." />
    <xsl:text>"</xsl:text>
  </xsl:for-each>
  <xsl:text>&gt;</xsl:text>
  <xsl:apply-templates select="node()" mode="html"/>
  <xsl:text>&lt;/</xsl:text>
  <xsl:value-of select="local-name()" />
  <xsl:text>&gt;</xsl:text>
</xsl:template>

<xsl:template name="format-datetime">
  <xsl:param name="datetime" />
  <xsl:param name="datetime-alternative" />
  <xsl:variable name="value">
    <xsl:choose>
      <xsl:when test="$datetime and $datetime != ''">
        <xsl:value-of select="$datetime" />
      </xsl:when>
      <xsl:when test="$datetime-alternative and $datetime-alternative != ''">
        <xsl:value-of select="$datetime-alternative" />
      </xsl:when>
      <xsl:otherwise></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:if test="$value and $value != ''">
    <xsl:value-of select="substring($value, 1, 10)" />
    <xsl:text>T</xsl:text>
    <xsl:value-of select="substring($value, 12, 8)" />
    <xsl:value-of select="substring($SYSTEM_TIME_OFFSET, 1, 3)" />
    <xsl:text>:</xsl:text>
    <xsl:value-of select="substring($SYSTEM_TIME_OFFSET, 4, 2)" />
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
