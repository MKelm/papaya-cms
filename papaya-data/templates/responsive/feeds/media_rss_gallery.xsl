<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:exsl="http://exslt.org/common"
  xmlns:func="http://exslt.org/functions"
  extension-element-prefixes="func exsl">
<!--
  @papaya:modules content_thumbs
-->

<xsl:import href="../_functions/replace-outputmode.xsl" />
<xsl:import href="../_lang/datetime.xsl" />

<xsl:output method="xml" encoding="UTF-8" standalone="yes" indent="yes"/>

<xsl:param name="PAGE_WEB_PATH">/</xsl:param>
<xsl:param name="PAGE_BASE_URL" />
<xsl:param name="PAGE_TITLE" />

<xsl:param name="DATETIME_USE_ISO8601" select="true()" />

<xsl:template match="/">
  <rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
      <xsl:if test="/page/content/topic/navigation/navlink">
        <xsl:for-each select="/page/content/topic/navigation/navlink">
          <atom:link href="{func:ensureUrlIsAbsolute(@href)}">
            <xsl:attribute name="rel">
              <xsl:choose>
                <xsl:when test="@dir = 'next'">next</xsl:when>
                <xsl:when test="@dir = 'prior'">previous</xsl:when>
              </xsl:choose>
            </xsl:attribute>
          </atom:link>
        </xsl:for-each>
      </xsl:if>
      <xsl:for-each select="/page/content/topic/thumbnails/thumb">
        <item>
          <guid isPermaLink='false'><xsl:value-of select="func:ensureUrlIsAbsolute(@src)"/></guid>
          <atom:updated>
            <xsl:call-template name="format-date-time">
              <xsl:with-param name="dateTime" select="@updated" />
              <xsl:with-param name="showOffset" select="true()" />
            </xsl:call-template>
          </atom:updated>
          <title><xsl:value-of select="@title" /></title>
          <link>
            <xsl:call-template name="replace-outputmode">
              <xsl:with-param name="href" select="func:ensureUrlIsAbsolute(a/@href)" />
            </xsl:call-template>
          </link>
          <description>
            <xsl:variable name="description">
              <xsl:call-template name="image-description">
                <xsl:with-param name="thumb" select="."/>
              </xsl:call-template>
            </xsl:variable>
            <xsl:apply-templates select="exsl:node-set($description)" mode="escaped"/>
          </description>
          <enclosure url="{func:ensureUrlIsAbsolute(@for)}" />
          <media:group>
            <media:title type='plain'><xsl:value-of select="@title" /></media:title>
            <media:thumbnail url="{func:ensureUrlIsAbsolute(a/img/@src)}" />
            <media:content url="{func:ensureUrlIsAbsolute(@for)}" medium='image'/>
            <media:description type="html">
              <xsl:call-template name="image-description">
                <xsl:with-param name="thumb" select="."/>
              </xsl:call-template>
            </media:description>
          </media:group>
        </item>
      </xsl:for-each>
    </channel>
  </rss>
</xsl:template>

<func:function name="func:ensureUrlIsAbsolute">
  <xsl:param name="url"/>
  <func:result>
    <xsl:choose>
      <xsl:when test="contains($url, '://')">
        <xsl:value-of select="$url"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat($PAGE_BASE_URL, $url)"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:result>
</func:function>

<xsl:template name="image-description">
  <xsl:param name="thumb"/>
  <table>
    <tr>
      <td valign="top"><img src="{func:ensureUrlIsAbsolute(a/img/@src)}" /></td>
      <td valign="top">
        <xsl:if test="$thumb/description/node()">
          <xsl:copy-of select="$thumb/description/node()" />
        </xsl:if>
      </td>
    </tr>
  </table>
</xsl:template>

<xsl:template match="*" mode="escaped">
  <xsl:choose>
    <xsl:when test="self::text()">
      <xsl:value-of select="."/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>&lt;</xsl:text>
      <xsl:value-of select="name()" />
      <xsl:for-each select="@*">
        <xsl:text> </xsl:text>
        <xsl:value-of select="name()"/>
        <xsl:text>="</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
      </xsl:for-each>
      <xsl:text>&gt;</xsl:text>
      <xsl:apply-templates select="node()" mode="escaped"/>
      <xsl:text>&lt;/</xsl:text>
      <xsl:value-of select="name()" />
      <xsl:text>&gt;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
