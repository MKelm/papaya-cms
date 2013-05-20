<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  @papaya:modules actbox_linkdb
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:template match="links">
  <xsl:if test="link">
    <div class="linkList">
      <xsl:for-each select="link">
        <div class="linkItem">
          <h2><a href="{@url}"><xsl:value-of select="@title" /></a></h2>
          <div class="data">
            <xsl:text> </xsl:text>
            <xsl:copy-of select="description/node()" />
          </div>
        </div>
      </xsl:for-each>
    </div>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
