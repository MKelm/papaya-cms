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
    <xsl:when test="$pageContent/@module = 'content_wiki'">
      <xsl:call-template name="module-content-wiki">
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

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_wiki.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="page-title">
  <xsl:if test="/page/content/topic/wikipage/subtitle">
    <xsl:value-of select="/page/content/topic/wikipage/subtitle" />
    <xsl:text> - </xsl:text>
  </xsl:if>
  <xsl:value-of select="/page/content/topic/wikipage/title" />
</xsl:template>

<xsl:template name="page-translations">
  <xsl:variable name="index" select="/page/content/topic/wikipage/article-translations/index/@href" />
  <xsl:if test="count(/page/translations/translation) &gt; 1">
    <ul class="pageTranslations">
      <xsl:for-each select="/page/translations/translation">
        <li>
          <xsl:if test="@selected">
            <xsl:attribute name="class">selected</xsl:attribute>
          </xsl:if>
          <xsl:variable name="lng_short" select="@lng_short"/>
          <xsl:variable name="lng_title" select="@lng_title"/>
          <xsl:variable name="selected" select="@selected"/>
          <xsl:choose>
            <xsl:when test="/page/content/topic/wikipage/article-translations/translation/@lng = $lng_short">
              <a href="{/page/content/topic/wikipage/article-translations/translation[@lng = $lng_short]/@href}"><xsl:value-of select="$lng_title"/></a>
            </xsl:when>
            <xsl:otherwise>
              <a href="{$index}"><xsl:value-of select="$lng_title"/></a>
            </xsl:otherwise>
          </xsl:choose>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-wiki">
  <xsl:param name="pageContent"/>
  <xsl:variable name="mode" select="$pageContent/wikipage/mode/text()"/>
  <h1><xsl:value-of select="$pageContent/wikipage/title/text()"/></h1>
  <xsl:call-template name="show-messages">
    <xsl:with-param name="content" select="$pageContent/wikipage" />
  </xsl:call-template>
  <xsl:call-template name="article-selector">
    <xsl:with-param name="content" select="$pageContent/wikipage" />
  </xsl:call-template>
  <h2><xsl:value-of select="$pageContent/wikipage/subtitle/text()"/></h2>
  <xsl:if test="$pageContent/wikipage/links/link">
    <div class="wikiNavBar">
    [
    <xsl:for-each select="$pageContent/wikipage/links/link">
      <xsl:if test="position() &gt; 1">
        |
      </xsl:if>
      <xsl:choose>
        <xsl:when test="@mode = $mode">
          <xsl:value-of select="@caption"/>
        </xsl:when>
        <xsl:otherwise>
          <a href="{@href}"><xsl:value-of select="@caption"/></a>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
    ]
    </div>
  </xsl:if>
  <xsl:if test="$pageContent/wikipage/file-details">
    <xsl:call-template name="file-details">
      <xsl:with-param name="details" select="$pageContent/wikipage/file-details" />
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="$pageContent/wikipage/teaser">
    <div class="wikiTeaserArea">
      <xsl:value-of select="$pageContent/wikipage/teaser/text()"/>
      <xsl:if test="$pageContent/wikipage/teaser-link">
        <xsl:text> </xsl:text>
        <a href="{$pageContent/wikipage/teaser-link/text()}"><xsl:value-of select="$pageContent/wikipage/teaser-link/@caption"/></a>
      </xsl:if>
    </div>
  </xsl:if>
  <xsl:choose>
    <xsl:when test="$pageContent/wikipage/redirected-from">
      <xsl:variable name="redirection" select="$pageContent/wikipage/redirection"/>
      <div class="wikiRedirectionInfo">
        <ul>
          <xsl:for-each select="$pageContent/wikipage/redirected-from">
            <li><xsl:value-of select="$redirection/caption[@for='redirected-from']/text()"/><xsl:text> </xsl:text><a href="{$redirection/@url}?{$redirection/@param-node}={@node}&amp;{$redirection/@param-noredir}"><xsl:value-of select="@node"/></a></li>
          </xsl:for-each>
        </ul>
      </div>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="$pageContent/wikipage/redirect">
        <xsl:variable name="redirection" select="$pageContent/wikipage/redirection"/>
        <div class="wikiRedirection">
          <ul>
            <xsl:for-each select="$pageContent/wikipage/redirect">
              <li><xsl:value-of select="$redirection/caption[@for='redirection-to']/text()"/><xsl:text> </xsl:text><a href="{$redirection/@url}?{$redirection/@param-node}={@node}"><xsl:value-of select="@node"/></a></li>
            </xsl:for-each>
          </ul>
        </div>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:if test="$pageContent/wikipage/create-link">
    <p><a href="{$pageContent/wikipage/create-link/@href}"><xsl:value-of select="$pageContent/wikipage/create-link/text()" /></a></p>
  </xsl:if>
  <xsl:if test="$pageContent/wikipage/search-results/link">
    <xsl:call-template name="search-results">
      <xsl:with-param name="results" select="$pageContent/wikipage/search-results" />
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="$pageContent/wikipage/comparison">
    <div class="wikiCompareArea">
      <table>
        <tr>
          <th><xsl:value-of select="$pageContent/wikipage/comparison/@old"/></th>
          <th><xsl:value-of select="$pageContent/wikipage/comparison/@new"/></th>
        </tr>
        <xsl:for-each select="$pageContent/wikipage/comparison/*">
          <xsl:if test="local-name() = 'del'">
            <tr>
              <td class="deleted" style="background-color: #FF9999">
                <xsl:value-of select="./text()"/>
              </td>
              <td></td>
            </tr>
          </xsl:if>
          <xsl:if test="local-name() = 'ins'">
            <tr>
              <td></td>
              <td class="inserted" style="background-color: #99FF99">
                <xsl:value-of select="./text()"/>
              </td>
            </tr>
          </xsl:if>
          <xsl:if test="local-name() = 'subst'">
            <tr>
              <td class="substitute" style="background-color: #FFFF99">
                <xsl:value-of select="from/text()"/>
              </td>
              <td class="substitute" style="background-color: #FFFF99">
                <!--
                <xsl:for-each select="by/*">
                  <xsl:if test="local-name() = 'ins'">
                    <span class="insertedWord" style="color: #009900; text-decoration: underline"><xsl:value-of select="./text()"/></span>
                  </xsl:if>
                  <xsl:if test="local-name() = 'del'">
                    <span class="deletedWord" style="color: #FF0000; text-decoration: line-through"><xsl:value-of select="./text()"/></span>
                  </xsl:if>
                  <xsl:if test="local-name() = 'unchanged'">
                    <xsl:value-of select="./text()"/>
                  </xsl:if>
                  <xsl:text> </xsl:text>
                </xsl:for-each>
                -->
                <xsl:value-of select="by/text()"/>
              </td>
            </tr>
          </xsl:if>
          <xsl:if test="local-name() = 'unchanged'">
            <tr>
              <td><xsl:value-of select="./text()"/></td>
              <td><xsl:value-of select="./text()"/></td>
            </tr>
          </xsl:if>
        </xsl:for-each>
      </table>
    </div>
  </xsl:if>
  <xsl:if test="$pageContent/wikipage/wiki">
    <div class="wikiArticle">
      <xsl:call-template name="show-wiki">
        <xsl:with-param name="wiki" select="$pageContent/wikipage/wiki"/>
      </xsl:call-template>
    </div>
    <xsl:if test="$pageContent/wikipage/categories">
      <div class="wikiCategories">
        <xsl:value-of select="$pageContent/wikipage/categories/@caption"/>:
        <xsl:for-each select="$pageContent/wikipage/categories/wiki-link">
          <xsl:text> </xsl:text>
          <xsl:apply-templates select="."/>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:if>
  <xsl:if test="$pageContent/wikipage/category-data">
    <div class="wikiArticle">
      <xsl:if test="$pageContent/wikipage/category-data/subcategories">
        <h2><xsl:value-of select="$pageContent/wikipage/category-data/subcategories/text()"/></h2>
        <ul>
        <xsl:for-each select="$pageContent/wikipage/category-data/wiki-link[@subcategory = 'true']">
          <li>
            <xsl:apply-templates select="."/>
          </li>
        </xsl:for-each>
        </ul>
      </xsl:if>
      <ul>
        <xsl:for-each select="$pageContent/wikipage/category-data/wiki-link[@subcategory != 'true']">
          <li>
            <xsl:apply-templates select="."/>
          </li>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:if>
  <xsl:if test="$mode = 'preview' or $mode = 'edit'">
    <xsl:if test="$pageContent/wikipage/edit">
      <div class="wikiEditor">
        <form action="{$pageContent/wikipage/edit/@href}" method="post">
          <input type="hidden" name="{$pageContent/wikipage/article/@param}" value="{$pageContent/wikipage/article/@node}"/>
          <input type="hidden" name="{$pageContent/wikipage/edit/mode/@param}" value="preview"/>
          <textarea name="{$pageContent/wikipage/edit/source/@name}" cols="100" rows="15" class="wikiEditor" wrap="virtual">
            <xsl:value-of select="$pageContent/wikipage/edit/source/text()"/>
          </textarea>
          <xsl:value-of select="$pageContent/wikipage/edit/comment/@caption"/>
          <xsl:text> </xsl:text>
          <input type="text" name="{$pageContent/wikipage/edit/comment/@name}" value="{$pageContent/wikipage/edit/comment/@value}"/>
          <input type="submit" name="{$pageContent/wikipage/edit/preview/@param}" value="{$pageContent/wikipage/edit/preview/@caption}"/>
          <input type="submit" name="{$pageContent/wikipage/edit/save/@param}" value="{$pageContent/wikipage/edit/save/@caption}"/>
        </form>
      </div>
    </xsl:if>
  </xsl:if>
  <xsl:if test="$mode = 'versions' and $pageContent/wikipage/versions">
    <form action="{$pageContent/wikipage/versions/@href}" method="get">
      <xsl:for-each select="$pageContent/wikipage/versions/hidden">
        <input type="hidden" name="{@name}" value="{@value}"/>
      </xsl:for-each>
      <div class="wikiVersions">
        <table>
          <tr>
            <th><xsl:value-of select="$pageContent/wikipage/versions/caption[@for='version']/@value"/></th>
            <th>&#160;</th>
            <th><xsl:value-of select="$pageContent/wikipage/versions/caption[@for='author']/@value"/></th>
            <th><xsl:value-of select="$pageContent/wikipage/versions/caption[@for='comment']/@value"/></th>
          </tr>
          <xsl:for-each select="$pageContent/wikipage/versions/version">
            <tr>
              <td style="border-right: 1px solid #000000; padding: 5px"><a href="{@link}"><xsl:value-of select="@timestamp"/></a></td>
              <td style="border-right: 1px solid #000000; padding: 5px">
                <input type="radio" name="{$pageContent/wikipage/versions/@param-name}[old]" value="{@plaintimestamp}"/>
                <input type="radio" name="{$pageContent/wikipage/versions/@param-name}[new]" value="{@plaintimestamp}"/>
              </td>
              <td style="border-right: 1px solid #000000; padding: 5px"><xsl:value-of select="@author"/></td>
              <td style="padding: 5px"><xsl:value-of select="@comment"/></td>
            </tr>
          </xsl:for-each>
        </table>
        <input type="submit" value="{$pageContent/wikipage/versions/@caption}"/>
      </div>
    </form>
  </xsl:if>
