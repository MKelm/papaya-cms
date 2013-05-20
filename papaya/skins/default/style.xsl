<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="controls/generics.xsl"/>
<xsl:import href="controls/messages.xsl"/>
<xsl:import href="controls/panel.xsl"/>
<xsl:import href="controls/menus.xsl"/>
<xsl:import href="controls/hierarchy.xsl"/>
<xsl:import href="controls/listview.xsl"/>
<xsl:import href="controls/dialogs.xsl"/>
<xsl:import href="controls/grid.xsl"/>
<xsl:import href="controls/sheet.xsl"/>
<xsl:import href="controls/iconpanel.xsl"/>
<xsl:import href="controls/login.xsl"/>
<xsl:import href="controls/calendar.xsl"/>

<xsl:import href="controls/javascript.xsl"/>

<!-- Seitenparamter / globale Variablen -->
<xsl:param name="PAGE_TITLE" />
<xsl:param name="PAGE_TITLE_ALIGN" select="true()"/>
<xsl:param name="PAGE_ICON" />
<xsl:param name="PAGE_USER" />
<xsl:param name="PAGE_PROJECT">Project</xsl:param>
<xsl:param name="PAPAYA_VERSION"></xsl:param>
<xsl:param name="PAPAYA_MESSAGES_INBOX_NEW" select="0"/>
<xsl:param name="PAPAYA_MESSAGES_INBOX_LINK">msgbox.php?msg:folder_id=0</xsl:param>

<xsl:param name="PAPAYA_UI_THEME">green</xsl:param>
<xsl:param name="PAPAYA_PATH_SKIN">skins/default/</xsl:param>
<xsl:param name="COLUMNWIDTH_LEFT">200px</xsl:param>
<xsl:param name="COLUMNWIDTH_CENTER">100%</xsl:param>
<xsl:param name="COLUMNWIDTH_RIGHT">300px</xsl:param>

<xsl:param name="PAPAYA_UI_LANGUAGE">en-US</xsl:param>
<xsl:param name="PAGE_SELF">index.php</xsl:param>

<xsl:param name="PAGE_MODE"/>
<xsl:param name="PAPAYA_LOGINPAGE" select="false()"/>

<xsl:param name="PAPAYA_USE_JS_WRAPPER" select="true()" />
<xsl:param name="PAPAYA_USE_JS_GZIP" select="true()" />

<xsl:param name="PAPAYA_USE_OVERLIB" select="false()" />
<xsl:param name="PAPAYA_USE_SWFOBJECT" select="true()" />

<xsl:param name="PAPAYA_USE_RICHTEXT" select="true()" />
<xsl:param name="PAPAYA_USE_TINYMCE_GZIP" select="true()" />
<xsl:param name="PAPAYA_RICHTEXT_TEMPLATES_FULL">p,div,h1,h2,h3,h4,h5,h6,blockquote</xsl:param>
<xsl:param name="PAPAYA_RICHTEXT_TEMPLATES_SIMPLE">p,div,h1,h2,h3</xsl:param>
<xsl:param name="PAPAYA_RICHTEXT_CONTENT_CSS"/>
<xsl:param name="PAPAYA_RICHTEXT_LINK_TARGET">_self</xsl:param>
<xsl:param name="PAPAYA_RICHTEXT_BROWSER_SPELLCHECK" select="false()"/>


<xsl:param name="PAPAYA_DBG_DEVMODE" select="false()" />

<xsl:output method="html" encoding="UTF-8" standalone="yes" indent="yes" />

<xsl:template match="/page">
  <xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html></xsl:text>
  <html>
    <head>
      <meta name="robots" content="noindex, nofollow" />
      <title><xsl:value-of select="$PAGE_PROJECT" />: <xsl:value-of select="$PAGE_TITLE" /> - papaya CMS 5</title>
      <link rel="stylesheet" type="text/css" href="{$PAPAYA_PATH_SKIN}css.style.php?rev={$PAPAYA_VERSION}&amp;theme={$PAPAYA_UI_THEME}"/>
      <link rel="stylesheet" type="text/css" href="./script/jquery/css/papaya/jquery-ui-1.8.21.custom.css"/>
      <link rel="SHORTCUT ICON" href="{$PAPAYA_PATH_SKIN}pics/{$PAPAYA_UI_THEME}/favicon.ico" />
      <xsl:call-template name="application-page-scripts" />
    </head>
    <xsl:choose>
      <xsl:when test="$PAGE_MODE = 'frame'"><xsl:call-template name="application-frame" /></xsl:when>
      <xsl:otherwise><xsl:call-template name="application-page" /></xsl:otherwise>
    </xsl:choose>
  </html>
