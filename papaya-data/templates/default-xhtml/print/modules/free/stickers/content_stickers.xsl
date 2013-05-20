<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_stickers.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_stickers'">
      <xsl:call-template name="module-content-stickers">
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

<xsl:template name="module-content-stickers">
  <xsl:param name="pageContent" />

  <h1><xsl:value-of select="$pageContent/title"/></h1>

  <div class="contentStickers">
    <xsl:call-template name="sticker-navigation">
      <xsl:with-param name="paging" select="$pageContent/collection/paging"/>
    </xsl:call-template>
    <xsl:call-template name="sticker-items">
      <xsl:with-param name="stickerItems" select="$pageContent/collection/stickers/sticker"/>
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="sticker-navigation">
  <xsl:param name="paging" />
  <div class="stickersPaging">
    <xsl:if test="count($paging/page) &gt; 1">
      <ul>
        <li class="pagingDirection">
          <xsl:choose>
            <xsl:when test="$paging/@backlink != ''">
              <a href="{$paging/@backlink}">&#171;</a>
            </xsl:when>
            <xsl:otherwise>
              <span class="disabledPaging">&#171;</span>
            </xsl:otherwise>
          </xsl:choose>
        </li>
        <xsl:for-each select="$paging/page">
          <xsl:choose>
            <xsl:when test="@selected">
              <li><strong><a href="{@href}"><xsl:value-of select="@id" /></a></strong></li>
            </xsl:when>
            <xsl:otherwise>
              <li><a href="{@href}"><xsl:value-of select="@id" /></a></li>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
        <li class="pagingDirection">
          <xsl:choose>
            <xsl:when test="$paging/@nextlink != ''">
              <a href="{$paging/@nextlink}">&#187;</a>
            </xsl:when>
            <xsl:otherwise>
              <span class="disabledPaging">&#187;</span>
            </xsl:otherwise>
          </xsl:choose>
        </li>
      </ul>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="sticker-items">
  <xsl:param name="stickerItems" />

  <div class="stickerItems">
    <xsl:if test="count($stickerItems) &gt; 0">
      <ul>
        <xsl:for-each select="$stickerItems">
          <li class="sticker">
            <div class="stickerId">#<xsl:value-of select="@id" /></div>
            <xsl:if test="text/text() != ''">
              <div class="stickerText"><xsl:value-of select="text" /></div>
            </xsl:if>
            <xsl:if test="image/img/@src != ''">
              <div class="stickerImage"><xsl:apply-templates select="image/img" /></div>
            </xsl:if>
            <xsl:call-template name="sticker-author">
              <xsl:with-param name="stickerAuthor" select="@author" />
            </xsl:call-template>
          </li>
        </xsl:for-each>
      </ul>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="sticker-author">
  <xsl:param name="stickerAuthor" />
  <xsl:if test="$stickerAuthor != ''">
    <div class="stickerAuthor">
      - <xsl:value-of select="$stickerAuthor" />
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>