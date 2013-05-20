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
    <xsl:when test="$pageContent/@module = 'content_wiki_upload'">
      <xsl:call-template name="module-content-wiki-upload">
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

<xsl:template name="module-content-wiki-upload">
  <xsl:param name="pageContent" select="/page/content/topic" />
  <h1><xsl:value-of select="$pageContent/title/text()" /></h1>
  <xsl:call-template name="show-messages">
    <xsl:with-param name="content" select="$pageContent" />
  </xsl:call-template>
  <xsl:call-template name="article-selector">
    <xsl:with-param name="content" select="$pageContent" />
  </xsl:call-template>
  <xsl:if test="$pageContent/info">
    <div class="info">
      <xsl:value-of select="$pageContent/info/text()" />
    </div>
  </xsl:if>
  <xsl:apply-templates select="$pageContent/text/*|$pageContent/text/text()" />
  <xsl:if test="$pageContent/dialog">
    <xsl:variable name="dialog" select="$pageContent/dialog" />
    <xsl:variable name="lines" select="$dialog/lines" />
    <form action="{$dialog/@action}" method="{$dialog/@method}" enctype="{$dialog/@enctype}">
      <xsl:copy-of select="$dialog/input[@type = 'hidden']" />
      <table class="form">
        <tr>
          <th>
            <xsl:value-of select="$lines/line[@fid = 'file']/@caption" />
          </th>
          <td>
            <xsl:call-template name="formInput">
              <xsl:with-param name="field" select="$lines/line[@fid = 'file']/input" />
            </xsl:call-template>
          </td>
        </tr>
        <tr>
          <th>
            <xsl:value-of select="$lines/line[@fid = 'filename']/@caption" />
          </th>
          <td>
            <xsl:call-template name="formInput">
              <xsl:with-param name="field" select="$lines/line[@fid = 'filename']/input" />
            </xsl:call-template>
          </td>
        </tr>
        <tr>
          <th>
            <xsl:value-of select="$lines/line[@fid = 'title']/@caption" />
          </th>
          <td>
            <xsl:call-template name="formInput">
              <xsl:with-param name="field" select="$lines/line[@fid = 'title']/input" />
            </xsl:call-template>
          </td>
        </tr>
        <tr>
          <th>
            <xsl:value-of select="$lines/line[@fid = 'description']/@caption" />
          </th>
          <td>
            <xsl:variable name="field" select="$lines/line[@fid = 'description']/textarea" />
            <textarea name="{$field/@name}" class="{$field/@class}" rows="{$field/@rows}" cols="{$field/@cols}" wrap="{$field/@wrap}">
              <xsl:value-of select="$field/text()" />
            </textarea>
          </td>
        </tr>
      </table>
      <input type="submit" value="{$dialog/dlgbutton/@value}" />
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

<xsl:template name="formInput">
  <xsl:param name="field" />
  <input type="{$field/@type}" name="{$field/@name}">
    <xsl:if test="$field/@id != ''">
      <xsl:attribute name="id"><xsl:value-of select="$field/@id" /></xsl:attribute>
    </xsl:if>
    <xsl:if test="$field/@class != ''">
      <xsl:attribute name="class"><xsl:value-of select="$field/@class" /></xsl:attribute>
    </xsl:if>
    <xsl:if test="$field/@value != ''">
      <xsl:attribute name="value"><xsl:value-of select="$field/@value" /></xsl:attribute>
    </xsl:if>
    <xsl:if test="$field/@size != ''">
      <xsl:attribute name="size"><xsl:value-of select="$field/@size" /></xsl:attribute>
    </xsl:if>
    <xsl:if test="$field/@maxlength != ''">
      <xsl:attribute name="maxlength"><xsl:value-of select="$field/@maxlength" /></xsl:attribute>
    </xsl:if>
    <xsl:if test="$field/@checked != ''">
      <xsl:attribute name="checked"><xsl:value-of select="$field/@checked" /></xsl:attribute>
    </xsl:if>
  </input>
</xsl:template>

</xsl:stylesheet>