</xsl:template>

<xsl:template name="application-page">
  <body class="page">
    <div class="pageBorder">
      <xsl:call-template name="application-page-navigation"/>
      <xsl:call-template name="application-page-title"/>
      <xsl:call-template name="application-page-menus"/>
      <xsl:call-template name="application-page-main"/>
      <xsl:call-template name="application-page-footer"/>
    </div>
    <xsl:call-template name="jquery-embed" />
    <xsl:call-template name="richtext-embed" />
  </body>
</xsl:template>

<xsl:template name="application-frame">
  <body class="framePage">
    <xsl:call-template name="application-page-menus"/>
    <xsl:call-template name="application-page-main"/>
    <xsl:call-template name="jquery-embed" />
  </body>
</xsl:template>

<xsl:template name="application-page-navigation">
  <xsl:if test="not($PAPAYA_LOGINPAGE)">
    <div id="pageNavigation">
      <a href="#pageMenuBar">Menu</a><xsl:text> * </xsl:text>
      <xsl:if test="leftcol">
        <a href="#pageNavigationColumn">Navigation</a><xsl:text> * </xsl:text>
      </xsl:if>
      <a href="#pageContentArea">Content</a>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="application-page-main">
  <div id="workarea">
    <xsl:choose>
      <xsl:when test="//login">
        <xsl:call-template name="login-dialog" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="leftColumn" select="leftcol/*"/>
        <xsl:variable name="centerColumn" select="centercol/*"/>
        <xsl:variable name="rightColumn" select="rightcol/*"/>
        <table class="columnGrid">
          <tr>
            <xsl:if test="$leftColumn">
              <td class="columnLeft" style="width: {$COLUMNWIDTH_LEFT}" id="pageNavigationColumn">
                <xsl:apply-templates select="leftcol/*"/>
                <xsl:call-template name="float-fix">
                  <xsl:with-param name="width" select="$COLUMNWIDTH_LEFT"/>
                </xsl:call-template>
              </td>
            </xsl:if>
            <xsl:choose>
              <xsl:when test="$rightColumn and (($COLUMNWIDTH_RIGHT = '100%') or ($COLUMNWIDTH_RIGHT = $COLUMNWIDTH_CENTER))">
                <xsl:if test="$centerColumn">
                  <td class="columnCenter" style="width: {$COLUMNWIDTH_CENTER}">
                    <xsl:apply-templates select="$centerColumn[name() != 'toolbar']"/>
                    <xsl:call-template name="float-fix">
                      <xsl:with-param name="width" select="$COLUMNWIDTH_CENTER"/>
                    </xsl:call-template>
                  </td>
                </xsl:if>
                <td class="columnRight" style="width: {$COLUMNWIDTH_RIGHT}" id="pageContentArea">
                  <xsl:if test="count(toolbars/*|$rightColumn[name() = 'toolbar']) &gt; 0">
                    <h2 class="nonGraphicBrowser">
                      <xsl:call-template name="translate-phrase">
                        <xsl:with-param name="phrase">Content Toolbar</xsl:with-param>
                      </xsl:call-template>
                    </h2>
                    <xsl:apply-templates select="toolbars/*"/>
                    <xsl:apply-templates select="$rightColumn[name() = 'toolbar']"/>
                  </xsl:if>
                  <xsl:call-template name="application-messages"/>
                  <xsl:apply-templates select="$rightColumn[name() != 'toolbar']"/>
                </td>
              </xsl:when>
              <xsl:when test="$rightColumn and (toolbars/* or $centerColumn[name() = 'toolbar'] or messages)">
                <td>
                  <table class="columnGrid" cellspacing="0">
                    <tr>
                      <td class="columnToolbar" colspan="2" id="pageContentArea">
                        <xsl:if test="count(toolbars/*|$centerColumn[name() = 'toolbar']) &gt; 0">
                          <h2 class="nonGraphicBrowser">
                            <xsl:call-template name="translate-phrase">
                              <xsl:with-param name="phrase">Content Toolbar</xsl:with-param>
                            </xsl:call-template>
                          </h2>
                          <xsl:apply-templates select="toolbars/*"/>
                          <xsl:apply-templates select="$centerColumn[name() = 'toolbar']"/>
                        </xsl:if>
                        <xsl:call-template name="application-messages"/>
                      </td>
                    </tr>
                    <tr>
                      <td class="columnCenter" style="width: {$COLUMNWIDTH_CENTER}">
                         <xsl:apply-templates select="$centerColumn[name() != 'toolbar']"/>
                         <xsl:call-template name="float-fix">
                           <xsl:with-param name="width" select="$COLUMNWIDTH_CENTER"/>
                         </xsl:call-template>
                      </td>
                      <td class="columnRight" style="width: {$COLUMNWIDTH_RIGHT}">
                        <xsl:apply-templates select="$rightColumn[name() != 'toolbar']"/>
                        <xsl:call-template name="float-fix">
                          <xsl:with-param name="width" select="$COLUMNWIDTH_RIGHT"/>
                        </xsl:call-template>
                      </td>
                    </tr>
                  </table>
                </td>
              </xsl:when>
              <xsl:otherwise>
                <td class="columnCenter" style="width: {$COLUMNWIDTH_CENTER}" id="pageContentArea">
                  <xsl:if test="count(toolbars/*|$centerColumn[name() = 'toolbar']) &gt; 0">
                    <h2 class="nonGraphicBrowser">
                      <xsl:call-template name="translate-phrase">
                        <xsl:with-param name="phrase">Content Toolbar</xsl:with-param>
                      </xsl:call-template>
                    </h2>
                    <xsl:apply-templates select="toolbars/*"/>
                    <xsl:apply-templates select="$centerColumn[name() = 'toolbar']"/>
                  </xsl:if>
                  <xsl:call-template name="application-messages"/>
                  <xsl:apply-templates select="$centerColumn[name() != 'toolbar']"/>
                  <xsl:call-template name="float-fix">
                    <xsl:with-param name="width" select="$COLUMNWIDTH_CENTER"/>
                  </xsl:call-template>
                </td>
                <xsl:if test="$rightColumn and ($COLUMNWIDTH_RIGHT = '100%' or (not($COLUMNWIDTH_CENTER = '100%') and contains($COLUMNWIDTH_CENTER, '%')))">
                  <td class="columnRight" style="width: {$COLUMNWIDTH_RIGHT}">asda
                    <xsl:apply-templates select="$rightColumn[name() != 'toolbar']"/>
                    <xsl:call-template name="float-fix">
                      <xsl:with-param name="width" select="$COLUMNWIDTH_RIGHT"/>
                    </xsl:call-template>
                    <xsl:text> </xsl:text>
                  </td>
                </xsl:if>
              </xsl:otherwise>
            </xsl:choose>
          </tr>
        </table>
      </xsl:otherwise>
    </xsl:choose>
  </div>
