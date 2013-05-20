<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  IMPORTANT! DO NOT CHANGE THIS FILE!

  If you need to change one of the templates just define a template with the
  same name in your xsl file. This will override the imported template from
  this file.

  This file contains named templates you might want to override when customizing
  your site.
-->

<!-- basic behaviour and parameters -->
<xsl:import href="./base.xsl" />

<xsl:param name="PAGE_BODY_CLASS"/>
<xsl:param name="SHOW_BREADCRUMB" select="true()"/>

<!--
  template definitions
-->

<xsl:template name="copyright">
  <div id="copyright">
    <xsl:if test="count(boxes/box[@group = 'copyright']) &gt; 0">
      <xsl:for-each select="boxes/box[@group = 'copyright']">
        <xsl:value-of select="." disable-output-escaping="yes"/>
      </xsl:for-each>
      <xsl:text>, </xsl:text>
    </xsl:if>
    powered by <a href="http://www.papaya-cms.com/">papaya CMS</a>
  </div>
</xsl:template>

<xsl:template name="header">
  <div class="roof clearfix">
    <div class="page clearfix">
      <xsl:call-template name="box-group">
        <xsl:with-param name="boxes" select="boxes/box[@group = 'service-links']"/>
        <xsl:with-param name="groupClass">serviceLinks</xsl:with-param>
      </xsl:call-template>
      <xsl:call-template name="page-translations"/>
    </div>
  </div>
  <hr class="accessibilityElement" />
  <div class="head clearfix">
    <div class="page clearfix">
      <xsl:variable name="HOMEPAGE_CAPTION">
        <xsl:choose>
          <xsl:when test="meta/metatags/pagetitle/text() != ''">
            <xsl:value-of select="meta/metatags/pagetitle/text()"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">JUMP_TARGET_HOME</xsl:with-param>
            </xsl:call-template>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <a href="./" class="logo">
        <xsl:variable 
          name="logoImage" 
          select="boxes/box[@group = 'logo' and @module = 'actionbox_background_image']/attributes/attribute[@name = 'image']/@value"/>
        <xsl:if test="$logoImage and $logoImage != ''">
          <xsl:attribute name="style">
            <xsl:text>background-image: url(</xsl:text>
            <xsl:value-of select="$logoImage"/>
            <xsl:text>);</xsl:text>
          </xsl:attribute>
        </xsl:if>
        <xsl:text> </xsl:text>
      </a>
      <xsl:call-template name="box-group">
        <xsl:with-param name="boxes" select="boxes/box[@group = 'main-navigation']"/>
        <xsl:with-param name="groupId">jump-main-navigation</xsl:with-param>
        <xsl:with-param name="groupClass">mainNavigation</xsl:with-param>
      </xsl:call-template>
    </div>
  </div>
  <hr class="accessibilityElement" />
</xsl:template>

