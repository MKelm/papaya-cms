<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="PAGE_LANGUAGE">en-US</xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_glossary.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_glossary'">
      <xsl:call-template name="module-content-glossary">
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

<xsl:template name="module-content-glossary">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-glossary-navigation">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="dialog">
    <xsl:with-param name="dialog" select="$pageContent/search/dialog" />
  </xsl:call-template>
  <xsl:call-template name="list">
    <xsl:with-param name="items" select="$pageContent/search/chars/char" />
  </xsl:call-template>
  <xsl:choose>
    <xsl:when test="$pageContent/glossary/@showcontent = 'yes'">
      <xsl:for-each select="$pageContent/glossary//glossaryentry">
        <xsl:call-template name="module-content-glossary-entry-details">
          <xsl:with-param name="entry" select="."/>
        </xsl:call-template>
      </xsl:for-each>
    </xsl:when>
    <xsl:when test="$pageContent/glossary/@flatmode = 'no' and $pageContent/glossary/charsection">
      <xsl:for-each select="$pageContent/glossary/charsection">
        <xsl:call-template name="module-content-glossary-entry-group">
          <xsl:with-param name="title" select="@title" />
          <xsl:with-param name="entries" select="glossaryentry" />
        </xsl:call-template>
      </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-glossary-entry-list">
        <xsl:with-param name="entries" select="$pageContent/glossary//glossaryentry" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:call-template name="module-content-glossary-pages">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="module-content-glossary-entry-group">
  <xsl:param name="title"/>
  <xsl:param name="entries"/>
  <div class="glossaryGroup">
    <h2><xsl:value-of select="$title"/></h2>
    <xsl:call-template name="module-content-glossary-entry-list">
      <xsl:with-param name="entries" select="$entries" />
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="module-content-glossary-entry-list">
  <xsl:param name="entries"/>
  <xsl:if test="$entries and count($entries) &gt; 0">
    <ul class="glossaryEntries">
      <xsl:for-each select="$entries">
        <li>
          <xsl:call-template name="module-content-glossary-entry-list-item">
            <xsl:with-param name="entry" select="."/>
          </xsl:call-template>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-glossary-entry-list-item">
  <xsl:param name="entry" />
  <a href="{$entry/@href}"><xsl:value-of select="$entry/@term" /></a>
  <xsl:if test="$entry/@synonyms and $entry/@synonyms != ''">
    <span class="synonyms">(<xsl:value-of select="$entry/@synonyms" />)</span>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-glossary-entry-details">
  <xsl:param name="entry" />
  <div class="glossaryEntryDetails">
    <h2><a href="{$entry/@href}"><xsl:value-of select="$entry/@term" /></a></h2>
    <xsl:call-template name="module-content-glossary-entry-details-info">
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">GLOSSARY_CAPTION_DERIVATION</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
      <xsl:with-param name="value" select="$entry/@derivation"/>
    </xsl:call-template>
    <xsl:call-template name="module-content-glossary-entry-details-info">
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">GLOSSARY_CAPTION_SYNONYMS</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
      <xsl:with-param name="value" select="$entry/@synonyms"/>
    </xsl:call-template>
    <xsl:call-template name="module-content-glossary-entry-details-info">
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">GLOSSARY_CAPTION_ABBREVIATIONS</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
      <xsl:with-param name="value" select="$entry/@abbreviations"/>
    </xsl:call-template>
    <div class="explanation">
      <xsl:apply-templates select="$entry/explanation/node()" />
    </div>
    <xsl:call-template name="module-content-glossary-entry-details-info">
      <xsl:with-param name="caption">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">GLOSSARY_CAPTION_SOURCE</xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
      <xsl:with-param name="value" select="$entry/source"/>
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="module-content-glossary-entry-details-info">
  <xsl:param name="caption"></xsl:param>
  <xsl:param name="value"/>
  <xsl:if test="$value and ($value != '' or $value/node())">
    <div class="info">
      <span class="caption"><xsl:value-of select="$caption" /></span>
      <xsl:text>: </xsl:text>
      <xsl:value-of select="$value" />
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-glossary-navigation">
  <xsl:param name="pageContent"/>
  <xsl:if test="$pageContent/navi/@*[contains(name(), 'href') and . != '']">
    <ul class="glossaryNavigation">
      <xsl:if test="$pageContent/navi/@back_href != ''">
        <li>
          <a href="{$pageContent/navi/@back_href}">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">BACK</xsl:with-param>
            </xsl:call-template>
          </a>
        </li>
      </xsl:if>
      <xsl:if test="$pageContent/navi/@all_href != ''">
        <li>
          <a href="{$pageContent/navi/@all_href}">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">GLOSSARY_LINK_ALL</xsl:with-param>
            </xsl:call-template>
          </a>
        </li>
      </xsl:if>
      <xsl:if test="$pageContent/navi/@abc_href != ''">
        <li>
          <a href="{$pageContent/navi/@abc_href}">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">GLOSSARY_LINK_ABC</xsl:with-param>
            </xsl:call-template>
          </a>
        </li>
      </xsl:if>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-glossary-pages">
  <xsl:param name="pageContent" />
  <xsl:call-template name="paging-links-numbered">
    <xsl:with-param name="start" select="1" />
    <xsl:with-param name="end" select="number($pageContent/navi/page/@total)" />
    <xsl:with-param name="selection" select="number($pageContent/navi/page/@current)" />
    <xsl:with-param name="step" select="$pageContent/navi/page/@step" />
    <xsl:with-param name="href" select="$pageContent/navi/page/@href" />
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>
