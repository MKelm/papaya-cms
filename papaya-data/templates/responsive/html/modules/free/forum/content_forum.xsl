<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="FORUM_CATEGORY_COLUMN_COUNT" select="2" />
<xsl:param name="FORUM_OVERVIEW_COLUMN_COUNT" select="1" />

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_forum.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_forum'">
      <xsl:call-template name="module-content-forum">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_forum_lastentries'">
      <xsl:call-template name="module-content-forum-last-entries">
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
    <xsl:when test="$itemType = 'forumCategory'">
      <xsl:call-template name="module-content-forum-category-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$itemType = 'forumOverview'">
      <xsl:call-template name="module-content-forum-overview-item">
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

<xsl:template name="module-content-forum-category-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">forumCategory</xsl:param>
  <h2><a href="{$item/@href}"><xsl:value-of select="$item/@title" /></a></h2>
  <xsl:if test="$item/*|$item/text()">
    <div class="description">
      <xsl:apply-templates select="$item/node()" mode="richtext"/>
    </div>
  </xsl:if>
  <div class="forumCategoryStatistics">
    <xsl:text>( </xsl:text>
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">FORUM_CATEGORY_COUNT</xsl:with-param>
    </xsl:call-template>
    <xsl:text> </xsl:text>
    <xsl:value-of select="$item/@categories" />
    <xsl:text> )</xsl:text>
  </div>
</xsl:template>

<xsl:template name="module-content-forum-overview-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">forumOverview</xsl:param>
  <h2><a href="{$item/@href}"><xsl:value-of select="$item/@title" /></a></h2>
  <xsl:if test="$item/description/node()">
    <div class="description">
      <xsl:apply-templates select="$item/description/node()" />
    </div>
  </xsl:if>
  <div class="forumStatistics">
    <xsl:text>( </xsl:text>
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">FORUM_THREAD_COUNT</xsl:with-param>
    </xsl:call-template>
    <xsl:text> </xsl:text>
    <xsl:value-of select="$item/@thread_count" />
    <xsl:text>, </xsl:text>
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">FORUM_ENTRY_COUNT</xsl:with-param>
    </xsl:call-template>
    <xsl:text> </xsl:text>
    <xsl:value-of select="$item/@entry_count" />
    <xsl:text> )</xsl:text>
  </div>
  <xsl:if test="$item/entry">
    <div class="forumLatestEntry">
      <xsl:call-template name="module-content-forum-threads">
        <xsl:with-param name="threads" select="$item/entry" />
        <xsl:with-param name="caption">
          <xsl:call-template name="language-text">
           <xsl:with-param name="text">FORUM_CAPTION_LAST_ENTRY</xsl:with-param>
          </xsl:call-template>
        </xsl:with-param>
      </xsl:call-template>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-forum">
  <xsl:param name="pageContent" />
  <xsl:choose>
    <xsl:when test="$pageContent/forum/no-content">
      <xsl:call-template name="module-content-forum-page-nocontent">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/forum/searchresults">
      <xsl:call-template name="module-content-forum-page-searchresults">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/forum/@mode = 0 and $pageContent/forum/entry">
      <!-- forum entry page in threaded mode-->
      <xsl:call-template name="module-content-forum-page-entry-threaded">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/forum/@mode = 1 and $pageContent/forum/thread">
      <!-- forum entry page in bbs -->
      <xsl:call-template name="module-content-forum-page-entry-bbs">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/forum/@mode = 2 and $pageContent/forum/thread">
      <!-- forum entry page in threaded bbs -->
      <xsl:call-template name="module-content-forum-page-entry-treaded-bbs">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/forum/@mode">
      <!-- forum threads page -->
      <xsl:call-template name="module-content-forum-page-threads">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <!-- category page -->
      <xsl:call-template name="module-content-forum-page-category">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-forum-page-searchresults">
  <xsl:param name="pageContent" />
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="withText" select="false()"></xsl:with-param>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-links">
    <xsl:with-param name="links" select="$pageContent/forum/links"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-search">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:if test="$pageContent/forum/searchresults/entry">
    <xsl:call-template name="module-content-forum-threads">
      <xsl:with-param name="threads" select="$pageContent/forum/searchresults/entry" />
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="not($pageContent/forum/searchresults/entry)">
    <div class="messageError">
      <xsl:call-template name="language-text">
        <xsl:with-param name="text">FORUM_NO_ENTRIES</xsl:with-param>
      </xsl:call-template>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-forum-page-nocontent">
  <xsl:param name="pageContent" />
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="withText" select="false()"></xsl:with-param>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-links">
    <xsl:with-param name="links" select="$pageContent/forum/links"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-search">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <h1 class="forumTitle">
    <xsl:value-of select="$pageContent/forum/@title" />
  </h1>
  <div><xsl:apply-templates select="$pageContent/forum/no-content/node()" mode="richtext"/></div>
  <xsl:call-template name="module-content-forum-newpost" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-forum-newpost">
  <xsl:param name="pageContent" />
  <xsl:if test="$pageContent/forum/newdlg/dialog">
    <xsl:call-template name="dialog">
      <xsl:with-param name="dialog" select="$pageContent/forum/newdlg/dialog" />
      <xsl:with-param name="id">forumNewEntry</xsl:with-param>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-forum-messages">
  <xsl:param name="pageContent" />
  <xsl:for-each select="$pageContent/forum/message">
    <div>
      <xsl:if test="@type = 'error'">
        <xsl:attribute name="class">errorMessage</xsl:attribute>
      </xsl:if>
      <xsl:apply-templates mode="richtext"/>
    </div>
  </xsl:for-each>
