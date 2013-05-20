<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template match="/twitter">
  <xsl:if test="title">
    <h2><xsl:value-of select="title/text()" /></h2>
  </xsl:if>
  <xsl:if test="follow-link">
    <p>
      <a class="followLink" href="follow-link/@href"><xsl:value-of select="follow-link/text()" /></a>
    </p>
  </xsl:if>
  <xsl:if test="status">
    <xsl:for-each select="status">
      <div class="tweet">
        <div class="tweetText">
          <xsl:copy-of select="text/text()|text/*" />
        </div>
        <div class="tweetTimestamp">
          <xsl:call-template name="format-date-time">
            <xsl:with-param name="dateTime" select="@created" />
          </xsl:call-template>
        </div>
        <div class="tweetSource">
          <xsl:value-of select="source/text()|source/*" disable-output-escaping="yes" />
        </div>
      </div>
    </xsl:for-each>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>