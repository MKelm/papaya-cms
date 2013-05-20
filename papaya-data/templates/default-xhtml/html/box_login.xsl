<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_login, actionbox_login_handle
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:template match="loginbox">
  <xsl:choose>
    <xsl:when test="login">
      <xsl:call-template name="login-box">
        <xsl:with-param name="login" select="login" />
        <xsl:with-param name="loginFormCaption" select="login_text" />
        <xsl:with-param name="loginButtonCaption" select="login_button" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="logout">
      <form class="surferLogoutForm" action="{logout/@action}" method="post">
        <input type="hidden" name="{logout/@name}_logout" value="1"/>
        <xsl:for-each select="logout/element">
          <input type="hidden" name="{../@name}_{@name}" value="{@value}"/>
        </xsl:for-each>
        <div class="message">
          <xsl:value-of select="logout_text"/>
          <xsl:text> </xsl:text>
          <span class="surferName"><xsl:value-of select="logout/@fullname"/></span>
        </div>
        <div class="button">
          <button type="submit"><xsl:value-of select="logout_button"/></button>
        </div>
      </form>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="login-box">
  <xsl:param name="login" />
  <xsl:param name="loginFormCaption" />
  <xsl:param name="loginButtonCaption" />
  <form class="surferLoginForm" action="{$login/@action}" method="post">
    <xsl:if test="$login/element[@name='query_string']">
      <input type="hidden" name="{$login/@name}[query_string]" value="{$login/element[@name='query_string']/@value}"/>
    </xsl:if>
    <xsl:variable name="emailField" select="$login/element[@name='email' or @name='handle']" />
    <xsl:variable name="passwordField" select="$login/element[@name='password']" />
    <div class="formCaption">
      <xsl:value-of select="$loginFormCaption"/>
    </div>
    <div class="fieldEmail">
      <label for="{generate-id($emailField)}">
        <xsl:value-of select="$emailField/@title"/>
      </label>
      <input type="text" id="{generate-id($emailField)}" name="{$login/@name}[{$emailField/@name}]" value="{$emailField/@value}"/>
    </div>
    <div class="fieldPassword">
      <label for="{generate-id($passwordField)}">
        <xsl:value-of select="$passwordField/@title"/>
      </label>
      <input type="password" id="{generate-id($passwordField)}" name="{$login/@name}[password]"/>
    </div>
    <div class="button">
      <button type="submit"><xsl:value-of select="$loginButtonCaption"/></button>
    </div>
    <div class="link">
      <a href="{$login/@chglink}">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">PASSWORD_FORGOTTEN</xsl:with-param>
        </xsl:call-template>
      </a>
    </div>
  </form>
</xsl:template>

</xsl:stylesheet>