</xsl:template>

<xsl:template name="module-content-forum-links">
  <xsl:param name="links" />
  <xsl:if test="$links/link">
    <ul class="forumNavigation">
      <xsl:for-each select="$links/link">
        <li>
          <a href="{@href}">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">
                <xsl:choose>
                  <xsl:when test="@type = 'back'">BACK</xsl:when>
                  <xsl:when test="@type = 'search'">SEARCH_CAPTION</xsl:when>
                  <xsl:when test="@type = 'add'">ADD</xsl:when>
                  <xsl:when test="@type = 'subscribe'">SUBSCRIBE</xsl:when>
                  <xsl:when test="@type = 'unsubscribe'">UNSUBSCRIBE</xsl:when>
                  <xsl:when test="@type = 'edit'">EDIT</xsl:when>
                  <xsl:when test="@type = 'cite'">CITE</xsl:when>
                  <xsl:when test="@type = 'view'">VIEW</xsl:when>
                  <xsl:when test="@type = 'answer'">ADD</xsl:when>
                  <xsl:when test="@type = 'reply'">REPLY</xsl:when>
                  <xsl:otherwise>UNKOWN_TYPE_<xsl:value-of select="@type"/></xsl:otherwise>
                </xsl:choose>
              </xsl:with-param>
            </xsl:call-template>
          </a>
        </li>
      </xsl:for-each>
   </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-forum-search">
  <xsl:param name="pageContent" />
  <xsl:if test="$pageContent/forum/searchdlg">
    <xsl:call-template name="dialog">
      <xsl:with-param name="dialog" select="$pageContent/forum/searchdlg/dialog" />
      <xsl:with-param name="id">forumSearch</xsl:with-param>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-forum-category">
  <xsl:param name="pageContent" />

  <xsl:if test="$pageContent/forum/category">
    <div class="forumCategoryTitle">
      <span class="titlePrefix">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FORUM_CAPTION_CATEGORY</xsl:with-param>
        </xsl:call-template>
        <xsl:text>: </xsl:text>
      </span>
      <span class="title">
        <xsl:value-of select="$pageContent/forum/category/@title" />
      </span>
    </div>
    <div class="forumCategoryDescription">
      <xsl:apply-templates select="$pageContent/forum/category/node()" mode="richtext"/>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-forum-page-category">
  <xsl:param name="pageContent" />
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-links">
    <xsl:with-param name="links" select="$pageContent/forum/links"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-category">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-search">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:if test="$pageContent/forum">
    <xsl:if test="count($pageContent/forum/categories/category) &gt; 0">
      <xsl:call-template name="multiple-columns">
        <xsl:with-param name="items" select="$pageContent/forum/categories/category"/>
        <xsl:with-param name="itemType">forumCategory</xsl:with-param>
        <xsl:with-param name="columnCount" select="$FORUM_CATEGORY_COLUMN_COUNT" />
      </xsl:call-template>
    </xsl:if>
    <xsl:if test="count($pageContent/forum/forums/forum) &gt; 0">
      <xsl:call-template name="multiple-columns">
        <xsl:with-param name="items" select="$pageContent/forum/forums/forum"/>
        <xsl:with-param name="itemType">forumOverview</xsl:with-param>
        <xsl:with-param name="columnCount" select="$FORUM_OVERVIEW_COLUMN_COUNT" />
      </xsl:call-template>
    </xsl:if>
    <xsl:if test="count($pageContent/forum/forums/forum) + count($pageContent/forum/categories/category) &lt;= 0">
      <div class="messageError">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FORUM_NO_ENTRIES</xsl:with-param>
        </xsl:call-template>
      </div>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-forum-page-threads">
  <xsl:param name="pageContent" />
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-links">
    <xsl:with-param name="links" select="$pageContent/forum/links"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-search">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <h1 class="forumTitle">
    <xsl:value-of select="$pageContent/forum/@title" />
  </h1>
  <xsl:call-template name="module-content-forum-threads">
    <xsl:with-param name="threads" select="$pageContent/forum/topics" />
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-threads">
    <xsl:with-param name="threads" select="$pageContent/forum/thread/entries" />
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-newpost" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-forum-page-entry-threaded">
  <xsl:param name="pageContent" />
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="withText" select="false()"></xsl:with-param>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-links">
    <xsl:with-param name="links" select="$pageContent/forum/links"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-search">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:if test="$pageContent/forum/entry/node()">
    <div class="forumEntry">
      <h1 class="forumTitle">
        <xsl:apply-templates select="$pageContent/forum/entry/subject/node()" mode="richtext"/>
      </h1>
      <xsl:call-template name="forumAuthorInfo">
        <xsl:with-param name="user" select="$pageContent/forum/entry/user"/>
        <xsl:with-param name="simpleAuthorInfo" select="false()" />
      </xsl:call-template>

      <div class="forumEntryContent">
        <xsl:apply-templates select="$pageContent/forum/entry/text/node()" mode="richtext"/>
      </div>
      <div class="forumEntryLinks">
        <xsl:call-template name="module-content-forum-links">
          <xsl:with-param name="links" select="$pageContent/forum/entry/links"/>
        </xsl:call-template>
      </div>
      <xsl:call-template name="float-fix" />
    </div>
  </xsl:if>
  <xsl:if test="count($pageContent/forum/thread/entries//entry) &gt; 1">
    <xsl:call-template name="module-content-forum-threads">
      <xsl:with-param name="threads" select="$pageContent/forum/thread/entries/entry" />
    </xsl:call-template>
  </xsl:if>
  <xsl:call-template name="module-content-forum-newpost" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-forum-page-entry-bbs">
  <xsl:param name="pageContent" />
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="withText" select="false()"></xsl:with-param>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-links">
    <xsl:with-param name="links" select="$pageContent/forum/links"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-search">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <h1 class="forumTitle">
    <xsl:value-of select="$pageContent/forum/thread/@title" />
  </h1>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-bbs">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-newpost" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-forum-page-entry-treaded-bbs">
  <xsl:param name="pageContent" />

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="withText" select="false()"></xsl:with-param>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-links">
    <xsl:with-param name="links" select="$pageContent/forum/links"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-search">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <h1 class="forumTitle">
    <xsl:value-of select="$pageContent/forum/thread/@title" />
  </h1>
  <xsl:call-template name="module-content-forum-messages" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-treaded-bbs">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-forum-newpost" >
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-forum-treaded-bbs">
  <xsl:param name="pageContent" />
  <xsl:for-each select="$pageContent/forum/thread/entries/entry">
    <div style="padding-left: {@indent * 30 + 10}px;">
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="position() mod 2">bbsOneEntry even</xsl:when>
          <xsl:otherwise>bbsOneEntry odd</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:if test="position() &gt; 1">
        <h2><xsl:value-of select="subject" /></h2>
      </xsl:if>
      <xsl:call-template name="forumAuthorInfo">
        <xsl:with-param name="user" select="user" />
        <xsl:with-param name="simpleAuthorInfo" select="false()" />
      </xsl:call-template>
      <div>
        <xsl:apply-templates select="text/node()" mode="richtext"/>
      </div>
      <div class="bbsInfoOptionsContainer">
        <div class="bbsEntryDateContainer">
          <xsl:call-template name="getEntryCreateModifyUpdateDateInfo">
            <xsl:with-param name="item" select="." />
          </xsl:call-template>
        </div>
        <div class="bbsOneEntryLinksContainer">
          <xsl:call-template name="module-content-forum-links">
            <xsl:with-param name="links" select="links" />
          </xsl:call-template>
        </div>
      </div>
      <xsl:call-template name="float-fix"/>
    </div>
  </xsl:for-each>
  <div class="pager">
    <xsl:for-each select="content/topic/forum/thread/entries/pages/pagelink">
      <a href="{@href}"><xsl:value-of select="@caption"></xsl:value-of></a>
    </xsl:for-each>
  </div>
