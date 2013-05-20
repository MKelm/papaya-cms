<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="../../../../_functions/javascript-escape-string.xsl" />

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_thumbs.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="page-scripts-lazy">
  <xsl:if test="/page/content/topic/options/mode/@lightbox = '1'">
    <xsl:call-template name="link-script">
      <xsl:with-param name="file">papaya/jquery.xmlns.js</xsl:with-param>
    </xsl:call-template>
    <xsl:call-template name="link-script">
      <xsl:with-param name="file">papaya/jquery.papayaLightbox.js</xsl:with-param>
    </xsl:call-template>
    <xsl:call-template name="link-script">
      <xsl:with-param name="file">papaya/jquery.papayaGallery.js</xsl:with-param>
    </xsl:call-template>
   <script type="text/javascript"><xsl:comment>
      jQuery(document).ready(
        function() {
          jQuery('#gallery').papayaGallery();
        }
      );
    </xsl:comment></script>
  </xsl:if>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_thumbs' or $pageContent/@module = 'ACommunitySurferGalleryPage'">
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
  <div id="gallery">
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
          <xsl:with-param name="originalImage" select="$pageContent/originalimage" />
          <xsl:with-param name="imageDownload" select="$pageContent/imagedownload" />
          <xsl:with-param name="navigation" select="$pageContent/navigation" />
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
    <xsl:call-template name="float-fix"/>
    <xsl:choose>
      <xsl:when test="count($pageContent/thumbnails/thumb) &gt; 0 or count($pageContent/image) &gt; 0">
        <xsl:call-template name="module-content-thumbns-navigation">
          <xsl:with-param name="navigation" select="$pageContent/navigation" />
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <div class="message"><xsl:call-template name="language-text">
          <xsl:with-param name="text" select="'NO_IMAGES'"/>
        </xsl:call-template></div>
      </xsl:otherwise>
    </xsl:choose>
  </div>
</xsl:template>

<xsl:template name="module-content-thumbs-list">
  <xsl:param name="thumbs" />
  <xsl:param name="options" />
  <!-- thumbnail view -->
  <xsl:if test="$thumbs">
    <xsl:for-each select="$thumbs">
      <div class="galleryThumbnail">
        <a class="galleryThumbnailFrame"
           style="width: {$options/thumbwidth}px; height: {$options/thumbheight}px;"
           href="{a/@href}"
           title="{image/@title}">
          <img src="{a/img/@src}" alt="{a/img/@alt}"/>
        </a>
      </div>
    </xsl:for-each>
    <xsl:if test="$options/mode/@lightbox = '1'">
      <script type="text/javascript"><xsl:comment>
        jQuery('#gallery').children().hide();
        var galleryMapping = {
          images : {
            <xsl:for-each select="$thumbs">
              <xsl:if test="position() &gt; 1">, </xsl:if>
              <xsl:call-template name="javascript-escape-string">
                <xsl:with-param name="string" select="a/@href" />
              </xsl:call-template>
              <xsl:text> : </xsl:text>
              <xsl:call-template name="javascript-escape-string">
                <xsl:with-param name="string" select="@for" />
              </xsl:call-template>
            </xsl:for-each>
          },
          getImageUrl : function(href) {
            return (this.images[href]) ? this.images[href] : href;
          }
        };
      </xsl:comment></script>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-thumbs-image-detail">
  <xsl:param name="image" />
  <xsl:param name="imageTitle" />
  <xsl:param name="imageComment" />
  <xsl:param name="originalImage" />
  <xsl:param name="imageDownload" />
  <xsl:param name="navigation" />
  <xsl:if test="$image">
    <div class="galleryImage">
      <xsl:choose>
        <xsl:when test="$navigation/navlink[@dir='index']">
          <a href="{$navigation/navlink[@dir='index']/@href}">
            <img src="{$image/img/@src}" alt="{$image/img/@alt}"/>
          </a>
        </xsl:when>
        <xsl:otherwise>
          <img src="{$image/img/@src}" alt="{$image/img/@alt}"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="$imageTitle">
        <h2><xsl:value-of select="$imageTitle"/></h2>
      </xsl:if>
      <xsl:if test="$imageComment">
        <div class="comment">
          <xsl:apply-templates select="$imageComment/node()"/>
        </div>
      </xsl:if>
      <xsl:if test="$originalImage">
        <div class="originalImage"><a href="{$originalImage/@href}"><xsl:call-template name="language-text">
          <xsl:with-param name="text" select="'ORIGINAL_IMAGE'"/>
        </xsl:call-template></a></div>
      </xsl:if>
      <xsl:if test="$imageDownload">
        <div class="imageDownload"><a href="{$imageDownload/@href}"><xsl:call-template name="language-text">
          <xsl:with-param name="text" select="'IMAGE_DOWNLOAD'"/>
        </xsl:call-template></a></div>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-thumbns-navigation">
  <xsl:param name="navigation" />
  <xsl:if test="$navigation/navlink[(@dir='prior') or (@dir='next')]">
    <div class="galleryNavigation">
      <xsl:if test="$navigation/navlink[@dir='prior']">
        <a href="{$navigation/navlink[@dir='prior']/@href}" class="navigationLinkPrevious">&lt;-</a>
      </xsl:if>
      <xsl:if test="$navigation/navlink[@dir='next']">
        <a href="{$navigation/navlink[@dir='next']/@href}" class="navigationLinkNext">-&gt;</a>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