<xsl:template name="page">
  <html lang="{$PAGE_LANGUAGE}">
    <head>
      <xsl:call-template name="html-head" />
    </head>
    
    <xsl:variable 
      name="hasNavigation" 
      select="not($DISABLE_NAVIGATION_COLUMN) and (count(boxes/box[@group = 'detail-navigation']) &gt; 0)" />
    <xsl:variable 
      name="hasAdditional"
      select="not($DISABLE_ADDITIONAL_COLUMN) and (count(boxes/box[@group = 'additional']) &gt; 0)" />
    <body>
      <xsl:attribute name="class">
        <xsl:if test="$PAGE_BODY_CLASS and $PAGE_BODY_CLASS != ''">
          <xsl:value-of select="$PAGE_BODY_CLASS"/>
          <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:choose>
          <xsl:when test="$hasNavigation and $hasAdditional">threeColumnLayout</xsl:when>
          <xsl:when test="$hasNavigation">twoColumnLayoutNavigation</xsl:when>
          <xsl:when test="$hasAdditional">twoColumnLayoutAdditional</xsl:when>
          <xsl:otherwise>singleColumnLayout</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:call-template name="accessibility-navigation" />
      <xsl:call-template name="header" />


      <div class="body">
        <div class="page clearfix">
          <xsl:call-template name="box-group">
            <xsl:with-param name="boxes" select="boxes/box[@group = 'detail-navigation']"/>
            <xsl:with-param name="groupId">jump-detail-navigation</xsl:with-param>
            <xsl:with-param name="groupClass">detailNavigation</xsl:with-param>
            <xsl:with-param name="withSeparatorAfter" select="true()"/>
            <xsl:with-param name="withTopJump" select="true()"/>
          </xsl:call-template>
          <div class="pageContent" id="jump-content">
            <xsl:call-template name="box-group">
              <xsl:with-param name="boxes" select="boxes/box[@group = 'before-content']"/>
              <xsl:with-param name="groupClass">beforeContent clearfix</xsl:with-param>
              <xsl:with-param name="separatorAfter" select="true()"/>
            </xsl:call-template> 
            <xsl:if test="$SHOW_BREADCRUMB">
              <xsl:call-template name="box-group">
                <xsl:with-param name="boxes" select="boxes/box[@group = 'breadcrumb']"/>
                <xsl:with-param name="groupClass">breadcrumb clearfix</xsl:with-param>
              </xsl:call-template>
            </xsl:if>
            <div class="content">
              <xsl:call-template name="content-area"/>
            </div>
            <xsl:call-template name="box-group">
              <xsl:with-param name="boxes" select="boxes/box[@group = 'after-content']"/>
              <xsl:with-param name="groupId">afterContent</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="accessibility-jump-to-top" />
          </div>
          <xsl:call-template name="box-group">
            <xsl:with-param name="boxes" select="boxes/box[@group = 'additional']"/>
            <xsl:with-param name="groupClass">pageAdditional</xsl:with-param>
            <xsl:with-param name="withSeparatorBefore" select="true()"/>
            <xsl:with-param name="withTopJump" select="true()"/>
          </xsl:call-template>
        </div>
      </div>
      <xsl:call-template name="footer" />
      <xsl:call-template name="papaya-scripts-lazy" />
      <xsl:call-template name="page-scripts-lazy" />
    </body>
  </html>
</xsl:template>

<xsl:template name="footer">
  <hr class="accessibilityElement"/>
  <div class="foot">
    <div class="page clearfix">    
      <xsl:call-template name="box-group">
        <xsl:with-param name="boxes" select="boxes/box[@group = 'foot']"/>
        <xsl:with-param name="groupClass">footSections</xsl:with-param>
        <xsl:with-param name="columns" select="3"/>
      </xsl:call-template>
      <xsl:call-template name="box-group">
        <xsl:with-param name="boxes" select="boxes/box[@group = 'copyright']"/>
        <xsl:with-param name="groupClass">copyright</xsl:with-param>
      </xsl:call-template>
    </div>
  </div>
</xsl:template>

<xsl:template name="page-views">
  <xsl:if test="count(views/viewmode[@type = 'page' and not(@selected)]) &gt; 0">
    <ul class="pageViews">
      <xsl:for-each select="views/viewmode[@type = 'page']">
        <xsl:if test="not(@selected)">
          <li>
            <xsl:choose>
              <xsl:when test="@ext = 'pdf'">
                <xsl:call-template name="page-views-link-pdf" />
              </xsl:when>
              <xsl:when test="@ext = 'print'">
                <xsl:call-template name="page-views-link-print" />
              </xsl:when>
              <xsl:otherwise>
                <a href="{@href}"><xsl:value-of select="@ext"/></a>
              </xsl:otherwise>
            </xsl:choose>
          </li>
        </xsl:if>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="page-views-link-pdf">
  <xsl:variable name="title">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">VIEW_CAPTION_PDF</xsl:with-param>
    </xsl:call-template>
  </xsl:variable>
  <a href="{@href}"><xsl:value-of select="$title"/></a>
</xsl:template>

<xsl:template name="page-views-link-print">
  <xsl:variable name="title">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">VIEW_CAPTION_PRINT</xsl:with-param>
    </xsl:call-template>
  </xsl:variable>
  <a href="{@href}"><xsl:value-of select="$title"/></a>
</xsl:template>

<xsl:template name="page-views-relations">
  <xsl:if test="count(views/viewmode[@type = 'feed' and not(@selected)]) &gt; 0">
    <xsl:for-each select="views/viewmode[@type = 'feed']">
      <xsl:if test="not(@selected)">
        <link rel="alternate" type="{@contenttype}" href="{@href}"/>
      </xsl:if>
    </xsl:for-each>
  </xsl:if>