</xsl:template>

<xsl:template name="module-content-forum-bbs">
  <xsl:param name="pageContent" />
  <xsl:for-each select="$pageContent/forum/thread/entries/entry">
    <div>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="position() mod 2">bbsOneEntry even</xsl:when>
          <xsl:otherwise>bbsOneEntry odd</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:if test="position() &gt; 1">
        <h2><xsl:value-of select="subject" /></h2>
      </xsl:if>
      <xsl:call-template name="forumAuthorInfo">
        <xsl:with-param name="user" select="user" />
        <xsl:with-param name="simpleAuthorInfo" select="false()" />
      </xsl:call-template>
      <div>
        <xsl:apply-templates select="text/node()" mode="richtext"/>
      </div>
      <div class="bbsInfoOptionsContainer">
        <div class="bbsEntryDateContainer">
          <xsl:call-template name="getEntryCreateModifyUpdateDateInfo">
            <xsl:with-param name="item" select="." />
          </xsl:call-template>
        </div>
        <div class="bbsOneEntryLinksContainer">
          <xsl:call-template name="module-content-forum-links">
            <xsl:with-param name="links" select="links" />
          </xsl:call-template>
        </div>
      </div>
      <xsl:call-template name="float-fix"/>
    </div>
  </xsl:for-each>
  <div class="pager">
    <xsl:for-each select="content/topic/forum/thread/entries/pages/pagelink">
      <a href="{@href}"><xsl:value-of select="@caption"></xsl:value-of></a>
    </xsl:for-each>
  </div>
