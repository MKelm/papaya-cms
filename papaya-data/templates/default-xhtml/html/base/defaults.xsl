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
  <div id="header" class="outerCorners">
    <div class="topCorners"><div><xsl:comment> header </xsl:comment></div></div>
    <div class="leftBorder">
      <div class="rightBorder">
        <div class="headerBackground">
          <div class="headerContent">
            <xsl:choose>
              <xsl:when test="count(boxes/box[@group = 'header']) &gt; 0">
                <xsl:call-template name="box-group">
                   <xsl:with-param name="boxes" select="boxes/box[@group = 'header']"/>
                </xsl:call-template>
              </xsl:when>
              <xsl:otherwise>
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
                <a href="./"><img src="{$PAGE_THEME_PATH}pics/logo.gif" alt="{$HOMEPAGE_CAPTION}" /></a>
              </xsl:otherwise>
            </xsl:choose>
            <xsl:call-template name="float-fix"/>
          </div>
          <xsl:call-template name="page-translations"/>
          <xsl:call-template name="float-fix"/>
        </div>
      </div>
    </div>
    <div class="bottomBorder"><div><xsl:comment> /header </xsl:comment></div></div>
  </div>
  <hr class="accessibilityElement" />
</xsl:template>

<xsl:template name="page">
  <html lang="{$PAGE_LANGUAGE}">
    <head>
      <xsl:call-template name="html-head" />
    </head>
    <body>
      <xsl:call-template name="accessibility-navigation" />
      <xsl:call-template name="header" />

      <xsl:variable name="hasNavigation" select="not($DISABLE_NAVIGATION_COLUMN) and (count(boxes/box[@group = 'navigation']) &gt; 0)" />
      <xsl:variable name="hasAdditional" select="not($DISABLE_ADDITIONAL_COLUMN) and (count(boxes/box[@group = 'additional']) &gt; 0)" />

      <div id="page" class="outerCorners">
        <div class="topCorners"><div><xsl:comment> page </xsl:comment></div></div>
        <div class="leftBorder">
          <div class="rightBorder">
            <div>
              <xsl:attribute name="class">
                <xsl:choose>
                  <xsl:when test="$hasNavigation and $hasAdditional">threeColumnLayout</xsl:when>
                  <xsl:when test="$hasNavigation">twoColumnLayoutLeft</xsl:when>
                  <xsl:when test="$hasAdditional">twoColumnLayoutRight</xsl:when>
                  <xsl:otherwise>singleColumnLayout</xsl:otherwise>
                </xsl:choose>
              </xsl:attribute>
              <div class="pageBackground">
                <xsl:if test="$hasNavigation">
                  <a name="jump-navigation"><xsl:text> </xsl:text></a>
                  <xsl:call-template name="box-group">
                    <xsl:with-param name="boxes" select="boxes/box[@group = 'navigation']"/>
                    <xsl:with-param name="groupId">pageNavigation</xsl:with-param>
                  </xsl:call-template>
                  <hr class="accessibilityElement" />
                </xsl:if>
                <div id="pageContent">
                  <a name="jump-content"><xsl:text> </xsl:text></a>
                  <xsl:call-template name="box-group">
                    <xsl:with-param name="boxes" select="boxes/box[@group = 'ariadne']"/>
                    <xsl:with-param name="groupId">ariadne</xsl:with-param>
                  </xsl:call-template>
                  <xsl:call-template name="box-group">
                    <xsl:with-param name="boxes" select="boxes/box[@group = 'before-content']"/>
                    <xsl:with-param name="groupId">beforeContent</xsl:with-param>
                  </xsl:call-template>

                  <div id="content">
                    <xsl:call-template name="content-area"/>
                    <xsl:call-template name="float-fix"/>
                  </div>

                  <xsl:call-template name="box-group">
                    <xsl:with-param name="boxes" select="boxes/box[@group = 'after-content']"/>
                    <xsl:with-param name="groupId">afterContent</xsl:with-param>
                  </xsl:call-template>

                  <xsl:call-template name="accessibility-jump-to-top" />
                  <xsl:call-template name="float-fix"/>
                </div>
                <xsl:if test="$hasAdditional">
                  <xsl:call-template name="box-group">
                    <xsl:with-param name="boxes" select="boxes/box[@group = 'additional']"/>
                    <xsl:with-param name="groupId">pageAdditional</xsl:with-param>
                  </xsl:call-template>
                  <xsl:call-template name="accessibility-jump-to-top" />
                </xsl:if>
                <xsl:call-template name="float-fix"/>
              </div>
            </div>
          </div>
          <xsl:call-template name="float-fix"/>
        </div>
        <div class="bottomBorder"><div><xsl:comment> /page </xsl:comment></div></div>
      </div>
      <xsl:call-template name="footer" />
      <xsl:call-template name="float-fix"/>
      <xsl:call-template name="page-scripts-lazy" />
      <xsl:call-template name="papaya-scripts-boxes-lazy" />
    </body>
  </html>
</xsl:template>

<xsl:template name="footer">
  <hr class="accessibilityElement"/>
  <div id="footer" class="outerCorners">
    <div class="topCorners"><div><xsl:comment> footer </xsl:comment></div></div>
    <div class="leftBorder">
      <div class="rightBorder">
        <div class="footerContent">
          <div id="footerNavigation">
            <ul>
              <xsl:if test="count(views/viewmode[@type = 'page' and not(@selected)]) &gt; 0">
                <li>
                  <xsl:call-template name="page-views" />
                </li>
              </xsl:if>
              <xsl:if test="count(boxes/box[@group = 'footer']) &gt; 0">
                <li>
                  <xsl:for-each select="boxes/box[@group = 'footer']">
                    <xsl:value-of select="." disable-output-escaping="yes"/>
                  </xsl:for-each>
                </li>
              </xsl:if>
              <li class="last">
                <a href="#jump-top" class="last" accesskey="1">
                  <xsl:call-template name="language-text">
                    <xsl:with-param name="text">JUMP_TO_TOP</xsl:with-param>
                  </xsl:call-template>
                </a>
              </li>
            </ul>
          </div>
          <xsl:call-template name="copyright" />
          <xsl:call-template name="float-fix"/>
        </div>
      </div>
    </div>
    <div class="bottomBorder"><div><xsl:comment> /footer </xsl:comment></div></div>
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
    <ul class="pageTranslations">
      <xsl:for-each select="translations/translation">
        <li>
          <xsl:if test="@selected">
            <xsl:attribute name="class">selected</xsl:attribute>
          </xsl:if>
          <a href="{@href}" title="{text()}"><xsl:value-of select="@lng_title"/></a>
        </li>
      </xsl:for-each>
    </ul>
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
  <div class="accessibilityElement">
    <a name="jump-top" id="jump-top"></a>
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
      <li><a href="#jump-navigation" accesskey="3">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">JUMP_TARGET_NAVIGTION</xsl:with-param>
        </xsl:call-template>
      </a></li>
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
