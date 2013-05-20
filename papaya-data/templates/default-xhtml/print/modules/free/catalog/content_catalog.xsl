<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_catalog.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>

  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_catalog_azlist'">
      <xsl:call-template name="module-content-catalog-az-list">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_catalog'">
      <xsl:call-template name="module-content-catalog">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_catalog_subscribe'">
      <xsl:call-template name="module-content-catalog-subscribe">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_catalog_unsubscribe'">
      <xsl:call-template name="module-content-catalog-unsubscribe">
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

<xsl:template name="module-content-catalog-subscribe">
  <xsl:param name="pageContent" />

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:call-template name="subscribe-unsubscribe-messages">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:if test="count($pageContent/subscribe/listitem) &gt; 0">
    <form class="catalogSubscribeForm" action="{$pageContent/subscribe/@href}" method="post">
      <xsl:call-template name="module-content-catalog-subscribe-sublist">
        <xsl:with-param name="list" select="$pageContent/subscribe" />
        <xsl:with-param name="paramname" select="$pageContent/subscribe/@param_name" />
      </xsl:call-template>
      <div class="email">
        <label for="catalog_subscribe_emial_field" class="emailTitle">
          <xsl:value-of select="$pageContent/subscribe/mailfieldname" />
        </label>
        <input id="catalog_subscribe_emial_field"
               type="text"
               class="emailField"
               name="{$pageContent/subscribe/@param_name}[email]">
        </input>
      </div>
      <xsl:call-template name="dialog-submit-button">
        <xsl:with-param name="buttonValue" select="$pageContent/subscribe/subscribefield" />
      </xsl:call-template>
    </form>
    <xsl:if test="$pageContent/backlink">
      <div class="catalogBackLink">
        <a href="{$pageContent/backlink/@href}">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">LINK_TO_BACK</xsl:with-param>
          </xsl:call-template>
        </a>
      </div>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-catalog-unsubscribe">
  <xsl:param name="pageContent" />

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:call-template name="subscribe-unsubscribe-messages">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <form class="catalogUnsubscribeForm"
    action="{$pageContent/unsubscribe/@href}"
    method="post">

    <xsl:call-template name="module-content-catalog-subscribe-sublist">
      <xsl:with-param name="list" select="$pageContent/unsubscribe" />
      <xsl:with-param name="paramname" select="$pageContent/unsubscribe/@param_name" />
    </xsl:call-template>

    <xsl:if test="$pageContent/unsubscribe/mailfieldname">
      <div class="emailTitle">
        <label for="catalog_unsubscribe_emial_field">
          <xsl:value-of select="$pageContent/unsubscribe/mailfieldname" />
        </label>
      </div>
      <input id="catalog_unsubscribe_emial_field"
           type="text"
           class="emailField"
           name="{$pageContent/unsubscribe/@param_name}[email]">
      </input>
    </xsl:if>
    <xsl:if test="$pageContent/unsubscribe/mailfieldname|$pageContent/unsubscribe/listitem">
      <xsl:call-template name="dialog-submit-button">
        <xsl:with-param name="buttonValue" select="$pageContent/unsubscribe/unsubscribefield" />
      </xsl:call-template>
    </xsl:if>
  </form>
</xsl:template>