</xsl:template>

<xsl:template name="show-messages">
  <xsl:param name="content" />
  <xsl:for-each select="$content/message">
    <div>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="@type = 'error'">message error</xsl:when>
          <xsl:otherwise>message info</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:value-of select="text()" />
    </div>
  </xsl:for-each>
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

<xsl:template name="search-results">
  <xsl:param name="results" />
  <ul>
    <xsl:for-each select="$results/link">
      <li><a href="{./@href}"><xsl:value-of select="./@title" /></a><br />
      <xsl:value-of select="./text()" /></li>
    </xsl:for-each>
  </ul>
</xsl:template>

<xsl:template name="show-wiki">
  <xsl:param name="wiki"/>
  <xsl:apply-templates select="$wiki" />
</xsl:template>

<xsl:template match="nowiki">
  <pre>
    <xsl:value-of select="./text()"/>
  </pre>
</xsl:template>

<xsl:template match="headline">
  <xsl:call-template name="float-fix" />
  <xsl:if test="@anchor">
    <a name="{@anchor}"></a>
  </xsl:if>
  <xsl:choose>
    <xsl:when test="@level = '1'">
      <h1><xsl:apply-templates/></h1>
    </xsl:when>
    <xsl:when test="@level = '2'">
      <h2><xsl:apply-templates/></h2>
    </xsl:when>
    <xsl:when test="@level = '3'">
      <h3><xsl:apply-templates/></h3>
    </xsl:when>
    <xsl:when test="@level = '4'">
      <h4><xsl:apply-templates/></h4>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates select="*|text()" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="toc">
  <xsl:if test="toc-item">
    <xsl:call-template name="float-fix" />
    <div class="wikiTOC">
      <h2><a name="toc"></a><xsl:value-of select="//caption[@for = 'toc']"/></h2>
      <xsl:for-each select="toc-item">
        <p><a href="{@href}"><xsl:value-of select="text()"/></a></p>
      </xsl:for-each>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template match="list">
  <xsl:choose>
    <xsl:when test="@type = 'bullet'">
      <ul><xsl:apply-templates/></ul>
    </xsl:when>
    <xsl:when test="@type = 'numeric'">
      <ol><xsl:apply-templates/></ol>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates select="*|text()" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="item">
  <li><xsl:apply-templates select="*|text()" /></li>
