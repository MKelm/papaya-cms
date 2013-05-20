<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_statelist'">
      <xsl:call-template name="module-content-statelist">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-default">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-statelist">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:value-of select="$pageContent/error"></xsl:value-of>
  <xsl:if test="$pageContent/states">
    <ul>
      <xsl:for-each select="$pageContent/states/state">
        <li>
          <xsl:value-of select="."></xsl:value-of>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
