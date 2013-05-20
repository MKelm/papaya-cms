<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="../../../_functions/javascript-escape-string.xsl" />

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-scripts-lazy">
  <xsl:call-template name="link-script">
    <xsl:with-param name="file">papaya/gallery.js</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_casestudy'">
      <xsl:call-template name="module-content-casestudy">
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

<xsl:template name="module-content-casestudy">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
    <xsl:with-param name="withText" select="not($pageContent/image)"/>
  </xsl:call-template>
  <xsl:choose>
    <xsl:when test="$pageContent/image">
      <xsl:call-template name="module-content-thumbs-image-detail">
        <xsl:with-param name="image" select="$pageContent/image" />
        <xsl:with-param name="imageTitle" select="$pageContent/imagetitle" />
        <xsl:with-param name="imageComment" select="$pageContent/imagecomment" />
        <xsl:with-param name="navigation" select="$pageContent/navigation" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-casestudy-factbox">
        <xsl:with-param name="factBox" select="$pageContent/factbox"/>
      </xsl:call-template>
      <xsl:if test="$pageContent/thumbnails//thumb">
        <xsl:call-template name="multiple-columns-table">
          <xsl:with-param name="items" select="$pageContent/thumbnails//thumb" />
          <xsl:with-param name="itemType">thumbnail</xsl:with-param>
          <xsl:with-param name="columnCount" select="count($pageContent/thumbnails/line[1]/thumb)"/>
        </xsl:call-template>
        <!-- js data -->
        <script type="text/javascript">
          <xsl:comment>
            var galleryImages = {
            <xsl:for-each select="$pageContent/thumbnails//thumb">
              <xsl:value-of select="generate-id()" />
              <xsl:text>: { url: </xsl:text>
                <xsl:call-template name="javascript-escape-string">
                  <xsl:with-param name="string" select="@for" />
                </xsl:call-template>
                <xsl:text>, &#10; title: </xsl:text>
                <xsl:call-template name="javascript-escape-string">
                  <xsl:with-param name="string" select="a/@title" />
                </xsl:call-template>
              <xsl:text>}</xsl:text>
              <xsl:if test="position() != last()">
                <xsl:text>, &#10;</xsl:text>
              </xsl:if>
            </xsl:for-each>
            };
          //</xsl:comment>
        </script>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-casestudy-factbox">
  <xsl:param name="factBox"/>
  <xsl:if test="$factBox">
    <div class="caseStudyFacts">
      <xsl:if test="$factBox/@title and $factBox/@title != ''">
        <h3><xsl:value-of select="$factBox/@title"/></h3>
      </xsl:if>
      <xsl:text> </xsl:text>
      <xsl:apply-templates select="$factBox/node()" />
    </div>
  </xsl:if>
</xsl:template>

<!-- overload the multiple columns item template to add own item types with different tag structures -->
<xsl:template name="multiple-columns-item">
  <xsl:param name="item" />
  <xsl:param name="itemType">item</xsl:param>
  <xsl:choose>
    <xsl:when test="$itemType = 'thumbnail'">
      <xsl:call-template name="module-content-casestudy-thumbnail">
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

<xsl:template name="module-content-casestudy-thumbnail">
  <xsl:param name="item"/>
  <xsl:param name="itemType">thumbnail</xsl:param>
  <a id="{generate-id($item)}" class="thumbnailLink" href="{$item/a/@href}">
    <img src="{$item/a/img/@src}" style="{$item/a/img/@style}" alt="{$item/a/img/@alt}"/>
  </a>
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
          <xsl:apply-templates select="$imageComment/node()"/>
        </div>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
