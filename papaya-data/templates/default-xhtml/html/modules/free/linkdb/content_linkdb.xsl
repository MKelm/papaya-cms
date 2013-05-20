<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="LINKDB_CATEGORY_COLUMNCOUNT">2</xsl:param>

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_linkdb.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_linkdb'">
      <xsl:call-template name="module-content-linkdb">
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

<!-- overload the multiple columns item template to add own item types with different tag structures -->
<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:choose>
    <xsl:when test="$itemType = 'linkdbCategory'">
      <xsl:call-template name="module-content-linkdb-categories-item">
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

<xsl:template name="module-content-linkdb-categories-item">
  <xsl:param name="item" />
  <xsl:param name="itemType" />
  <h3><xsl:value-of select="$item/@title" /></h3>
  <div>
    <xsl:apply-templates select="$item/node()"/>
  </div>
  <a href="{$item/@href}" class="more">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">MORE</xsl:with-param>
    </xsl:call-template>
  </a>
</xsl:template>

<xsl:template name="module-content-linkdb">
  <xsl:param name="pageContent"/>
  <xsl:if test="$pageContent/title/text() != ''">
    <xsl:if test="$pageContent/backlink/text() != ''">
      <a class="back" href="{$pageContent/backlink}">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">BACK</xsl:with-param>
        </xsl:call-template>
      </a>
    </xsl:if>
    <xsl:call-template name="float-fix"/> 
    <xsl:if test="$pageContent/searchdlg" >
      <xsl:call-template name="dialog" >
        <xsl:with-param name="dialog" select="$pageContent/searchdlg/dialog"/>
        <xsl:with-param name="showMandatory" select="false()"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:call-template name="module-content-topic">
      <xsl:with-param name="pageContent" select="$pageContent" />
    </xsl:call-template>
    <xsl:call-template name="list">
      <xsl:with-param name="items" select="$pageContent/pathNavi/categ"/>
      <xsl:with-param name="itemType">linkDBCategory</xsl:with-param>
      <xsl:with-param name="listClass">linkDBPathNavigation</xsl:with-param>
      <xsl:with-param name="isRecursive" select="false()" />
    </xsl:call-template>
  </xsl:if>
  <xsl:choose>
    <xsl:when test="$pageContent/categs or $pageContent/links" >
      <xsl:if test="$pageContent/categs" >
        <xsl:call-template name="module-content-linkdb-categories" >
          <xsl:with-param name="pageContent" select="$pageContent"/>
       </xsl:call-template>
      </xsl:if>
      <xsl:if test="$pageContent/links">
        <xsl:call-template name="module-content-linkdb-links">
          <xsl:with-param name="pageContent" select="$pageContent" />
        </xsl:call-template>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-no-elements" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


<xsl:template name="module-content-linkdb-categories">
  <xsl:param name="pageContent" />
  <xsl:if test="count($pageContent/categs/categ) &gt; 0">
    <h2>
      <xsl:call-template name="language-text">
        <xsl:with-param name="text">LINKDB_CATEGORIES</xsl:with-param>
      </xsl:call-template>
    </h2>
    <xsl:call-template name="multiple-columns">
      <xsl:with-param name="items" select="$pageContent/categs/categ" />
      <xsl:with-param name="itemType">linkdbCategory</xsl:with-param>
      <xsl:with-param name="columnCount" select="$LINKDB_CATEGORY_COLUMNCOUNT" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-linkdb-links">
  <xsl:param name="pageContent" />
  <xsl:for-each select="$pageContent/links/link">
    <div class="linkdbLinkBox">
      <h2><a href="{@href}" target="{@target}"><xsl:value-of select="@title" /></a></h2>
      <div>
        <xsl:apply-templates />
      </div>
    </div>
  </xsl:for-each>
</xsl:template>

<xsl:template name="module-content-no-elements">
  <div class="message">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">LINKDB_NO_CONTENT</xsl:with-param>
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="module-content-linkdb-sites-navi">
  <xsl:param name="linksnavi" />
  <div class="linkSiteNavi">
    <xsl:if test="$linksnavi/linksback">
      <a class="buttonPrev" href="{$linksnavi/linksback/@href}">
        <xsl:value-of select="$linksnavi/linksback/@text" />
      </a>
    </xsl:if>
    <xsl:if test="$linksnavi/linksnext">
      <a class="buttonNext" href="{$linksnavi/linksnext/@href}">
        <xsl:value-of select="$linksnavi/linksnext/@text" />
      </a>
    </xsl:if>
    <div class="pageLink">
      <xsl:for-each select="$linksnavi/pages/page">
        <xsl:if test="position()>1"> - </xsl:if>
        <xsl:choose>
          <xsl:when test="@option = 'selected'">
            <xsl:value-of select="@num" />
          </xsl:when>
          <xsl:otherwise>
            <a href="{@href}"><xsl:value-of select="@num" /></a>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </div>
    <xsl:call-template name="float-fix"/>
  </div>
</xsl:template>

</xsl:stylesheet>