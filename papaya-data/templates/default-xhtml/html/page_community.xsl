<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules content_login, content_profile, content_userdata, content_register, content_register_dynamic, content_profile_change_confirmation
-->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./page_main.xsl" />

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/_base/community/content_community.xsl" />

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>