</xsl:template>

<xsl:template name="application-page-title">
  <div id="title">
    <div id="titleArtworkLeft">
      <div id="titleArtworkRight">
        <div id="titleArtworkOverlay">
          <xsl:call-template name="application-page-buttons"/>
          <xsl:variable name="glyphsrc">
            <xsl:choose>
              <xsl:when test="starts-with($PAGE_ICON, './')"><xsl:value-of select="$PAGE_ICON"/></xsl:when>
              <xsl:otherwise>pics/icons/22x22/<xsl:value-of select="$PAGE_ICON"/></xsl:otherwise>
            </xsl:choose>
          </xsl:variable>
          <img src="{$PAPAYA_PATH_SKIN}pics/logo.png" class="papayaLogo" alt="">
            <xsl:if test="$PAGE_TITLE_ALIGN and leftcol">
               <xsl:attribute name="style">margin-right: <xsl:value-of select="number(substring-before($COLUMNWIDTH_LEFT, 'px')) - 100"/>px</xsl:attribute>
            </xsl:if>
          </img>
          <xsl:if test="$PAGE_ICON and ($PAGE_ICON != '')">
            <img src="{$glyphsrc}" class="pageLogo" alt="" />
          </xsl:if>
          <h1 id="titleText"><xsl:value-of select="$PAGE_TITLE" /></h1>
        </div>
      </div>
    </div>
  </div>
  <div id="titleMenu">
    <xsl:call-template name="application-page-links-right"/>
    <a href="../" target="_blank">
      <xsl:attribute name="title">
        <xsl:call-template name="translate-phrase">
          <xsl:with-param name="phrase">Website</xsl:with-param>
        </xsl:call-template>: <xsl:value-of select="$PAGE_PROJECT" />
      </xsl:attribute>
      <xsl:value-of select="$PAGE_PROJECT" />
    </a>
    <xsl:text> - </xsl:text>
    <span class="user">
      <xsl:value-of select="$PAGE_USER" />
      <xsl:if test="$PAPAYA_MESSAGES_INBOX_NEW &gt; 0">
        (<a href="{$PAPAYA_MESSAGES_INBOX_LINK}"><xsl:value-of select="$PAPAYA_MESSAGES_INBOX_NEW" /></a>)
      </xsl:if>
    </span>
    <xsl:call-template name="application-page-links"/>
  </div>
