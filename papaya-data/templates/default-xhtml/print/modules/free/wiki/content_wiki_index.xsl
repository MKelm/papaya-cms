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
    <xsl:when test="$pageContent/@module = 'content_wiki_index'">
      <xsl:call-template name="module-content-wiki-index">
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

<xsl:template name="module-content-wiki-index">
  <xsl:param name="pageContent"/>
  <h1><xsl:value-of select="$pageContent/wiki-index/title/text()"/></h1>
  <xsl:call-template name="article-selector">
    <xsl:with-param name="content" select="$pageContent/wiki-index" />
  </xsl:call-template>

  <xsl:if test="$pageContent/wiki-index/message">
    <div class="wikiMessages">
      <ul>
        <xsl:for-each select="$pageContent/wiki-index/message">
          <li><span style="color: #FF0000; font-weight: bold"><xsl:value-of select="./text()"/></span></li>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:if>
  <div class="navigationLinks">
    <xsl:choose>
      <xsl:when test="$pageContent/wiki-index/categories">
        <strong><xsl:value-of select="$pageContent/wiki-index/link-categ/text()"/></strong>
      </xsl:when>
      <xsl:otherwise>
        <a href="{$pageContent/wiki-index/link-categ/@href}"><xsl:value-of select="$pageContent/wiki-index/link-categ/text()" /></a>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text> | </xsl:text>
    <xsl:choose>
      <xsl:when test="$pageContent/wiki-index/recent">
        <strong><xsl:value-of select="$pageContent/wiki-index/link-recent/text()"/></strong>
      </xsl:when>
      <xsl:otherwise>
        <a href="{$pageContent/wiki-index/link-recent/@href}"><xsl:value-of select="$pageContent/wiki-index/link-recent/text()"/></a>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:choose>
      <xsl:when test="$pageContent/wiki-index/letter-links">
        <xsl:for-each select="$pageContent/wiki-index/letter-links/link">
          <xsl:text> | </xsl:text>
          <xsl:choose>
            <xsl:when test="@selected = 'selected'">
              <strong><xsl:value-of select="text()"/></strong>
            </xsl:when>
            <xsl:otherwise>
              <a href="{@href}"><xsl:value-of select="text()"/></a>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text> | </xsl:text>
        <xsl:choose>
          <xsl:when test="$pageContent/wiki-index/recent or $pageContent/wiki-index/categories">
            <a href="{$pageContent/wiki-index/link-articles/@href}"><xsl:value-of select="$pageContent/wiki-index/link-articles/text()"/></a>
          </xsl:when>
          <xsl:otherwise>
            <strong><xsl:value-of select="$pageContent/wiki-index/link-articles/text()"/></strong>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </div>

  <xsl:if test="$pageContent/wiki-index/article-paging/link">
    <xsl:call-template name="article-paging">
      <xsl:with-param name="paging" select="$pageContent/wiki-index/article-paging" />
    </xsl:call-template>
  </xsl:if>

  <xsl:if test="$pageContent/wiki-index/articles/article">
    <div class="wikiIndexArea">
      <ul>
        <xsl:for-each select="$pageContent/wiki-index/articles/article">
          <li><a class="wikiLink" href="{@href}"><xsl:value-of select="@name"/></a></li>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:if>

  <xsl:if test="$pageContent/wiki-index/categories/category">
    <div class="wikiIndexArea">
      <h2><xsl:value-of select="$pageContent/wiki-index/categories/@caption"/></h2>
      <ul>
        <xsl:for-each select="$pageContent/wiki-index/categories/category">
          <li><a class="wikiLink" href="{@href}"><xsl:value-of select="@name"/></a></li>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:if>

  <xsl:if test="$pageContent/wiki-index/article-paging/link">
    <xsl:call-template name="article-paging">
      <xsl:with-param name="paging" select="$pageContent/wiki-index/article-paging" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="article-selector">
  <xsl:param name="content" />
  <div class="wikiSearchArea">
    <form action="{$content/article-select/@href}" method="get">
      <input type="hidden" name="{$content/article-select/hidden/@param}" value="read"/>
      <xsl:value-of select="$content/article-select/field/@caption"/>
      <xsl:text> </xsl:text>
      <input type="text" name="{$content/article-select/field/@param}"/>
      <xsl:text> </xsl:text>
      <input type="submit" value="{$content/article-select/button/@caption}"/>
    </form>
  </div>
</xsl:template>

<xsl:template name="article-paging">
  <xsl:param name="paging" />
  <div class="pagingLinks">
    <xsl:value-of select="$paging/@caption" />
    <xsl:text> </xsl:text>
    <xsl:for-each select="$paging/link">
      <xsl:choose>
        <xsl:when test="@selected = 'selected'">
          <strong><xsl:value-of select="./text()" /></strong>
        </xsl:when>
        <xsl:otherwise>
          <a href="{./@href}"><xsl:value-of select="./text()" /></a>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="position() != last()">
        <xsl:text> | </xsl:text>
      </xsl:if>
    </xsl:for-each>
  </div>
</xsl:template>

</xsl:stylesheet>
