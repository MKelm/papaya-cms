<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="MNOGO_SHOW_SEARCH_DIALOG" select="true()"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_mnogo.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_mnogo'">
      <xsl:call-template name="module-content-mnogo">
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

<xsl:template name="module-content-mnogo">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-mnogo-searchdialog">
    <xsl:with-param name="dialog" select="$pageContent/searchdialog"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-mnogo-message">
    <xsl:with-param name="message" select="$pageContent/message"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-mnogo-navigation">
    <xsl:with-param name="pages" select="$pageContent/pages/page"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-mnogo-matches">
    <xsl:with-param name="matches" select="$pageContent/matches"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-mnogo-navigation">
    <xsl:with-param name="pages" select="$pageContent/pages/page"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-mnogo-searchdialog">
  <xsl:param name="dialog" />
  <xsl:param name="dialogTitle"></xsl:param>
  <xsl:if test="$MNOGO_SHOW_SEARCH_DIALOG and $dialog">
    <xsl:call-template name="dialog">
      <xsl:with-param name="dialog" select="$dialog" />
      <xsl:with-param name="id">mnogoSearch</xsl:with-param>
      <xsl:with-param name="submitButton">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">SEARCH_BUTTON</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-mnogo-message">
  <xsl:param name="message"></xsl:param>
  <xsl:if test="$message and $message/node()">
    <div class="messageError">
      <xsl:apply-templates select="$message/node()" />
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-mnogo-matches">
  <xsl:param name="matches"/>
  <xsl:if test="$matches">
    <xsl:call-template name="module-content-mnogo-matches-header">
      <xsl:with-param name="matches" select="$matches"/>
    </xsl:call-template>
    <xsl:call-template name="multiple-columns">
      <xsl:with-param name="items" select="$matches/match"/>
      <xsl:with-param name="itemType">searchResult</xsl:with-param>
      <xsl:with-param name="columnCount" select="1" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-mnogo-matches-header">
  <xsl:param name="matches"/>
  <div class="resultsHeader">
    <xsl:value-of select="$matches/@first"/>
    <xsl:text> - </xsl:text>
    <xsl:value-of select="$matches/@last"/>
    <xsl:text> / </xsl:text>
    <xsl:value-of select="$matches/@count"/>
  </div>
</xsl:template>

<xsl:template name="module-content-mnogo-navigation">
  <xsl:param name="pages"/>
  <xsl:if test="$pages and count($pages) &gt; 1">
    <ul class="searchResultNavigation">
      <xsl:for-each select="$pages">
        <li>
          <xsl:if test="@selected">
            <xsl:attribute name="class">selected</xsl:attribute>
          </xsl:if>
          <a href="{@href}"><xsl:value-of select="@no"/></a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-mnogo-match-item">
  <xsl:param name="item" />
  <xsl:param name="itemType" />
  <h2>
    <a href="{$item/@href}"><xsl:value-of select="$item/@title"/></a>
    <span class="subTitle">
      <xsl:call-template name="format-date-time">
        <xsl:with-param name="dateTime" select="$item/@modified"/>
      </xsl:call-template>
    </span>
  </h2>
  <div class="subTopicData">
    <xsl:apply-templates select="$item/node()"/>
    <a href="{$item/@href}" class="more"><xsl:value-of select="$item/@short_href"/></a>
  </div>
</xsl:template>

<!-- overload the multiple columns item template to add own item types with different tag structures -->
<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:choose>
    <xsl:when test="$itemType = 'searchResult'">
      <xsl:call-template name="module-content-mnogo-match-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-category-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