</xsl:template>

<xsl:template name="application-page-footer">
  <div id="footer">
    <div id="footerArtworkLeft">
      <div id="footerArtworkRight">
        <div id="footerArtworkOverlay">
          <span class="versionString"><a href="http://www.papaya-cms.com/" target="_blank">papaya CMS <xsl:value-of select="$PAPAYA_VERSION"/></a></span>
        </div>
      </div>
    </div>
  </div>
</xsl:template>

<xsl:template name="application-page-buttons">
  <xsl:if test="not($PAPAYA_LOGINPAGE)">
    <xsl:variable name="captionLogOut">
      <xsl:call-template name="translate-phrase">
        <xsl:with-param name="phrase">Logout</xsl:with-param>
      </xsl:call-template>
    </xsl:variable>
    <xsl:variable name="captionHelp">
      <xsl:call-template name="translate-phrase">
        <xsl:with-param name="phrase">Help</xsl:with-param>
      </xsl:call-template>
    </xsl:variable>
    <div id="titleButtons">
      <a href="help.php" id="papayaTitleButtonHelp" title ="{$captionHelp}"><img src="pics/icons/22x22/categories/help.png" alt="{$captionHelp}" title ="{$captionHelp}" class="glyph22"/></a>
      <a href="end.php?usr[cmd]=logout" id="papayaTitleButtonLogout" title ="{$captionLogOut}"><img src="pics/icons/22x22/actions/log-out.png" alt="{$captionLogOut}" title ="{$captionLogOut}" class="glyph22"/></a>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="application-page-menus">
  <xsl:if test="menus/menu">
    <div id="pageMenuBar">
      <xsl:for-each select="menus/menu[@ident = 'main']">
        <xsl:call-template name="menu-bar">
          <xsl:with-param name="menu" select="."/>
        </xsl:call-template>
      </xsl:for-each>
      <xsl:if test="count(menus/menu[not(@ident) or @ident != 'main']) &gt; 0">
        <h2 class="nonGraphicBrowser">
          <xsl:call-template name="translate-phrase">
            <xsl:with-param name="phrase">Actions</xsl:with-param>
          </xsl:call-template>
        </h2>
        <xsl:for-each select="menus/menu[not(@ident) or @ident != 'main']">
          <xsl:call-template name="menu-bar">
            <xsl:with-param name="menu" select="."/>
          </xsl:call-template>
        </xsl:for-each>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="application-page-links-right">
  <xsl:if test="title-menu/links[@align = 'right']/@title">
    <script type="text/javascript"><xsl:comment>
      <xsl:for-each select="title-menu/links[@align = 'right']/link">
        <xsl:variable name="selected">
          <xsl:choose>
            <xsl:when test="@selected">true</xsl:when>
            <xsl:otherwise>false</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        PapayaRichtextSwitch.add("<xsl:value-of select="@title"/>", "<xsl:value-of select="@href"/>", <xsl:value-of select="$selected"/>);
      </xsl:for-each>
      PapayaRichtextSwitch.output(document.getElementById("titleMenu"), "<xsl:value-of select="title-menu/links[@align = 'right']/@title"/>");
    //</xsl:comment></script>
  </xsl:if>
</xsl:template>

