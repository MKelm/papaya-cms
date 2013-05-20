<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:str="http://exslt.org/strings"
  extension-element-prefixes="str"
  exclude-result-prefixes="#default"
>

<xsl:import href="../../../../_functions/javascript-escape-string.xsl" />

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-scripts-lazy">
  <xsl:variable name="thumbs" select="/page/content/topic/thumbnails/thumb"/>
  <xsl:if test="$thumbs">
    <script type="text/javascript">
      jQuery(document).ready(
        function() {
          jQuery('.gallery').children().hide();
          jQuery('.gallery').papayaGallery(
            {},
            {
              images : {
                <xsl:for-each select="$thumbs">
                  <xsl:if test="position() &gt; 1">,&#10; </xsl:if>
                  <xsl:call-template name="javascript-escape-string">
                    <xsl:with-param name="string" select="str:encode-uri(string(a/@href), true())" />
                  </xsl:call-template>
                  <xsl:text> : </xsl:text>
                  <xsl:call-template name="javascript-escape-string">
                    <xsl:with-param name="string" select="@for" />
                  </xsl:call-template>
                </xsl:for-each>
              },
              getImageUrl : function(href) {
                if (this.images[href]) {
                  return this.images[href]
                } else {
                  var escapedHref = encodeURIComponent(href);
                  return (this.images[escapedHref]) ? this.images[escapedHref] : href;
                }
              }
            }
          );
        }
      );
   </script>
 </xsl:if>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_thumbs'">
      <xsl:call-template name="module-content-thumbs">
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

<xsl:template name="module-content-thumbs">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="withText" select="not($pageContent/image)" />
  </xsl:call-template>
  <div class="gallery galleryColumns{$pageContent/options/maxperline}">
    <xsl:choose>
      <xsl:when test="$pageContent/thumbnails/thumb">
        <xsl:call-template name="module-content-thumbs-list">
          <xsl:with-param name="thumbs" select="$pageContent/thumbnails/thumb" />
          <xsl:with-param name="options" select="$pageContent/options" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/image">
        <xsl:call-template name="module-content-thumbs-image-detail">
          <xsl:with-param name="image" select="$pageContent/image" />
          <xsl:with-param name="imageTitle" select="$pageContent/imagetitle" />
          <xsl:with-param name="imageComment" select="$pageContent/imagecomment" />
          <xsl:with-param name="navigation" select="$pageContent/navigation" />
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
    <xsl:call-template name="module-content-thumbns-navigation">
      <xsl:with-param name="navigation" select="$pageContent/navigation" />
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="module-content-thumbs-list">
  <xsl:param name="thumbs" />
  <xsl:param name="options" />
  <!-- thumbnail view -->
  <xsl:if test="$thumbs">
    <div class="galleryImages clearfix">
      <xsl:for-each select="$thumbs">
        <div class="galleryThumbnail">
          <a class="galleryThumbnailFrame"
             style="width: {$options/thumbwidth}px; height: {$options/thumbheight}px;"
             href="{a/@href}"
             title="{image/@title}">
            <img src="{a/img/@src}" style="{a/img/@style}" alt="{a/img/@alt}"/>
          </a>
        </div>
      </xsl:for-each>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-thumbs-image-detail">
  <xsl:param name="image" />
  <xsl:param name="imageTitle" />
  <xsl:param name="imageComment" />
  <xsl:param name="navigation" />
  <xsl:if test="$image">
    <div class="galleryImage">
      <xsl:choose>
        <xsl:when test="$navigation/navlink[@dir='index']">
          <a href="{$navigation/navlink[@dir='index']/@href}">
            <img src="{$image/img/@src}" style="{$image/img/@style}" alt="{$image/img/@alt}"/>
          </a>
        </xsl:when>
        <xsl:otherwise>
          <img src="{$image/img/@src}" style="{$image/img/@style}" alt="{$image/img/@alt}"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="$imageTitle">
        <h2><xsl:value-of select="$imageTitle"/></h2>
      </xsl:if>
      <xsl:if test="$imageComment">
        <div class="comment">
          <xsl:apply-templates select="$imageComment/node()" mode="richtext"/>
        </div>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-thumbns-navigation">
  <xsl:param name="navigation" />
  <xsl:if test="$navigation/navlink[(@dir='prior') or (@dir='next')]">
    <div class="galleryNavigation clearfix">
      <xsl:if test="$navigation/navlink[@dir='prior']">
        <a href="{$navigation/navlink[@dir='prior']/@href}" class="navigationLinkPrevious">&#8656;</a>
      </xsl:if>
      <xsl:if test="$navigation/navlink[@dir='next']">
        <a href="{$navigation/navlink[@dir='next']/@href}" class="navigationLinkNext">&#8658;</a>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
