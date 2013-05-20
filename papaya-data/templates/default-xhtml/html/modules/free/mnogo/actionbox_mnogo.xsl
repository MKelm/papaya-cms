<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:template match="searchdialog">

  <div class="mnogo-search-container">
    <xsl:call-template name="dialog">
      <xsl:with-param name="dialog" select="." />
      <xsl:with-param name="submitButton" select="@button" />
    </xsl:call-template>
  </div> 
</xsl:template>

</xsl:stylesheet>