<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
<!--
  @papaya:modules content_categimg, content_tagcateg
-->

<xsl:import href="../_functions/replace-outputmode.xsl" />

<xsl:output method="xml" encoding="UTF-8" standalone="yes" indent="yes" />

<xsl:param name="PAGE_WEB_PATH">/</xsl:param>
<xsl:param name="PAGE_BASE_URL"></xsl:param>
<xsl:param name="PAGE_TITLE"></xsl:param>
<xsl:param name="PAGE_LANGUAGE"></xsl:param>

<xsl:template match="/">
  <rss version="2.0">
    <channel>
      <title><xsl:value-of select="/page/content/topic/title" /></title>
      <link><xsl:value-of select="$PAGE_BASE_URL"/></link>
      <description><xsl:value-of select="/page/content/topic/teaser" /></description>
      <language><xsl:value-of select="substring-before($PAGE_LANGUAGE, '-')" /></language>
      <xsl:for-each select="/page/content/topic/subtopics/subtopic">
        <item>
          <title><xsl:value-of select="title" /></title>
          <description><xsl:value-of select="text" /></description>
          <link>
            <!-- We need to replace the ouput mode in the links -->
            <xsl:call-template name="replace-outputmode">
              <xsl:with-param name="href" select="concat($PAGE_BASE_URL, @href)" />
            </xsl:call-template>
          </link>
          <pubDate><xsl:value-of select="@createdRFC822" /></pubDate>
        </item>
      </xsl:for-each>
    </channel>
  </rss>
</xsl:template>

</xsl:stylesheet>