</xsl:template>

<xsl:template name="module-content-forum-threads">
  <xsl:param name="threads" />
  <xsl:param name="caption" select="''" />
  
  <xsl:if test="$threads/entry and count($threads/entry) &gt; 0">
    <table class="forumThreads">
      <xsl:if test="$caption != ''">
        <caption><xsl:value-of select="$caption" /></caption>
      </xsl:if>
      <thead>
        <tr>
          <th class="title">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FORUM_CAPTION_TITLE</xsl:with-param>
            </xsl:call-template>
          </th>
          <xsl:if test="$threads/entry/text">
            <th class="text">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">FORUM_CAPTION_CONTENT</xsl:with-param>
              </xsl:call-template>
            </th>
          </xsl:if>
          <xsl:if test="content/topic/forum">
            <th class="author">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">FORUM_CAPTION_AUTHOR</xsl:with-param>
              </xsl:call-template>
            </th>
            <th class="answerCount">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">FORUM_CAPTION_ANSWERS</xsl:with-param>
              </xsl:call-template>
            </th>
            <th class="date">
              <xsl:text> </xsl:text>
            </th>
            <xsl:if test="$threads/entry/links/link">
              <th class="links">
                <xsl:text> </xsl:text>
              </th>
            </xsl:if>
          </xsl:if>
        </tr>
      </thead>
      <tbody>
        <xsl:choose>
          <xsl:when test="content/topic/forum">
            <xsl:for-each select="$threads/entry">
              <tr>
                <xsl:attribute name="class">
                  <xsl:choose>
                    <xsl:when test="not(position() mod 2)">even</xsl:when>
                    <xsl:otherwise>odd</xsl:otherwise>
                  </xsl:choose>
                </xsl:attribute>
                <td class="title" style="padding-left: {@indent * 20}px;">
                  <a href="{@href}">
                    <xsl:apply-templates select="subject" mode="richtext"/>
                  </a>
                </td>
                <xsl:if test="$threads/entry/text">
                  <td class="text"><xsl:apply-templates select="text/node()" mode="richtext"/></td>
                </xsl:if>
                <td class="author">
                  <xsl:call-template name="forumAuthorInfo">
                    <xsl:with-param name="user" select="user" />
                    <xsl:with-param name="simpleAuthorInfo" select="true()" />
                  </xsl:call-template>
                </td>
                <td class="answerCount"><xsl:value-of select="@answers" /></td>
                <td class="date">
                  <xsl:call-template name="getEntryCreateModifyUpdateDateInfo">
                    <xsl:with-param name="item" select="." />
                  </xsl:call-template>
                </td>
                <xsl:if test="$threads/entry/links/link">
                  <td class="links">
                    <xsl:call-template name="module-content-forum-links">
                      <xsl:with-param name="links" select="links"/>
                    </xsl:call-template>
                  </td>
                </xsl:if>
              </tr>
            </xsl:for-each>
          </xsl:when>
          <xsl:otherwise>
            <xsl:for-each select="$threads/entry">
              <tr>
                <xsl:attribute name="class">
                  <xsl:choose>
                    <xsl:when test="not(position() mod 2)">even</xsl:when>
                    <xsl:otherwise>odd</xsl:otherwise>
                  </xsl:choose>
                </xsl:attribute>
                <td class="title" style="padding-left: {@indent * 20}px;">
                  <a href="{@href}">
                    <xsl:apply-templates select="subject" mode="richtext"/>
                  </a>
                </td>
                <xsl:if test="$threads/entry/text">
                  <td class="text"><xsl:apply-templates select="text/node()" mode="richtext"/></td>
                </xsl:if>
                </tr>
            </xsl:for-each>
          </xsl:otherwise>
        </xsl:choose>
      </tbody>
    </table>
    <div class="pager">
      <xsl:for-each select="$threads/pages/pagelink">
        <a href="{@href}"><xsl:value-of select="@caption"></xsl:value-of></a>
      </xsl:for-each>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="getEntryCreateModifyUpdateDateInfo">
  <xsl:param name="item" />

  <div>
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">FORUM_CAPTION_LAST_MODIFIED</xsl:with-param>
    </xsl:call-template>
    <xsl:text>: </xsl:text>
    <xsl:call-template name="format-date-time">
      <xsl:with-param name="dateTime" select="$item/@modified" />
    </xsl:call-template>
  </div>
  <div>
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">FORUM_CAPTION_CREATED</xsl:with-param>
    </xsl:call-template>
    <xsl:text>: </xsl:text>
    <xsl:call-template name="format-date-time">
      <xsl:with-param name="dateTime" select="$item/@created" />
    </xsl:call-template>
  </div>
  <div>
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">FORUM_CAPTION_EDIT_DATE</xsl:with-param>
    </xsl:call-template>
    <xsl:text>: </xsl:text>
    <xsl:choose>
      <xsl:when test="@thread_modified = '1970-01-01 01:00:00'">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FORUM_DATE_NEVER</xsl:with-param>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="format-date-time">
          <xsl:with-param name="dateTime" select="$item/@thread_modified" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </div>
