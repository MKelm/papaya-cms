<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template match="sitemap">

  <xsl:if test="count(mapitem) &gt; 0">
    <div class="catalogNavigationBox">
      <ul>
        <xsl:for-each select="mapitem">
          <li><a href="{@href}"><xsl:value-of select="@title" /></a></li>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template match="related">
  
  <if test="count(path/mapitem) &gt; 0">
    <ul class="relatedCatalog">
      <xsl:for-each select="path">
        <li class="oneRelatedCategorie">
          <ul class="relatedPathList">
            <xsl:for-each select="mapitem">
              <li class="oneRelatedPath">
                <a href="{@href}"><xsl:value-of select="@title" /></a>
              </li>
            </xsl:for-each>
          </ul>
        </li>
      </xsl:for-each>
    </ul>
  </if>
  
</xsl:template>

<xsl:template match="subtopics">
  <xsl:if test="count(link) &gt; 0">
    <ul class="limitedCatalogLinkList">
      <xsl:for-each select="link">
        <li>
          <a href="{@href}">
            <xsl:value-of select="@title" />
          </a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>


<xsl:template match="letters">
  
  <div class="catalogAZListBox">
    <h2><xsl:value-of select="@caption" /></h2>
    <xsl:if test="@description != ''">
      <p><xsl:value-of select="@description" /></p>
    </xsl:if>
  
    <xsl:if test="count(letter) &gt; 0">
      <ul class="catalogAZList">
        <xsl:for-each select="letter">
          <li>
            <a href="{@href}">
              <xsl:value-of select="text()" />
            </a>
          </li>
        </xsl:for-each>
      </ul>
    </xsl:if>
    <xsl:call-template name="float-fix" />
  </div>
  
</xsl:template>

</xsl:stylesheet>