<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="module-rdf-feed-rss2">
  <xsl:param name="items"/>
  <xsl:param name="limit">10</xsl:param>
  <xsl:param name="linkTitle">yes</xsl:param>
  <xsl:param name="showDescriptions">yes</xsl:param>
  <div class="subtopics">
    <ul>
		  <xsl:call-template name="module-rdf-feed-rss2-items">
        <xsl:with-param name="items" select="$items" />
		    <xsl:with-param name="limit">
          <xsl:choose>
            <xsl:when test="count($items) &gt;= $limit">
              <xsl:value-of select="$limit"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="count($items)"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:with-param>
		    <xsl:with-param name="linkTitle" select="$linkTitle" />
		    <xsl:with-param name="showDescriptions" select="$showDescriptions" />
		  </xsl:call-template>
		</ul>
  </div>
</xsl:template>

<xsl:template name="module-rdf-feed-rss2-items">
  <xsl:param name="items"/>
  <xsl:param name="limit">10</xsl:param>
  <xsl:param name="currentPosition">1</xsl:param>
  <xsl:param name="linkTitle">yes</xsl:param>
  <xsl:param name="showDescriptions">yes</xsl:param>
  <xsl:variable name="subtopicClass">
    <xsl:text>subtopic</xsl:text>
    <xsl:if test="$currentPosition = 1"> first</xsl:if>
    <xsl:if test="$currentPosition = $limit"> last</xsl:if>
  </xsl:variable>
  <xsl:call-template name="module-rdf-feed-rss2-item">
    <xsl:with-param name="item" select="$items[$currentPosition]" />
    <xsl:with-param name="class" select="$subtopicClass" />
    <xsl:with-param name="linkTitle" select="$linkTitle" />
    <xsl:with-param name="showDescriptions" select="$showDescriptions" />
  </xsl:call-template>
  <xsl:if test="$currentPosition &lt; $limit">
    <xsl:comment>Limit: <xsl:value-of select="$limit"/></xsl:comment>
    <xsl:call-template name="module-rdf-feed-rss2-items">
      <xsl:with-param name="items" select="$items"/>
      <xsl:with-param name="limit" select="$limit"/>
      <xsl:with-param name="currentPosition" select="$currentPosition + 1"/>
      <xsl:with-param name="linkTitle" select="$linkTitle"/>
      <xsl:with-param name="showDescriptions" select="$showDescriptions"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="module-rdf-feed-rss2-item">
  <xsl:param name="item"/>
  <xsl:param name="class"/>
  <xsl:param name="linkTitle">yes</xsl:param>
  <xsl:param name="showDescriptions">yes</xsl:param>
  <li>
    <xsl:if test="$class">
      <xsl:attribute name="class"><xsl:value-of select="$class"/></xsl:attribute>
    </xsl:if>
    <h2 class="title">
      <xsl:choose>
        <xsl:when test="$linkTitle and $linkTitle = 'yes'">
          <a href="{$item/link}">
            <xsl:value-of select="$item/title" />
          </a>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$item/title" />
        </xsl:otherwise>
      </xsl:choose>
    </h2>
    <xsl:if test="$showDescriptions and $showDescriptions = 'yes'">
      <p class="text">
        <xsl:value-of select="$item/description"/>
      </p>
    </xsl:if>
  </li>
</xsl:template>

</xsl:stylesheet>