</xsl:template>

<xsl:template match="deflist">
  <dl><xsl:apply-templates select="*|text()" /></dl>
</xsl:template>

<xsl:template match="deftitle">
  <dt><xsl:apply-templates select="*|text()" /></dt>
</xsl:template>

<xsl:template match="definition">
  <dd><xsl:apply-templates select="*|text()" /></dd>
</xsl:template>

<xsl:template match="indent">
  <xsl:call-template name="do-indent">
    <xsl:with-param name="steps" select="@steps"/>
    <xsl:with-param name="content" select="*|text()"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="do-indent">
  <xsl:param name="steps"/>
  <xsl:param name="content"/>
  <xsl:choose>
    <xsl:when test="$steps &lt;= 0">
      <xsl:apply-templates select="$content" />
    </xsl:when>
    <xsl:otherwise>
      <blockquote>
        <xsl:call-template name="do-indent">
          <xsl:with-param name="steps" select="$steps - 1"/>
          <xsl:with-param name="content" select="$content"/>
        </xsl:call-template>
      </blockquote>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="ref">
  <a name="text{@no}">&#160;</a><sup><a href="#ref{@no}">[<xsl:value-of select="@no" />]</a></sup>
</xsl:template>

<xsl:template match="references">
  <ol>
    <xsl:for-each select="reftext">
      <li><a name="ref{@id}">&#160;</a><xsl:apply-templates select="*|text()" />
      &#160;
      <a href="#text{@id}">^</a></li>
    </xsl:for-each>
  </ol>
