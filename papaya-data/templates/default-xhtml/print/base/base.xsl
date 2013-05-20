<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:func="http://exslt.org/functions"
  xmlns:str="http://exslt.org/strings"
  xmlns:exsl="http://exslt.org/common"
  extension-element-prefixes="func str exsl"
  exclude-result-prefixes="#default">

<!--
  IMPORTANT! DO NOT CHANGE THIS FILE!

  If you need to change one of the templates just define a template with the
  same name in your xsl file. This will override the imported template from
  this file.

  This file contains
   - global parameters passed by papaya CMS
   - global variables that one can override to change how the template set
     behaves
   - some named templates you likely don't need to override.
-->

<!-- localisation -->
<xsl:import href="../../_lang/language.xsl" />

<!-- match templates -->
<xsl:import href="./match.xsl" />
<!-- dialog (form) layouts -->
<xsl:import href="./dialogs.xsl" />
<!-- column layouts -->
<xsl:import href="./columns.xsl" />
<!-- list layouts -->
<xsl:import href="./lists.xsl" />


<!--
  papaya CMS parameters
-->

<!-- page title (like in navigations) -->
<xsl:param name="PAGE_TITLE" />
<!-- content language (example: en-US) -->
<xsl:param name="PAGE_LANGUAGE" />
<!-- base installation path in browser -->
<xsl:param name="PAGE_WEB_PATH" />
<!-- base url of the papaya installation -->
<xsl:param name="PAGE_BASE_URL" />
<!-- url of this page -->
<xsl:param name="PAGE_URL" />
<!-- theme name -->
<xsl:param name="PAGE_THEME" />
<!-- theme set id if defined -->
<xsl:param name="PAGE_THEME_SET">0</xsl:param>
<!-- theme path in browser -->
<xsl:param name="PAGE_THEME_PATH" />
<!-- theme path in server file system -->
<xsl:param name="PAGE_THEME_PATH_LOCAL" />

<!-- current ouput mode (file extension in url) -->
<xsl:param name="PAGE_OUTPUTMODE_CURRENT" />
<!-- default/main ouput mode -->
<xsl:param name="PAGE_OUTPUTMODE_DEFAULT" />
<!-- public or preview page? -->
<xsl:param name="PAGE_MODE_PUBLIC" />
<!-- website version string if available -->
<xsl:param name="PAGE_WEBSITE_REVISION" />

<!-- papaya cms version string if available -->
<xsl:param name="PAPAYA_VERSION" />
<!-- installation in dev mode? (option in conf.inc.php) -->
<xsl:param name="PAPAYA_DBG_DEVMODE" />

<!-- current local time and offset from UTC -->
<xsl:param name="SYSTEM_TIME" />
<xsl:param name="SYSTEM_TIME_OFFSET" />

<!--
  template set parameters
-->

<!-- use favicon.ico in theme directory -->
<xsl:param name="FAVORITE_ICON" select="true()" />

<!-- IE only, disable the mouseover image toolbar, default: true -->
<xsl:param name="IE_DISABLE_IMAGE_TOOLBAR" select="true()" />
<!-- IE only, disable the smart tag linking, default: true -->
<xsl:param name="IE_DISABLE_SMARTTAGS" select="true()" />
<!-- IE only, optional user agent compatibility definition, default: not used -->
<xsl:param name="USER_AGENT_COMPATIBILITY"></xsl:param>

<!-- define indexing for robots -->
<xsl:param name="PAGE_META_ROBOTS">index,follow</xsl:param>
<!-- define indexing for robots if a box suggests content dupplication-->
<xsl:param name="PAGE_META_ROBOTS_DUPLICATES">noindex,nofollow,nocache</xsl:param>

<!-- add css classes to boxes based on the module class name -->
<xsl:param name="BOX_MODULE_CSSCLASSES" select="false()" />
<!-- load file containing module specific css files name definitions -->
<xsl:param name="BOX_MODULE_FILES" select="false()" />
<!-- do not index box output (puts noindex comments around it) -->
<xsl:param name="BOX_DISABLE_INDEX" select="true()" />

