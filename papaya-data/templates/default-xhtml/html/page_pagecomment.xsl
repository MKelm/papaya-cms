<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules content_pagecomment
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./page_main.xsl"/>

<!-- import module specific rules, this overrides the content-area and other default rules -->
<!-- Because content_pagesend belongs to the mail module as content_feedback also does, content_feedback is -->
<!-- included to serve other templates in order to keep the same funtionality and layout. -->
<xsl:import href="./modules/free/mail/content_feedback.xsl"/>

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/free/mail/content_pagecomment.xsl"/>

<!-- template parameters for module mail -->

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>