</xsl:template>

<xsl:template name="module-content-forum-thread-indent">
  <xsl:param name="indent" />
  <xsl:if test="$indent &gt; 0">
    <div class="indent">
      <xsl:text> </xsl:text>
    </div>
    <xsl:call-template name="module-content-forum-thread-indent">
      <xsl:with-param name="indent" select="$indent - 1" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="forumAuthorInfo">
  <xsl:param name="user" />
  <xsl:param name="simpleAuthorInfo" select="true()"/>

  <xsl:choose>
    <xsl:when test="$simpleAuthorInfo">
      <xsl:apply-templates select="$user/username/node()" mode="richtext"/>
    </xsl:when>
    <xsl:otherwise>
      <div class="forumAuthorInfo">
        <xsl:if test="$user/avatar/img">
          <xsl:apply-templates select="$user/avatar/img" mode="richtext"/>
          <br />
        </xsl:if>

        <strong>
          <span class="forumAuthorInfoCaption">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FORUM_CAPTION_AUTHOR</xsl:with-param>
            </xsl:call-template>
            <xsl:text>: </xsl:text>
          </span>
          <span class="forumAuthorInfoValue">
            <xsl:apply-templates select="$user/username/node()" mode="richtext"/>
          </span>
        </strong>
        <xsl:if test="$user/@registered != 'true'">
          <span class="forumUserUnverified">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FORUM_USER_UNVERIFIED</xsl:with-param>
            </xsl:call-template>
          </span>
        </xsl:if>

        <br/>
        <xsl:if test="$user/registration">
          <span class="forumAuthorInfoCaption">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FORUM_CAPTION_REGISTRATION</xsl:with-param>
            </xsl:call-template>
            <xsl:text>: </xsl:text>
          </span>
          <span class="forumAuthorInfoValue">
            <xsl:value-of select="$user/registration" />
          </span>
          <br/>
        </xsl:if>
        <xsl:if test="$user/lastlogin" >
          <span class="forumAuthorInfoCaption">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FORUM_CAPTION_LAST_LOGIN</xsl:with-param>
            </xsl:call-template>
            <xsl:text>: </xsl:text>
          </span>
          <span class="forumAuthorInfoValue">
            <xsl:value-of select="$user/lastlogin" />
          </span>
          <br/>
        </xsl:if>
        <xsl:if test="$user/group">
          <span class="forumAuthorInfoCaption">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FORUM_CAPTION_AUTHOR_GROUP</xsl:with-param>
            </xsl:call-template>
            <xsl:text>: </xsl:text>
          </span>
          <span class="forumAuthorInfoValue">
            <xsl:value-of select="$user/group" />
          </span>
          <br/>
        </xsl:if>
        <xsl:if test="$user/entries">
          <span class="forumAuthorInfoCaption">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FORUM_ENTRY_COUNT</xsl:with-param>
            </xsl:call-template>
            <xsl:text>: </xsl:text>
          </span>
          <span class="forumAuthorInfoValue">
            <xsl:value-of select="$user/entries" />
          </span>
        </xsl:if>
      </div>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- list entries template -->
<xsl:template name="module-content-forum-last-entries">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
  <div class="forumEntries">
    <xsl:for-each select="$pageContent/forum/lastentries/entries/entry">
      <h2>
        <xsl:value-of select="forum" />
        <xsl:text>,</xsl:text>
        <xsl:value-of select="category" />
      </h2>
      <p>
        <a href="{@href}"><xsl:value-of select="subject" /></a><br />
        <xsl:text>(</xsl:text>
          <xsl:value-of select="user/username" />
          <xsl:text> - </xsl:text>
          <xsl:call-template name="format-date-time">
            <xsl:with-param name="dateTime" select="@modified" />
          </xsl:call-template>
        <xsl:text>)</xsl:text>
      </p>
    </xsl:for-each>
    <xsl:if test="not($pageContent/forum/lastentries/entries/entry)">
      <div class="messageError">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">FORUM_NO_ENTRIES</xsl:with-param>
        </xsl:call-template>
      </div>
    </xsl:if>
  </div>
</xsl:template>

</xsl:stylesheet>