<!-- disable the navigation column, even if the xml contains boxes for it -->
<xsl:param name="DISABLE_NAVIGATION_COLUMN" select="false()" />
<!-- disable the additional content column, even if the xml contains boxes for it -->
<xsl:param name="DISABLE_ADDITIONAL_COLUMN" select="false()" />

<!--
  template definitions
-->

<!-- topic content module -->
<xsl:template name="module-content-topic">
  <xsl:param name="pageContent"/>
  <xsl:param name="withText" select="true()"/>
  <h1>
    <xsl:value-of select="$pageContent/title/text()"/>
    <xsl:if test="$pageContent/subtitle/text() != ''">
      <xsl:text> </xsl:text>
      <span class="subTitle"><xsl:value-of select="$pageContent/subtitle"/></span>
    </xsl:if>
  </h1>
  <xsl:call-template name="papaya-error" />
  <xsl:call-template name="papaya-redirect" />
  <xsl:if test="$withText">
    <div class="contentData">
      <xsl:choose>
        <xsl:when test="$pageContent/image[((@align='left') and (@break='none'))]//img">
          <div class="topicImageLeftBreakNone">
            <xsl:apply-templates select="$pageContent/image//img"/>
          </div>
        </xsl:when>
        <xsl:when test="$pageContent/image[((@align='right') and (@break='none'))]//img">
          <div class="topicImageRightBreakNone">
            <xsl:apply-templates select="$pageContent/image//img"/>
          </div>
        </xsl:when>
        <xsl:when test="$pageContent/image[((@align='left') and (@break='side'))]//img">
          <div class="topicImageLeftBreakSide">
            <xsl:apply-templates select="$pageContent/image//img"/>
          </div>
        </xsl:when>
        <xsl:when test="$pageContent/image[((@align='right') and (@break='side'))]//img">
          <div class="topicImageRightBreakSide">
            <xsl:apply-templates select="$pageContent/image//img"/>
          </div>
        </xsl:when>
        <xsl:when test="$pageContent/image[@align='center']//img">
          <div class="topicImageCenter">
            <xsl:apply-templates select="$pageContent/image//img"/>
          </div>
        </xsl:when>
      </xsl:choose>
      <xsl:apply-templates select="$pageContent/text/*|$pageContent/text/text()" />
      <xsl:call-template name="float-fix" />
    </div>
  </xsl:if>
</xsl:template>

<!-- category content module -->
<xsl:template name="module-content-category">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
  <xsl:if test="count($pageContent/subtopics/subtopic) &gt; 0">
    <xsl:variable name="columnCount">
      <xsl:choose>
        <xsl:when test="$pageContent/columns/text() &gt; 6">6</xsl:when>
        <xsl:when test="$pageContent/columns/text() &gt; 1"><xsl:value-of select="$pageContent/columns"/></xsl:when>
        <xsl:otherwise>1</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:call-template name="multiple-columns">
      <xsl:with-param name="items" select="$pageContent/subtopics/subtopic"/>
      <xsl:with-param name="itemType">subTopic</xsl:with-param>
      <xsl:with-param name="columnCount" select="$columnCount"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<!-- the default content module template - in preview mode it outputs a warning -->
<xsl:template name="module-content-default">
  <xsl:param name="pageContent"/>
  <xsl:if test="not($PAGE_MODE_PUBLIC)">
    <div class="warning" style="text-align: center; color: red; margin: 20px;"><b>WARNING: Using default template</b></div>
  </xsl:if>
  <xsl:apply-templates select="$pageContent/text/*|$pageContent/text/text()" />
</xsl:template>

<!-- xhtml content module template - outputs the text-node content -->
<xsl:template name="module-content-xhtml">
  <xsl:param name="pageContent"/>
  <xsl:apply-templates select="$pageContent/text/*|$pageContent/text/text()" />
