<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules content_imgtopic, content_errorpage, content_categimg, content_tagcateg, content_xhtml
-->

<!-- default templates to use and maybe overload -->
<xsl:import href="./base/defaults.xsl" />

<xsl:output method="xml" encoding="UTF-8" standalone="no" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" indent="yes" omit-xml-declaration="yes" />

<!-- 
  papaya CMS parameters
-->

<!-- page title (like in navigations) -->
<xsl:param name="PAGE_TITLE" />
<!-- content language (example: en-US) -->
<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<!-- base installation path in browser -->
<xsl:param name="PAGE_WEB_PATH" />
<!-- base url of the papaya installation -->
<xsl:param name="PAGE_BASE_URL" />
<!-- url of this page -->
<xsl:param name="PAGE_URL" />
<!-- theme name -->
<xsl:param name="PAGE_THEME" />
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

<!-- define Indexing for robots -->
<xsl:param name="PAGE_META_ROBOTS">index,follow</xsl:param>

<!-- add css classes to boxes based on the module class name -->
<xsl:param name="BOX_MODULE_CSSCLASSES" select="false()" />
<!-- load file containing module specific css/js files name definitions -->
<xsl:param name="BOX_MODULE_FILES" select="document('boxes.xml')" />
<!-- do not index box output (puts noindex comments around it) -->
<xsl:param name="BOX_DISABLE_INDEX" select="true()" />

<!-- disable the navigation column, even if the xml contains boxes for it -->
<xsl:param name="DISABLE_NAVIGATION_COLUMN" select="false()" />
<!-- disable the additional content column, even if the xml contains boxes for it -->
<xsl:param name="DISABLE_ADDITIONAL_COLUMN" select="false()" />

<!-- maximum columns for multiple column outputs of items (like subtopics) -->
<xsl:param name="MULTIPLE_COLUMNS_MAXIMUM" select="3" />

<!-- use international date time formatting, ISO 8601 -->
<xsl:param name="DATETIME_USE_ISO8601" select="false()" />
<!-- char between date and time (ISO 8601 = T, default = &#160;) -->
<xsl:param name="DATETIME_SEPARATOR">&#160;</xsl:param>
<!-- default date time format: short, medium or large -->
<xsl:param name="DATETIME_DEFAULT_FORMAT">short</xsl:param>

<!-- load current language texts -->
<xsl:param name="LANGUAGE_TEXTS_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<!-- load fallback language texts -->
<xsl:param name="LANGUAGE_TEXTS_FALLBACK" select="document('en-US.xml')" />

<!-- 
  template definitions
-->

<!-- call the page template for the root tag -->
<xsl:template match="/page">
  <xsl:call-template name="page" />
</xsl:template>

</xsl:stylesheet>