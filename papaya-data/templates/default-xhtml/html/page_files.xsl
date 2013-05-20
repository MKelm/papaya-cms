<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules content_download, content_upload
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./page_main.xsl"/>

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/free/thumbs/content_files.xsl"/>

<!--
  Parameters
-->

<!-- show download list or teaser mode -->
<xsl:param name="FILES_SHOW_LIST" select="true()"/>
<!-- show a detail link (or details if disabled)-->
<xsl:param name="FILES_SHOW_LINK_DETAIL" select="false()"/>
<!-- show a download link on overviews/lists -->
<xsl:param name="FILES_SHOW_LINK_DOWNLOAD" select="true()"/>
<!--
  byte formattigg mode:
    decimal divides by 1000 and uses decimal units kilo, mega, ...
    binary by 1024 and uses binary units kibi, mibi, ...
-->
<xsl:param name="FILES_BYTE_FORMAT">binary</xsl:param>
<!-- digit length for byte output (without decimal seperator and unit) -->
<xsl:param name="FILES_BYTE_DIGITS" select="3"/>


<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>