</xsl:template>

<!-- page title -->
<xsl:template name="page-title">
  <xsl:choose>
    <xsl:when test="meta/metatags/pagetitle/text() != '' and $PAGE_TITLE != '' and meta/metatags/pagetitle/text() != $PAGE_TITLE">
      <xsl:value-of select="meta/metatags/pagetitle/text()"/> - <xsl:value-of select="$PAGE_TITLE" />
    </xsl:when>
    <xsl:when test="$PAGE_TITLE != ''">
      <xsl:value-of select="$PAGE_TITLE" />
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="meta/metatags/pagetitle/text()"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- meta tags list -->
<xsl:template name="page-metatags">
  <xsl:param name="metaTags" select="meta/metatags"/>
  <xsl:if test="$metaTags/metatag[@type='date'] != ''">
    <meta name="date" content="{$metaTags/metatag[@type='date']}" />
  </xsl:if>
  <xsl:if test="$metaTags/metatag[@type='keywords'] != ''">
    <meta name="keywords" content="{$metaTags/metatag[@type='keywords']}" />
  </xsl:if>
  <xsl:if test="$metaTags/metatag[@type='description'] != ''">
    <meta name="description" content="{$metaTags/metatag[@type='description']}" />
  </xsl:if>
  <xsl:choose>
    <xsl:when test="/page/boxes/box/attributes/attribute[@name = 'noIndex' and @value = 'yes']">
      <meta name="robots" content="{$PAGE_META_ROBOTS_DUPLICATES}" />
    </xsl:when>
    <xsl:when test="$PAGE_META_ROBOTS != ''">
      <meta name="robots" content="{$PAGE_META_ROBOTS}" />
    </xsl:when>
  </xsl:choose>
</xsl:template>

<!--
  content area template- checks for modules and calls the matching template
  you need to redefine that template for own content modules
 -->
<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:if test="$pageContent">
    <xsl:choose>
      <xsl:when test="$pageContent/@module = 'content_categimg'">
        <xsl:call-template name="module-content-category">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/@module = 'content_tagcateg'">
        <xsl:call-template name="module-content-category">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/@module = 'content_imgtopic'">
        <xsl:call-template name="module-content-topic">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/@module = 'content_errorpage'">
        <xsl:call-template name="module-content-topic">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$pageContent/@module = 'content_xhtml'">
        <xsl:call-template name="module-content-xhtml">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="module-content-default">
          <xsl:with-param name="pageContent" select="$pageContent"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
</xsl:template>

<xsl:template name="papaya-error">
  <xsl:param name="error" select="/page/content/topic/papaya-error" />
  <xsl:if test="$error and $error/@status != 0">
    <div class="messageError">
      <xsl:value-of select="$error/@status" />
      <xsl:text> - </xsl:text>
      <xsl:apply-templates select="$error/node()" />
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="papaya-redirect">
  <xsl:param name="redirect" select="/page/content/topic/papaya-redirect" />
  <xsl:if test="$redirect">
    <a href="{$redirect}" class="externalLink"><xsl:value-of select="$redirect" /></a>
  </xsl:if>
</xsl:template>

<xsl:template name="papaya-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="files">
      <file>basic.css</file>
      <file>print.css</file>
    </xsl:with-param>
  </xsl:call-template>
  <xsl:text disable-output-escaping="yes">&lt;!--[if IE 7]&gt;</xsl:text>
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">ie7.css</xsl:with-param>
  </xsl:call-template>
  <xsl:text disable-output-escaping="yes">&lt;![endif]--></xsl:text>
  <xsl:text disable-output-escaping="yes">&lt;!--[if lt IE 7]&gt;</xsl:text>
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">ie6win.css</xsl:with-param>
  </xsl:call-template>
  <xsl:text disable-output-escaping="yes">&lt;![endif]--&gt;</xsl:text>
</xsl:template>