<xsl:template name="application-page-links">
  <xsl:if test="title-menu/links[not(@align)]/@title">
    - <ul class="links">
      <li class="caption"><xsl:value-of select="title-menu/links[not(@align)]/@title"/>: </li>
      <xsl:if test="title-menu/links[not(@align)]/link[@selected]">
        <xsl:variable name="selectedLink" select="title-menu/links[not(@align)]/link[@selected]"/>
        <li class="selected">
          <xsl:if test="$selectedLink/@image">
            <img src="pics/language/{$selectedLink/@image}" alt="" title="{$selectedLink/@title}"/>
          </xsl:if>
          <xsl:value-of select="$selectedLink/@title"/>
        </li>
      </xsl:if>
      <xsl:if test="count(title-menu/links[not(@align)]/link) &gt; 0">
        <xsl:for-each select="title-menu/links[not(@align)]/link[not(@selected)]">
          <li>
            <a href="{@href}" title="{@title}">
              <xsl:choose>
                <xsl:when test="@image">
                  <img src="pics/language/{@image}" alt="{@title}" title="{@title}"/>
                </xsl:when>
                <xsl:otherwise><xsl:value-of select="@title"/></xsl:otherwise>
              </xsl:choose>
            </a>
          </li>
        </xsl:for-each>
      </xsl:if>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="application-messages">
  <xsl:call-template name="messages">
    <xsl:with-param name="messages" select="messages"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="application-page-scripts">
  <script type="text/javascript" src="./script/jquery/js/jquery-1.7.2.min.js"></script>
  <xsl:if test="not($PAPAYA_LOGINPAGE)">
    <script type="text/javascript" src="{$PAPAYA_PATH_SKIN}js.style.php?rev={$PAPAYA_VERSION}"></script>

    <xsl:choose>
      <xsl:when test="$PAPAYA_USE_JS_WRAPPER and not($PAPAYA_DBG_DEVMODE)">
        <xsl:variable name="jsQueryString">
          <xsl:if test="$PAPAYA_USE_JS_GZIP">gzip=true</xsl:if>
          <xsl:if test="$PAPAYA_USE_OVERLIB">&amp;overlib=true</xsl:if>
          <xsl:if test="$PAPAYA_USE_SWFOBJECT">&amp;swfobject=true</xsl:if>
        </xsl:variable>
        <xsl:choose>
          <xsl:when test="$jsQueryString != ''">
            <script type="text/javascript" src="./script/papayascripts.php?{$jsQueryString}&amp;rev={$PAPAYA_VERSION}"></script>
          </xsl:when>
          <xsl:otherwise>
            <script type="text/javascript" src="./script/papayascripts.php?rev={$PAPAYA_VERSION}"></script>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <script type="text/javascript" src="./script/jsonclass.js?rev={$PAPAYA_VERSION}"></script>
        <script type="text/javascript" src="./script/xmlrpc.js?rev={$PAPAYA_VERSION}"></script>
        <script type="text/javascript" src="./script/controls.js?rev={$PAPAYA_VERSION}"></script>
        <xsl:variable name="changedMessage">
          <xsl:call-template name="translate-phrase">
            <xsl:with-param name="phrase">Content changed</xsl:with-param>
          </xsl:call-template>
        </xsl:variable>
        <script type="text/javascript">
          <xsl:comment>
            if ($.papayaDialogManager) {
              $.papayaDialogManager().settings.message = '<xsl:value-of select="$changedMessage"/>';
            }
          //</xsl:comment>
        </script>
        <xsl:if test="$PAPAYA_USE_OVERLIB">
          <script type="text/javascript" src="./script/overlib/overlib_mini.js?rev={$PAPAYA_VERSION}"></script>
        </xsl:if>
        <xsl:if test="$PAPAYA_USE_SWFOBJECT">
          <script type="text/javascript" src="./script/swfobject/swfobject.js?rev={$PAPAYA_VERSION}"></script>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="scripts/script">
      <xsl:for-each select="scripts/script">
        <script type="{@type}">
          <xsl:if test="@src">
            <xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute>
          </xsl:if>
          <xsl:if test="@language">
            <xsl:attribute name="language"><xsl:value-of select="@language"/></xsl:attribute>
          </xsl:if>
          <xsl:if test="text() != ''">
            <xsl:comment>
              <xsl:value-of select="." disable-output-escaping="yes"/>
            //</xsl:comment>
          </xsl:if>
          <xsl:text> </xsl:text>
        </script>
      </xsl:for-each>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="jquery-embed">
  <xsl:if test="$EMBED_JQUERY">
    <script type="text/javascript" src="./script/jquery/js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="./script/jquery/js/timepicker.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaUtilities.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaPopIn.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaPopUp.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogManager.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogHints.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogField.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldColor.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldCounted.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldGeoPosition.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldImage.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldImageResized.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldMediaFile.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldPage.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogFieldSelect.js"></script>
    <script type="text/javascript" src="./script/jquery.papayaDialogCheckboxes.js"></script>
  </xsl:if>
</xsl:template>

