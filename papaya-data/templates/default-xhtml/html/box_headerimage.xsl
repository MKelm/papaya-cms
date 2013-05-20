<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_background_image
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:template match="image">
  <img>
    <xsl:copy-of select="@*" />
  </img>
</xsl:template>

</xsl:stylesheet>
