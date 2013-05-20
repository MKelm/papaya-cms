<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/free/domains/connector_domains.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/free/domains/papaya_domains.php');

PapayaTestCase::defineConstantDefaults(
 'PAPAYA_DB_TBL_VIEWMODES',
 'PAPAYA_DB_TABLEPREFIX'
);

class PapayaModuleDomainsConnectorTest extends PapayaTestCase {

  private function getConnectorObjectFixture() {
    return $this->getProxy('connector_domains');
  }


  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * @covers connector_domains::getDomainList
  */
  public function testGetDomainList() {
    $expected = array(
      23 => array(
        'domain_id' => 23,
        'domain_hostname' => 'dev2.papaya.local',
        'domain_protocol' => '0',
        'domain_mode' => '0',
      )
    );

    $domain = $this->getMock('papaya_domains', array('loadDomainList'));
    $domain
      ->expects($this->once())
      ->method('loadDomainList')
      ->will($this->returnValue($expected));

    $connector = $this->getConnectorObjectFixture();
    $connector->setDomainObject($domain);

    $this->assertEquals($expected, $connector->getDomainList());
  }

  /**
  * @covers connector_domains::getDomainPropertyTranslations
  */
  public function testGetDomainPropertyTranslations() {
    $modeDesc = array(
      PAPAYA_DOMAIN_MODE_DEFAULT => 'PAPAYA_DOMAIN_MODE_DEFAULT',
      PAPAYA_DOMAIN_MODE_PAGE => 'PAPAYA_DOMAIN_MODE_PAGE',
      PAPAYA_DOMAIN_MODE_LANG => 'PAPAYA_DOMAIN_MODE_LANG',
      PAPAYA_DOMAIN_MODE_DOMAIN => 'PAPAYA_DOMAIN_MODE_DOMAIN',
      PAPAYA_DOMAIN_MODE_TREE => 'PAPAYA_DOMAIN_MODE_TREE'
    );
    $modeImgs = array(
      PAPAYA_DOMAIN_MODE_DEFAULT => 'items-page',        // page symbol
      PAPAYA_DOMAIN_MODE_PAGE => 'items-alias',       // alias symbol
      PAPAYA_DOMAIN_MODE_LANG => 'items-translation', // globe
      PAPAYA_DOMAIN_MODE_DOMAIN => 'items-link',        // link symbol
      PAPAYA_DOMAIN_MODE_TREE => 'categories-sitemap' // tree symbol
    );
    $protDesc = array(
      1 => 'http://',
      2 => 'https://'
    );
    $expected = array($modeDesc, $modeImgs, $protDesc);
    $connector = $this->getConnectorObjectFixture();

    $this->assertEquals($expected, $connector->getDomainPropertyTranslations());
  }

  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * @covers connector_domains::getDomainObject
  */
  public function testGetDomainObject() {
    $connector = $this->getConnectorObjectFixture();

    $this->assertInstanceOf('papaya_domains', $connector->getDomainObject());
  }

  /**
  * @covers connector_domains::setDomainObject
  */
  public function testSetDomainObject() {
    $connector = $this->getConnectorObjectFixture();
    $connector->setDomainObject($this->getMock('papaya_domains'));

    $this->assertAttributeInstanceOf('papaya_domains', '_domainObject', $connector);
  }

  /**
  * @covers connector_domains::getDomainObject
  */
  public function testGetDomainObjectWithDomainObjectSet() {
    $connector = $this->getConnectorObjectFixture();
    $connector->setDomainObject($this->getMock('papaya_domains'));

    $this->assertInstanceOf('papaya_domains', $connector->getDomainObject());
  }


  /***************************************************************************/
  /** DataProvider                                                           */
  /***************************************************************************/

}
?>