<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  IMPORTANT! DO NOT CHANGE THIS FILE!

  If you need to change one of the templates just define a template with the
  same name in your xsl file. This will override the imported template from
  this file.

  This file contains named templates you might want to override when customizing
  your site.
-->

<!-- basic behaviour and parameters -->
<xsl:import href="./base.xsl" />

<!--
  template definitions
-->


<xsl:template name="page">
  <html lang="{$PAGE_LANGUAGE}">
    <head>
      <title><xsl:call-template name="page-title"/></title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <xsl:call-template name="papaya-styles" />
    </head>
    <body>
      <div id="printView">
        <xsl:call-template name="content-area"/>
      </div>
      <xsl:call-template name="float-fix"/>
    </body>
  </html>
</xsl:template>


</xsl:stylesheet>