<xsl:template name="link-style">
  <xsl:param name="file" />
  <xsl:param name="files">
    <file><xsl:value-of select="$file"/></file>
  </xsl:param>
  <xsl:param name="media">screen, projection</xsl:param>
  <xsl:param name="useWrapper" select="not($PAPAYA_DBG_DEVMODE)"/>
  <xsl:call-template name="link-resource">
    <xsl:with-param name="files" select="$files"/>
    <xsl:with-param name="type">text/css</xsl:with-param>
    <xsl:with-param name="media" select="$media"/>
    <xsl:with-param name="useWrapper" select="$useWrapper"/>
  </xsl:call-template>
</xsl:template>

<!--  embed resources, css and javascript -->

<xsl:template name="link-resource">
  <xsl:param name="files"/>
  <xsl:param name="type">text/css</xsl:param>
  <xsl:param name="media">screen, projection</xsl:param>
  <xsl:param name="useWrapper" select="false()"/>
  <xsl:choose>
    <xsl:when test="exsl:object-type($files) = 'RTF'">
      <xsl:call-template name="link-resource">
        <xsl:with-param name="files" select="exsl:node-set($files)/*"/>
        <xsl:with-param name="type" select="$type"/>
        <xsl:with-param name="media" select="$media"/>
        <xsl:with-param name="useWrapper" select="$useWrapper"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="exsl:object-type($files) = 'node-set'">
      <xsl:if test="count($files) &gt; 0">
        <xsl:choose>
          <xsl:when test="$useWrapper">
            <xsl:variable name="wrapper">
              <xsl:choose>
                <xsl:when test="$type = 'text/javascript'">js.php</xsl:when>
                <xsl:otherwise>css.php</xsl:otherwise>
              </xsl:choose>
            </xsl:variable>
            <xsl:variable name="href">
              <xsl:value-of select="$PAGE_THEME_PATH"/>
              <xsl:value-of select="$wrapper"/>
              <xsl:text>?files=</xsl:text>
              <xsl:for-each select="$files">
                <xsl:if test="position() &gt; 1">
                  <xsl:text>,</xsl:text>
                </xsl:if>
                <xsl:value-of select="."/>
              </xsl:for-each>
              <xsl:text>&amp;rev=</xsl:text>
              <xsl:value-of select="str:encode-uri($PAGE_WEBSITE_REVISION, true())"/>
              <xsl:if test="number($PAGE_THEME_SET) &gt; 0">
                <xsl:text>&amp;set=</xsl:text>
                <xsl:value-of select="str:encode-uri($PAGE_THEME_SET, true())"/>
              </xsl:if>
            </xsl:variable>
            <xsl:choose>
              <xsl:when test="$type = 'text/css'">
                <link rel="stylesheet" type="text/css" href="{$href}" media="{$media}"/>
              </xsl:when>
              <xsl:otherwise>
                <script type="{$type}" src="{$href}"><xsl:comment><xsl:text> </xsl:text>//</xsl:comment></script>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:when>
          <xsl:otherwise>
            <xsl:for-each select="$files">
              <xsl:variable name="href">
                <xsl:value-of select="$PAGE_THEME_PATH"/>
                <xsl:value-of select="."/>
                <xsl:text>?rev=</xsl:text>
                <xsl:value-of select="str:encode-uri($PAGE_WEBSITE_REVISION, true())"/>
              </xsl:variable>
              <xsl:choose>
                <xsl:when test="$type = 'text/css'">
                  <link rel="stylesheet" type="text/css" href="{$href}" media="{$media}"/>
                </xsl:when>
                <xsl:otherwise>
                  <script type="{$type}" src="{$href}"><xsl:comment><xsl:text> </xsl:text>//</xsl:comment></script>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:for-each>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<!-- a little div to fix floating problems (height of elements) -->
<xsl:template name="float-fix">
  <div class="floatFix"><xsl:text> </xsl:text></div>
</xsl:template>

</xsl:stylesheet>
