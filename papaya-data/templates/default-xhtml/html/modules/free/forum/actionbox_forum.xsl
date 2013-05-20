<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="../../../page_main.xsl"/>
<xsl:import href="./content_forum.xsl" />

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:param name="DISABLE_ADDITIONAL_COLUMN" select="true()" />

<xsl:template match="forumbox">
  <xsl:call-template name="module-content-forum">
    <xsl:with-param name="pageContent" select="." />
  </xsl:call-template>
  <xsl:if test="comments">
    <xsl:for-each select="comments/forum/links/link">
      <ul>
        <li><a href="{@href}"><xsl:value-of select="@caption"></xsl:value-of></a></li>
      </ul>
    </xsl:for-each>
    <xsl:if test="comments/forum/newdlg/dialog">
      <xsl:call-template name="dialog">
        <xsl:with-param name="dialog" select="comments/forum/newdlg/dialog" />
        <xsl:with-param name="id">forumNewEntry</xsl:with-param>
      </xsl:call-template>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template match="entries">
  <xsl:if test="count(entry) &gt; 0">
    <ul class="forumBoxLastEntries">
      <xsl:for-each select="entry">
        <li class="forumBoxOneEntry">
          <xsl:call-template name="entryLink">
            <xsl:with-param name="href" select="@href" />
            <xsl:with-param name="linkName" select="subject" />
            <xsl:with-param name="label">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">FORUM_CAPTION_TITLE</xsl:with-param>
              </xsl:call-template>
            </xsl:with-param>
          </xsl:call-template>
          <xsl:call-template name="entryLink">
            <xsl:with-param name="linkName" select="user/username" />
            <xsl:with-param name="label">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">FORUM_CAPTION_AUTHOR</xsl:with-param>
              </xsl:call-template>
            </xsl:with-param>
          </xsl:call-template>
          <xsl:call-template name="entryLink">
            <xsl:with-param name="linkName" select="forum" />
            <xsl:with-param name="label">
               <xsl:call-template name="language-text">
                <xsl:with-param name="text">FORUM_CAPTION_FORUM</xsl:with-param>
              </xsl:call-template>
            </xsl:with-param>
          </xsl:call-template>
          <xsl:call-template name="entryLink">
            <xsl:with-param name="linkName" select="category" />
            <xsl:with-param name="label">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">FORUM_CAPTION_CATEGORY</xsl:with-param>
              </xsl:call-template>
            </xsl:with-param>
          </xsl:call-template>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="entryLink">
  <xsl:param name="href" select="''"/>
  <xsl:param name="linkName" />
  <xsl:param name="label" />
  <div>
    <xsl:choose>
      <xsl:when test="$href != ''">
        <strong><xsl:value-of select="$label"/>:</strong>
        <xsl:text> </xsl:text>
        <a href="{$href}">
          <xsl:value-of select="$linkName" />
        </a>
      </xsl:when>
      <xsl:otherwise>
        <strong><xsl:value-of select="$label"/>:</strong>
        <xsl:text> </xsl:text>
        <xsl:value-of select="$linkName" />
      </xsl:otherwise>
    </xsl:choose>
  </div>
</xsl:template>

</xsl:stylesheet>