</xsl:template>

<xsl:template match="table">
  <table>
    <xsl:for-each select="@*">
      <xsl:attribute name="{name()}"><xsl:value-of select="*|text()"/></xsl:attribute>
    </xsl:for-each>
    <xsl:apply-templates select="*|text()" />
  </table>
</xsl:template>

<xsl:template match="caption">
  <caption><xsl:apply-templates select="*|text()" /></caption>
</xsl:template>

<xsl:template match="row">
  <tr><xsl:apply-templates select="*|text()" /></tr>
</xsl:template>

<xsl:template match="headline-cell">
  <th>
    <xsl:for-each select="@*">
      <xsl:attribute name="{name()}"><xsl:value-of select="."/></xsl:attribute>
    </xsl:for-each>
    <xsl:apply-templates select="*|text()" />
  </th>
</xsl:template>

<xsl:template match="cell">
  <td>
    <xsl:for-each select="@*">
      <xsl:attribute name="{name()}"><xsl:value-of select="."/></xsl:attribute>
    </xsl:for-each>
    <xsl:apply-templates select="*|text()" />
  </td>
</xsl:template>

<xsl:template match="wiki-link">
  <xsl:variable name="article" select="/page/content/topic/wikipage/article"/>
  <xsl:variable name="node" select="@node"/>
  <xsl:variable name="class">
    <xsl:choose>
      <xsl:when test="@exists = 'true' or /page/content/topic/wikipage/mode/text() != 'read'">wikiLinkExisting</xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="/page/content/topic/wikipage/wiki-links/link[@node = $node]/@exists = 'true'">wikiLinkExisting</xsl:when>
          <xsl:otherwise>wikiLinkNew</xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <a class="{$class}">
    <xsl:attribute name="href">
      <xsl:choose>
        <xsl:when test="@href != ''"><xsl:value-of select="@href" /></xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="/page/content/topic/wikipage/wiki-links/link[@node = $node]/@href" />
        </xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
    <xsl:apply-templates select="*|text()"/>
  </a>