</xsl:template>

<xsl:template name="page-translations">
  <xsl:if test="count(translations/translation) &gt; 1">
    <xsl:comment><xsl:text> noindex </xsl:text></xsl:comment>
    <ul class="pageTranslations navigation">
      <xsl:for-each select="translations/translation">
        <li>
          <xsl:if test="@selected">
            <xsl:attribute name="class">selected</xsl:attribute>
          </xsl:if>
          <a href="{@href}" title="{text()}"><xsl:value-of select="@lng_title"/></a>
        </li>
      </xsl:for-each>
    </ul>
    <xsl:comment><xsl:text> /noindex </xsl:text></xsl:comment>
  </xsl:if>
</xsl:template>

<xsl:template name="page-styles">
  <!-- place holder - overload in page xsl to add own styles -->
</xsl:template>

<xsl:template name="page-scripts">
  <!-- place holder - overload in page xsl to add own scripts to html head -->
</xsl:template>

<xsl:template name="page-scripts-lazy">
  <!-- place holder - overload in page xsl to add own scripts to end of html body -->
</xsl:template>

<xsl:template name="header-area">
  <!-- place holder - overload in page xsl to add stuff to end of html head -->
</xsl:template>

<xsl:template name="html-head">
  <title><xsl:call-template name="page-title"/></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <xsl:if test="$FAVORITE_ICON">
    <link rel="shortcut icon" href="{$PAGE_THEME_PATH}favicon.ico" />
  </xsl:if>
  <xsl:if test="$IE_DISABLE_IMAGE_TOOLBAR">
    <meta http-equiv="imagetoolbar" content="false" />
  </xsl:if>
  <xsl:if test="$IE_DISABLE_SMARTTAGS">
    <meta name="MSSmartTagsPreventParsing" content="true" />
  </xsl:if>
  <xsl:if test="$USER_AGENT_COMPATIBILITY and $USER_AGENT_COMPATIBILITY != ''">
    <meta name="X-UA-Compatible" content="{$USER_AGENT_COMPATIBILITY}" />
  </xsl:if>
  <xsl:call-template name="page-metatags"/>
  <xsl:call-template name="papaya-styles" />
  <xsl:call-template name="page-styles" />
  <xsl:call-template name="papaya-scripts"/>
  <xsl:call-template name="page-scripts" />
  <xsl:call-template name="page-views-relations"/>
  <xsl:call-template name="header-area"/>
  <xsl:for-each select="boxes/box[@group = 'html-head']">
    <xsl:value-of select="." disable-output-escaping="yes"/>
  </xsl:for-each>
</xsl:template>

<xsl:template name="accessibility-navigation">
  <div class="accessibilityElement" id="jump-top">
    <em>
      <xsl:call-template name="language-text">
        <xsl:with-param name="text">JUMP_TO</xsl:with-param>
      </xsl:call-template>
      <xsl:text>: </xsl:text>
    </em>
    <ul>
      <li><a href="#jump-content" accesskey="2">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">JUMP_TARGET_CONTENT</xsl:with-param>
        </xsl:call-template>
      </a></li>
      <xsl:if test="boxes/box[@group = 'main-navigation']">
        <li><a href="#jump-main-navigation" accesskey="3">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">JUMP_TARGET_MAIN_NAVIGATION</xsl:with-param>
          </xsl:call-template>
        </a></li>
      </xsl:if>
      <xsl:if test="boxes/box[@group = 'detail-navigation']">
        <li><a href="#jump-detail-navigation" accesskey="4">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">JUMP_TARGET_DETAIL_NAVIGATION</xsl:with-param>
          </xsl:call-template>
        </a></li>
      </xsl:if>
      <li><a href="./" accesskey="0">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">JUMP_TARGET_HOME</xsl:with-param>
        </xsl:call-template>
      </a></li>
    </ul>
  </div>
  <hr class="accessibilityElement" />
</xsl:template>

<xsl:template name="accessibility-jump-to-top">
  <a href="#jump-top" class="accessibilityElement">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">topJump</xsl:with-param>
    </xsl:call-template>
  </a>
</xsl:template>

</xsl:stylesheet>
