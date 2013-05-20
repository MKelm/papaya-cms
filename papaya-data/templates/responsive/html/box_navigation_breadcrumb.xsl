<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:func="http://exslt.org/functions"
  extension-element-prefixes="func"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_sitemap
-->

<xsl:import href="./base/boxes.xsl" />
<xsl:import href="../_lang/functions.xsl" />

<xsl:param name="ACCESSIBILITY_BULLET_NUMBERS" select="false()" />

<xsl:template match="sitemap">
  <xsl:if test="mapitem">
    <h2 class="breadcrumbCaption">
      <xsl:value-of select="func:language-text('PATH_CAPTION')"/>
      <xsl:text>: </xsl:text>
    </h2>
  </xsl:if>
  <xsl:call-template name="navigation"/>
</xsl:template>

</xsl:stylesheet>
