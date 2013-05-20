<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_sitemap
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:param name="ACCESSIBILITY_BULLET_NUMBERS" select="false()" />

<xsl:template match="sitemap">
  <xsl:call-template name="navigation"/>
</xsl:template>

</xsl:stylesheet>