<xsl:template name="module-content-catalog-subscribe-sublist">
  <xsl:param name="list" />
  <xsl:param name="paramname" />

  <xsl:if test="count($list/listitem) &gt; 0">
    <ul>
      <xsl:for-each select="$list/listitem">
        <li class="list_indent{@indent}">
          <input name="{$paramname}[catalog_ids][{@catalog_id}]"
                 id="cid_{@catalog_id}"
                 value="1"
                 type="checkbox">

            <xsl:if test="@selected = 'selected'">
              <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            <xsl:if test="@disabled = 'disabled'">
              <xsl:attribute name="disabled">disabled</xsl:attribute>
            </xsl:if>
            <label for="cid_{@catalog_id}">
              <xsl:value-of select="@title" />
            </label>
        </input>
      </li>
      <xsl:if test="count(./list) &gt; 0">
        <li>
          <xsl:call-template name="module-content-catalog-subscribe-sublist">
            <xsl:with-param name="list" select="./list" />
            <xsl:with-param name="paramname" select="$paramname" />
          </xsl:call-template>
        </li>
      </xsl:if>
    </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="subscribe-unsubscribe-messages">
  <xsl:param name="pageContent" />

  <xsl:if test="count($pageContent/msgs/msg) &gt; 0">
    <div class="subscribeMessagesContainer">
      <ul class="subscribeMessages">
        <xsl:for-each select="$pageContent/msgs/msg">
          <xsl:if test="text() != ''">
            <li class="msg{@type}">
              <xsl:value-of select="text()" />
            </li>
          </xsl:if>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-catalog-az-list">
  <xsl:param name="pageContent" />

  <a id="link_content" name="link_content"> </a>

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <xsl:call-template name="module-content-catalog-show-azlist-letters">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="catalog" select="$pageContent/azlist" />
  </xsl:call-template>

  <xsl:call-template name="module-content-catalog-show-azlist">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="catalog" select="$pageContent/azlist" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-catalog-show-azlist-letters">
  <xsl:param name="pageContent" />
  <xsl:param name="catalog" />
  <xsl:param name="showLink" select="false()"/>

  <xsl:if test="count($catalog/letter) &gt; 0">
    <div class="azlistLetterContainer">
      <ul class="catalogAZListLetterList">
        <xsl:for-each select="$catalog/letter">
          <xsl:variable name="chargroup" select="@title" />
          <li>
            <xsl:choose>
              <xsl:when test="@href">
                <a href="{@href}">
                  <xsl:value-of select="@title" />
                </a>
              </xsl:when>
              <xsl:when test="$showLink and count($pageContent/catalog/categories/category[@chargroup = $chargroup]) &gt; 0">
                <a href="#{@title}">
                  <xsl:value-of select="@title" />
                </a>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="@title" />
              </xsl:otherwise>
            </xsl:choose>

          </li>
        </xsl:for-each>
      </ul>
      <xsl:call-template name="float-fix" />
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-catalog-show-azlist">
  <xsl:param name="pageContent" />
  <xsl:param name="catalog" />

  <xsl:if test="count($catalog/letter) &gt; 0">
    <div class="azlistContainer">
      <xsl:for-each select="$catalog/letter">
        <xsl:if test="count(link) &gt; 0">
          <h2 id="{@title}"><xsl:value-of select="@title" /></h2>
          <div class="catalogList">
            <xsl:call-template name="catalog-az-list-show-links">
              <xsl:with-param name="links" select="."/>
            </xsl:call-template>
            <a class="ctalogLinkToTop" href="#link_content">
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">LINK_TO_TOP</xsl:with-param>
              </xsl:call-template>
            </a>
          </div>
        </xsl:if>
      </xsl:for-each>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="catalog-az-list-show-links">
  <xsl:param name="links" />

  <xsl:if test="count($links/link) &gt; 0">
    <ul>
      <xsl:for-each select="$links/link">
        <li><a href="{@href}"><xsl:value-of select="@title" /></a></li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-catalog">
  <xsl:param name="pageContent" />

  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>

  <a id="link_content" name="link_content"> </a>

  <xsl:choose>
    <xsl:when test="$pageContent/catalog/category">
      <xsl:call-template name="module-content-catalog-show-one-category">
        <xsl:with-param name="pageContent" select="$pageContent" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-catalog-show-azlist-letters">
        <xsl:with-param name="catalog" select="$pageContent/catalog/letters" />
        <xsl:with-param name="pageContent" select="$pageContent" />
        <xsl:with-param name="showLink" select="true()" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:call-template name="module-content-catalog-show-categories">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-catalog-show-one-category">
  <xsl:param name="pageContent" />

  <xsl:if test="$pageContent/catalog/category">
    <div class="catalogShowOneCategory">
      <h2><xsl:value-of select="$pageContent/catalog/category/@title" /></h2>
      <xsl:if test="$pageContent/catalog/category/image/img">
        <xsl:apply-templates select="$pageContent/catalog/category/image/img" />
      </xsl:if>
      <xsl:if test="$pageContent/catalog/category/text">
        <xsl:apply-templates select="$pageContent/catalog/category/text/node()" />
      </xsl:if>
      <xsl:call-template name="float-fix" />
      <xsl:call-template name="module-content-catalog-show-links">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-catalog-show-categories">
  <xsl:param name="pageContent" />

  <xsl:for-each select="$pageContent/catalog/letters/letter">
    <xsl:variable name="chargroup" select="@title" />

    <xsl:if test="count($pageContent/catalog/categories/category[@chargroup = $chargroup]) &gt; 0">
      <h2 id="{@title}"><xsl:value-of select="@title" /></h2>
      <div class="catalogCategories">
        <xsl:for-each select="$pageContent/catalog/categories/category[@chargroup = $chargroup]">
          <div class="catalogOneCategory">
            <xsl:if test="glyph/img">
              <a href="{@href}">
                <xsl:apply-templates select="glyph/img" /></a>
            </xsl:if>
            <a href="{@href}">
              <xsl:value-of select="@title" />
            </a>
            <xsl:if test="teaser != ''">
              <div class="categoryTeaser">
                <xsl:apply-templates select="teaser/node()" />
              </div>
            </xsl:if>
            <xsl:if test="count(links/link) &gt; 0">
              <ul>
                <xsl:for-each select="links/link">
                  <li><a href="{@href}"><xsl:value-of select="@title" /></a></li>
                </xsl:for-each>
              </ul>
              <xsl:call-template name="float-fix" />
            </xsl:if>
          </div>
        </xsl:for-each>
        <a class="ctalogLinkToTop" href="#link_content">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">LINK_TO_TOP</xsl:with-param>
          </xsl:call-template>
        </a>
      </div>
    </xsl:if>
  </xsl:for-each>
</xsl:template>

<xsl:template name="module-content-catalog-show-links">
  <xsl:param name="pageContent" />

  <xsl:for-each select="$pageContent/catalog/links/link">
    <div class="catalogLinks">
      <h3><a href="{@href}"><xsl:value-of select="@title" /></a></h3>
      <xsl:if test="teaser/subtopic">
        <xsl:if test="teaser/subtopic/title">
          <h4><xsl:value-of select="teaser/subtopic/title" /></h4>
        </xsl:if>
        <xsl:if test="teaser/subtopic/subtitle">
          <h5><xsl:value-of select="teaser/subtopic/subtitle" /></h5>
        </xsl:if>
        <xsl:value-of select="teaser/subtopic/text" />
      </xsl:if>
    </div>
  </xsl:for-each>
  <a class="ctalogLinkToTop" href="#link_content">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">LINK_TO_TOP</xsl:with-param>
    </xsl:call-template>
  </a>
</xsl:template>

</xsl:stylesheet>