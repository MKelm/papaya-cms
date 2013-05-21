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
    <xsl:if test="/page/content/topic/options/lightbox = '1'">
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
      <xsl:when test="$pageContent/@module = 'MediaImageGalleryPage'">
        <xsl:call-template name="module-content-image-gallery">
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

  <xsl:template name="module-content-image-gallery">
    <xsl:param name="pageContent"/>
    <xsl:call-template name="module-content-topic">
      <xsl:with-param name="pageContent" select="$pageContent" />
      <xsl:with-param name="withText" select="not($pageContent/image)" />
    </xsl:call-template>
    <div id="gallery">
      <xsl:choose>
        <xsl:when test="$pageContent/images/image">
          <xsl:call-template name="module-content-gallery-images">
            <xsl:with-param name="images" select="$pageContent/images/image" />
            <xsl:with-param name="options" select="$pageContent/options" />
          </xsl:call-template>
        </xsl:when>
        <xsl:when test="$pageContent/image">
          <xsl:call-template name="module-content-gallery-image">
            <xsl:with-param name="image" select="$pageContent/image" />
            <xsl:with-param name="navigation" select="$pageContent/navigation" />
          </xsl:call-template>
        </xsl:when>
      </xsl:choose>
      <xsl:call-template name="float-fix"/>
      <xsl:choose>
        <xsl:when test="count($pageContent/images/image) &gt; 0 or count($pageContent/image) &gt; 0">
          <xsl:call-template name="module-content-gallery-navigation">
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

  <xsl:template name="module-content-gallery-images">
    <xsl:param name="images" />
    <xsl:param name="options" />
    <xsl:if test="$images">
      <xsl:for-each select="$images">
        <div class="galleryThumbnail">
          <a class="galleryThumbnailFrame" href="{destination/@href}" title="{title}">
            <img src="{img/@src}" alt="{img/@alt}"/>
          </a>
        </div>
      </xsl:for-each>
      <xsl:if test="$options/lightbox = '1'">
        <script type="text/javascript"><xsl:comment>
          jQuery('#gallery').children().hide();
          var galleryMapping = {
            images : {
              <xsl:for-each select="$images">
                <xsl:if test="position() &gt; 1">, </xsl:if>
                <xsl:call-template name="javascript-escape-string">
                  <xsl:with-param name="string" select="destination/@href" />
                </xsl:call-template>
                <xsl:text> : </xsl:text>
                <xsl:call-template name="javascript-escape-string">
                  <xsl:with-param name="string" select="destination/img/@src" />
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

  <xsl:template name="module-content-gallery-image">
    <xsl:param name="image" />
    <xsl:param name="navigation" />
    <xsl:if test="$image">
      <div class="galleryImage">
        <xsl:choose>
          <xsl:when test="$navigation/navlink[@direction = 'index']">
            <a href="{$navigation/navlink[@direction = 'index']/@href}">
              <img src="{$image/img/@src}" alt="{$image/img/@alt}"/>
            </a>
          </xsl:when>
          <xsl:otherwise>
            <img src="{$image/img/@src}" alt="{$image/img/@alt}"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="$image/title">
          <h2><xsl:value-of select="$image/title"/></h2>
        </xsl:if>
        <xsl:if test="$image/description">
          <div class="description">
            <xsl:apply-templates select="$image/description"/>
          </div>
        </xsl:if>
        <xsl:if test="$image/original-link">
          <div class="originalImage"><a href="{$image/original-link}"><xsl:call-template name="language-text">
            <xsl:with-param name="text" select="'ORIGINAL_IMAGE'"/>
          </xsl:call-template></a></div>
        </xsl:if>
        <xsl:if test="$image/download-link">
          <div class="imageDownload"><a href="{$image/download-link}"><xsl:call-template name="language-text">
            <xsl:with-param name="text" select="'IMAGE_DOWNLOAD'"/>
          </xsl:call-template></a></div>
        </xsl:if>
      </div>
    </xsl:if>
  </xsl:template>

  <xsl:template name="module-content-gallery-navigation">
    <xsl:param name="navigation" />
    <xsl:if test="$navigation/navlink[(@direction = 'previous') or (@direction = 'next')]">
      <div class="galleryNavigation">
        <xsl:if test="$navigation/navlink[@direction = 'previous']">
          <a href="{$navigation/navlink[@direction = 'previous']/@href}" class="navigationLinkPrevious">&lt;-</a>
        </xsl:if>
        <xsl:if test="$navigation/navlink[@direction = 'next']">
          <a href="{$navigation/navlink[@direction = 'next']/@href}" class="navigationLinkNext">-&gt;</a>
        </xsl:if>
      </div>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>