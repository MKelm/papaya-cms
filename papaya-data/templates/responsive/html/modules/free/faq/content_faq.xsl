<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="FAQ_SHOW_LINK_SEARCH" select="true()" />
<xsl:param name="FAQ_SHOW_LINK_BACK" select="true()" />

<xsl:param name="FAQ_GROUP_COLUMN_COUNT" select="2" />
<xsl:param name="FAQ_ENTRY_COLUMN_COUNT" select="1" />
<xsl:param name="FAQ_SEARCH_COLUMN_COUNT" select="1" />

<xsl:param name="PAGE_LANGUAGE">en-US</xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_faq.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_faq'">
      <xsl:call-template name="module-content-faq">
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

<xsl:template name="module-content-faq">
  <xsl:param name="pageContent"/>
  <div id="faqContent">
    <xsl:choose>
      <xsl:when test="$pageContent/faq/results">
        <xsl:call-template name="module-content-faq-page-search">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/faq/groups/group/entries/entry[@selected]">
        <xsl:call-template name="module-content-faq-page-entry">
           <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/faq/groups/group[@selected]">
        <xsl:call-template name="module-content-faq-page-group">
           <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/faq/searchdlg/dialog">
        <xsl:call-template name="module-content-faq-page-search">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/faq">
        <xsl:call-template name="module-content-faq-page-groups">
           <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </div>
</xsl:template>

<!-- overload the multiple columns item template to add own item types with different tag structures -->
<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:choose>
    <xsl:when test="$itemType = 'faqGroup'">
      <xsl:call-template name="module-content-faq-group-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$itemType = 'faqEntry'">
      <xsl:call-template name="module-content-faq-entry-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$itemType = 'faqSearchEntry'">
      <xsl:call-template name="module-content-faq-search-item">
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