<xsl:template name="richtext-embed">
  <xsl:if test="$PAPAYA_USE_RICHTEXT and $EMBED_TINYMCE">
    <xsl:variable name="tinymce">tiny_mce3</xsl:variable>
    <xsl:variable name="language-short">
      <xsl:choose>
        <xsl:when test="substring-before($PAPAYA_UI_LANGUAGE, '-')"><xsl:value-of select="substring-before($PAPAYA_UI_LANGUAGE, '-')"/></xsl:when>
        <xsl:otherwise>en</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="$PAPAYA_USE_TINYMCE_GZIP">
        <script type="text/javascript" src="./script/{$tinymce}/tiny_mce.js.php?gzip=true&amp;rev={$PAPAYA_VERSION}"></script>
        <script type="text/javascript"><xsl:comment>
          <xsl:if test="$PAPAYA_DBG_DEVMODE">
            tinyMCELoading.disk_cache = false;
            tinyMCELoading.suffix = '_src';
          </xsl:if>
          <xsl:if test="$language-short != 'en'">
            tinyMCELoading.languages = '<xsl:value-of select="$language-short"/>';
          </xsl:if>
          tinyMCELoading.revision = '<xsl:value-of select="$PAPAYA_VERSION"/>';
          tinyMCE_GZ.init(tinyMCELoading);
        //</xsl:comment></script>
      </xsl:when>
      <xsl:when test="$PAPAYA_DBG_DEVMODE">
        <script type="text/javascript" src="./script/{$tinymce}/tiny_mce_src.js?rev={$PAPAYA_VERSION}"></script>
        <script type="text/javascript" src="./script/{$tinymce}.js?rev={$PAPAYA_VERSION}"></script>
      </xsl:when>
      <xsl:otherwise>
        <script type="text/javascript" src="./script/{$tinymce}/tiny_mce.js.php?rev={$PAPAYA_VERSION}"></script>
      </xsl:otherwise>
    </xsl:choose>
    <script type="text/javascript"><xsl:comment>
      tinyMCEOptionsSimple.content_css = '<xsl:call-template name="richtext-embed-content-css"/>';
      tinyMCEOptionsFull.content_css = '<xsl:call-template name="richtext-embed-content-css"/>';
      <xsl:if test="$PAPAYA_RICHTEXT_CONTENT_CSS">
        tinyMCEOptionsSimple.theme_advanced_buttons1_add = 'styleselect';
        tinyMCEOptionsFull.theme_advanced_buttons1_add = 'styleselect';
      </xsl:if>
      tinyMCEOptionsSimple.theme_advanced_blockformats = '<xsl:value-of select="$PAPAYA_RICHTEXT_TEMPLATES_SIMPLE"/>';
      tinyMCEOptionsFull.theme_advanced_blockformats = '<xsl:value-of select="$PAPAYA_RICHTEXT_TEMPLATES_FULL"/>';
      <xsl:if test="$language-short != 'en'">
        tinyMCEOptionsSimple.language = '<xsl:value-of select="$language-short"/>';
        tinyMCEOptionsFull.language = '<xsl:value-of select="$language-short"/>';
      </xsl:if>      
      <xsl:if test="$PAPAYA_RICHTEXT_BROWSER_SPELLCHECK">
        tinyMCEOptionsSimple.gecko_spellcheck = true;
        tinyMCEOptionsFull.gecko_spellcheck = true;
      </xsl:if>
      tinyMCEOptionsSimple.papayaParser.linkTarget = '<xsl:value-of select="$PAPAYA_RICHTEXT_LINK_TARGET"/>';
      tinyMCEOptionsFull.papayaParser.linkTarget = '<xsl:value-of select="$PAPAYA_RICHTEXT_LINK_TARGET"/>';
      tinyMCE.init(tinyMCEOptionsSimple);
      tinyMCE.init(tinyMCEOptionsFull);
    //</xsl:comment></script>
  </xsl:if>
</xsl:template>

<xsl:template name="richtext-embed-content-css">
  <xsl:value-of select="$PAPAYA_PATH_SKIN"/>
  <xsl:text>css.richtext.php?rev=</xsl:text>
  <xsl:value-of select="$PAPAYA_VERSION"/>
  <xsl:if test="$PAPAYA_RICHTEXT_CONTENT_CSS">
    <xsl:text>,</xsl:text>
    <xsl:value-of select="$PAPAYA_RICHTEXT_CONTENT_CSS"/>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
