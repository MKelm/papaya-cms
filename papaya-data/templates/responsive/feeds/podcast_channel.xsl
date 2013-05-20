<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
<!--
  @papaya:modules content_categimg, content_tagcateg
-->

<xsl:output method="xml" encoding="UTF-8" standalone="yes" indent="yes" omit-xml-declaration="no" />

<!-- Params -->
<xsl:param name="PAGE_THEME_PATH" />
<xsl:param name="PAGE_WEB_PATH" />
<xsl:param name="PAGE_TITLE" />
<xsl:param name="PAGE_PRINT_URL" />

<xsl:template match="/">
  <xsl:param name="pageContent" select="page/content/topic"/>
  <rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
    <channel>
      <ttl><xsl:value-of select="$pageContent/timetolive"/></ttl>
      <xsl:choose>
        <xsl:when test="$pageContent/link !=''">
          <link><xsl:value-of select="$pageContent/link"/></link>
        </xsl:when>
        <xsl:otherwise>
          <link>http://<xsl:value-of select="$pageContent/server"/><xsl:value-of select="$PAGE_WEB_PATH"/>podcast.<xsl:value-of select="$pageContent/@no"/>.html</link>
        </xsl:otherwise>
      </xsl:choose>
      <title><xsl:value-of select="$pageContent/title"/></title>
      <description><xsl:value-of select="$pageContent/description"/></description>
      <language><xsl:call-template name="podcastLanguage"/></language>
      <copyright><xsl:value-of select="$pageContent/copyright"/></copyright>
      <itunes:subtitle><xsl:value-of select="$pageContent/subtitle" /></itunes:subtitle>
      <itunes:author><xsl:value-of select="$pageContent/author"/></itunes:author>
      <itunes:summary><xsl:value-of select="$pageContent/summary"/></itunes:summary>
      <xsl:if test="image != ''">
        <itunes:image href="http://{server}{$PAGE_WEB_PATH}index.media.{image}" />
      </xsl:if>
      <xsl:for-each select="$pageContent/subtopics/subtopic">
        <xsl:call-template name="subtopics"/>
      </xsl:for-each>
    </channel>
  </rss>
</xsl:template>

<xsl:template name="subtopics">
  <xsl:if test="file">
    <item>
      <xsl:if test="title != ''"><title><xsl:value-of select="title" /></title></xsl:if>
      <xsl:if test="author"><itunes:author><xsl:value-of select="author"/></itunes:author></xsl:if>
      <xsl:if test="subtitle"><itunes:subtitle><xsl:value-of select="subtitle"/></itunes:subtitle></xsl:if>
      <xsl:if test="summary"><itunes:summary><xsl:value-of select="summary"/></itunes:summary></xsl:if>
      <xsl:if test="keywords"><itunes:keywords><xsl:value-of select="keywords"/></itunes:keywords></xsl:if>
      <enclosure url="http://{../../server}/{file/@download}" length="{file/@file_size}" type="{file/@file_type}" />
      <guid>http://<xsl:value-of select="../../server"/>/<xsl:value-of select="file/@download"/></guid>
      <pubDate><xsl:value-of select="@createdRFC822"/></pubDate>
    </item>
  </xsl:if>
</xsl:template>

<xsl:template name="podcastLanguage">
  <xsl:value-of select="/page/meta/metatags/language" />
</xsl:template>

</xsl:stylesheet>
