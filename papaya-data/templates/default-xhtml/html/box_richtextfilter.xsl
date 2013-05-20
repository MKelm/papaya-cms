<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_richtextfilter
-->

<xsl:import href="./base/match.xsl" />
<xsl:import href="./base/boxes.xsl" />

<xsl:template match="richtext">
  <xsl:if test="node()">
    <div class="richtextBlock">
      <xsl:apply-templates/>
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