<xsl:template name="module-content-faq-group-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">faqGroup</xsl:param>
  <h2><xsl:value-of select="$item/@title"/></h2>
  <div class="{$itemType}Data">
    <xsl:apply-templates select="$item/node()" mode="richtext"/>
    <xsl:text> </xsl:text>
    <xsl:if test="$item/@href and $item/@href != ''">
      <a href="{$item/@href}" class="more">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">MORE</xsl:with-param>
        </xsl:call-template>
      </a>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="module-content-faq-entry-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">faqEntry</xsl:param>
  <h2>
    <xsl:value-of select="$item/@title"/>
  </h2>
  <div class="{$itemType}Data">
    <div class="question">
      <xsl:apply-templates select="$item/question/node()" mode="richtext"/>
      <xsl:text> </xsl:text>
    </div>
    <div class="answer">
      <xsl:apply-templates select="$item/answer/node()" mode="richtext"/>
      <xsl:text> </xsl:text>
    </div>
    <xsl:if test="$item/@href and $item/@href != ''">
      <a href="{$item/@href}" class="more">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">MORE</xsl:with-param>
        </xsl:call-template>
      </a>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="module-content-faq-search-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">faqSearchEntry</xsl:param>
  <h2>
    <xsl:value-of select="$item/@group"/> -
    <xsl:value-of select="$item/@title"/>
  </h2>
  <div class="{$itemType}Data">
    <div class="question">
      <xsl:apply-templates select="$item/node()" mode="richtext"/>
      <xsl:text> </xsl:text>
    </div>
    <xsl:if test="$item/@href and $item/@href != ''">
      <a href="{$item/@href}" class="more">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">MORE</xsl:with-param>
        </xsl:call-template>
      </a>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="module-content-faq-navigation-links">
  <xsl:param name="searchLink" />
  <xsl:param name="backLink" />
  <xsl:variable name="showSearchLink" select="not(/page/content/topic/faq/searchdlg)" />
  <xsl:if test="($FAQ_SHOW_LINK_SEARCH and $showSearchLink and $searchLink and $searchLink != '') or ($FAQ_SHOW_LINK_BACK and $backLink and $backLink != '')">
    <ul class="faqNavigationLinks">
      <xsl:if test="$FAQ_SHOW_LINK_SEARCH and $showSearchLink and $searchLink and $searchLink != ''">
        <li class="search"><a href="{$searchLink}" class="search">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">SEARCH_CAPTION</xsl:with-param>
          </xsl:call-template>
        </a></li>
      </xsl:if>
      <xsl:if test="$FAQ_SHOW_LINK_BACK and $backLink and $backLink != ''">
        <li class="back"><a href="{$backLink}" class="back">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">BACK</xsl:with-param>
          </xsl:call-template>
        </a></li>
      </xsl:if>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-faq-page-groups">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-faq-navigation-links">
    <xsl:with-param name="searchLink" select="$pageContent/faq/@shref" />
  </xsl:call-template>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-faq-search-dialog">
    <xsl:with-param name="dialog" select="$pageContent/faq/searchdlg/dialog" />
  </xsl:call-template>
  <xsl:choose>
    <xsl:when test="count($pageContent/faq/groups/group) &gt; 0">
      <xsl:call-template name="multiple-columns">
        <xsl:with-param name="items" select="$pageContent/faq/groups/group"/>
        <xsl:with-param name="itemType">faqGroup</xsl:with-param>
        <xsl:with-param name="columnCount" select="$FAQ_GROUP_COLUMN_COUNT" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <div class="message">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FAQ_NO_GROUPS</xsl:with-param>
        </xsl:call-template>
      </div>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-faq-page-group">
  <xsl:param name="pageContent"/>
  <xsl:variable name="faqGroup" select="$pageContent/faq/groups/group[@selected]"/>
  <xsl:call-template name="module-content-faq-navigation-links">
    <xsl:with-param name="searchLink" select="$pageContent/faq/@shref" />
    <xsl:with-param name="backLink">
      <xsl:if test="not($faqGroup/@defaultGroup) or $faqGroup/@defaultGroup != 'true'">
         <xsl:value-of select="$pageContent/faq/@href" />
      </xsl:if>
    </xsl:with-param>
  </xsl:call-template>
  <h1 class="contentTitle">
    <xsl:value-of select="$pageContent/title" /> -
    <xsl:value-of select="$pageContent/faq/@title" /> -
    <span class="subTitle"><xsl:value-of select="$faqGroup/@title" /></span>
  </h1>
  <xsl:call-template name="module-content-faq-search-dialog">
    <xsl:with-param name="dialog" select="$pageContent/faq/searchdlg/dialog" />
  </xsl:call-template>
  <xsl:choose>
    <xsl:when test="count($faqGroup/entries/entry) &gt; 0">
      <xsl:call-template name="multiple-columns">
        <xsl:with-param name="items" select="$faqGroup/entries/entry"/>
        <xsl:with-param name="itemType">faqEntry</xsl:with-param>
        <xsl:with-param name="columnCount" select="$FAQ_ENTRY_COLUMN_COUNT" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <div class="message">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FAQ_NO_ENTRIES</xsl:with-param>
        </xsl:call-template>
      </div>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-faq-page-entry">
  <xsl:param name="pageContent"/>
  <xsl:variable name="entry" select="$pageContent/faq/groups/group/entries/entry[@selected]"/>
  <xsl:call-template name="module-content-faq-navigation-links">
    <xsl:with-param name="searchLink" select="$pageContent/faq/@shref" />
    <xsl:with-param name="backLink" select="$pageContent/faq/groups/group[@selected]/@href" />
  </xsl:call-template>
  <h1 class="contentTitle">
    <xsl:value-of select="$pageContent/title"/> -
    <xsl:value-of select="$pageContent/faq/@title"/> -
    <span class="subTitle"><xsl:value-of select="$pageContent/faq/groups/group[@selected]/@title"/></span>
  </h1>
  <xsl:call-template name="module-content-faq-search-dialog">
    <xsl:with-param name="dialog" select="$pageContent/faq/searchdlg/dialog" />
  </xsl:call-template>
  <div class="faqEntryPage">
    <div class="faqEntry">
      <div class="question">
        <xsl:apply-templates select="$entry/question/node()"  mode="richtext"/>
      </div>
      <div class="answer">
        <xsl:apply-templates select="$entry/answer/node()"  mode="richtext"/>
      </div>
      <xsl:if test="count($entry/notes/note) &gt; 0">
        <div class="notes">
          <h2>
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">COMMENTS</xsl:with-param>
            </xsl:call-template>
          </h2>
          <xsl:for-each select="$entry/notes/note">
            <h3>
              <xsl:value-of select="@user" />
              <xsl:text> - </xsl:text>
              <xsl:call-template name="format-date-time">
                <xsl:with-param name="dateTime" select="@created" />
              </xsl:call-template>
            </h3>
            <div class="noteContent">
              <xsl:apply-templates  mode="richtext"/>
            </div>
          </xsl:for-each>
        </div>
      </xsl:if>
    </div>
    <xsl:if test="$entry/newdlg/dialog">
      <xsl:call-template name="dialog">
        <xsl:with-param name="dialog" select="$entry/newdlg/dialog"/>
        <xsl:with-param name="id">faqDialogAddComment</xsl:with-param>
        <xsl:with-param name="title">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">FAQ_COMMENT_ADD</xsl:with-param>
          </xsl:call-template>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="module-content-faq-page-search">
  <xsl:param name="pageContent" />
  <xsl:call-template name="module-content-faq-navigation-links">
    <xsl:with-param name="backLink" select="$pageContent/faq/@href" />
  </xsl:call-template>
  <h1 class="contentTitle">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">SEARCH_CAPTION</xsl:with-param>
    </xsl:call-template>
    <xsl:text> - </xsl:text>
    <span class="contentSubtitle">
      <xsl:value-of select="/page/content/topic/title"/> -
      <xsl:value-of select="/page/content/topic/faq/@title"/>
    </span>
  </h1>
  <xsl:call-template name="module-content-faq-search-dialog">
    <xsl:with-param name="dialog" select="$pageContent/faq/searchdlg/dialog" />
  </xsl:call-template>
  <xsl:variable name="results" select="$pageContent/faq/results" />
  <xsl:choose>
    <xsl:when test="count($results/result) &gt; 0">
      <xsl:call-template name="multiple-columns">
        <xsl:with-param name="items" select="$results/result"/>
        <xsl:with-param name="itemType">faqSearchEntry</xsl:with-param>
        <xsl:with-param name="columnCount" select="$FAQ_SEARCH_COLUMN_COUNT" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/faq/message/@no = 1">
      <div class="error">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FAQ_INVALID_SEARCH</xsl:with-param>
        </xsl:call-template>
      </div>
    </xsl:when>
    <xsl:when test="$pageContent/faq/message/@no = 2">
      <div class="message">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FAQ_NO_SEARCHRESULT</xsl:with-param>
        </xsl:call-template>
      </div>
    </xsl:when>
    <xsl:when test="count($pageContent/faq/groups/group) &gt; 0">
      <xsl:call-template name="multiple-columns">
        <xsl:with-param name="items" select="$pageContent/faq/groups/group"/>
        <xsl:with-param name="itemType">faqGroup</xsl:with-param>
        <xsl:with-param name="columnCount" select="$FAQ_GROUP_COLUMN_COUNT" />
      </xsl:call-template>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-faq-search-dialog">
  <xsl:param name="dialog" />
  <xsl:if test="$dialog">
    <div class="faqsearch">
      <xsl:call-template name="dialog">
        <xsl:with-param name="dialog" select="$dialog" />
      </xsl:call-template>
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
