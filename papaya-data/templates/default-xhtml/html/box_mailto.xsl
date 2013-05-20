<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  @papaya:modules actionbox_sendpage_mailto, actionbox_sendcomment_mailto
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:template match="mailto">
  <a href="{mailtolink}" class="mailto"><xsl:value-of select="title" /></a>
</xsl:template>

</xsl:stylesheet>
