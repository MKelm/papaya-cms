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

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./page_main.xsl"/>

<!-- 
  template definitions
-->
<xsl:param name="SHOW_BREADCRUMB" select="false()"/>

<!-- call the page template for the root tag -->
<xsl:template match="/page">
  <xsl:call-template name="page" />
</xsl:template>

</xsl:stylesheet>