</xsl:template>

<xsl:template match="media">
  <xsl:variable name="fileindex" select="@index" />
  <xsl:variable name="file" select="/page/content/topic/wikipage/files/file[@index = $fileindex]" />
  <xsl:if test="$file">
    <xsl:choose>
      <xsl:when test="$file/@type = 'image'">
        <xsl:choose>
          <xsl:when test="@border = 'true'">
            <div class="imageHolder">
              <xsl:if test="@align != ''">
                <xsl:attribute name="align"><xsl:value-of select="@align" /></xsl:attribute>
              </xsl:if>
              <xsl:attribute name="style">
                <xsl:choose>
                  <xsl:when test="@width &gt; 300">width:<xsl:value-of select="@width + 20" />px;</xsl:when>
                  <xsl:otherwise>width:320px;</xsl:otherwise>
                </xsl:choose>
                <xsl:if test="@align = 'left' or @align='right'">float:<xsl:value-of select="@align" />;</xsl:if>
              </xsl:attribute>
              <a href="{$file/@href}"><img src="{$file/@src}" alt="{$file/@title}" title="{$file/@title}" /></a><br />
              <strong><xsl:value-of select="$file/@title" /></strong><br />
              <xsl:value-of select="$file/text()" />
            </div>
          </xsl:when>
          <xsl:otherwise>
            <a href="{$file/@href}"><img src="{$file/@src}" alt="{$file/@title}" title="{$file/@title}">
              <xsl:if test="@align != ''">
                <xsl:attribute name="align"><xsl:value-of select="@align" /></xsl:attribute>
              </xsl:if>
            </img></a>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="@border = 'true'">
            <div class="imageHolder">
              <a href="{$file/@href}"><xsl:value-of select="$file/@title" /></a><br />
              <xsl:value-of select="$file/text()" />
            </div>
          </xsl:when>
          <xsl:otherwise>
            <a href="{$file/@href}"><xsl:value-of select="$file/@title" /></a>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
</xsl:template>

<xsl:template name="file-details">
  <xsl:param name="details" />
  <table class="fileDetails">
    <tr>
      <td colspan="2">
        <xsl:choose>
          <xsl:when test="$details/file/@type = 'image'">
            <a href="{$details/file/@href}"><img src="{$details/file/@href}" title="{$details/title/text()}" alt="{$details/title/text()}" /></a>
          </xsl:when>
          <xsl:otherwise>
            <a href="{$details/file/@href}"><xsl:value-of select="$details/title/text()" /></a>
          </xsl:otherwise>
        </xsl:choose>
      </td>
    </tr>
    <tr>
      <th><xsl:value-of select="$details/title/@caption" /></th>
      <td><xsl:value-of select="$details/title/text()" /></td>
    </tr>
    <tr>
      <th><xsl:value-of select="$details/description/@caption" /></th>
      <td><xsl:value-of select="$details/description/text()" /></td>
    </tr>
    <tr>
      <th><xsl:value-of select="$details/size/@caption" /></th>
      <td><xsl:value-of select="$details/size/text()" /></td>
    </tr>
    <tr>
      <th><xsl:value-of select="$details/type/@caption" /></th>
      <td><xsl:value-of select="$details/type/text()" /></td>
    </tr>
    <xsl:if test="$details/imagesize">
      <tr>
        <th><xsl:value-of select="$details/imagesize/@caption" /></th>
        <td><xsl:value-of select="$details/imagesize/@width" /> x <xsl:value-of select="$details/imagesize/@height" /> px</td>
      </tr>
    </xsl:if>
  </table>
</xsl:template>

<xsl:template match="external-link">
  <a class="externalLink" href="{@url}"><xsl:apply-templates select="*|text()"/></a>
</xsl:template>

<xsl:template match="bold">
  <b><xsl:apply-templates select="*|text()"/></b>
</xsl:template>

<xsl:template match="italic">
  <i><xsl:apply-templates select="*|text()"/></i>
</xsl:template>

<xsl:template match="text()">
  <xsl:value-of select="."/>
</xsl:template>

</xsl:stylesheet>
