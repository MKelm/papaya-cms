<?php

require_once('/opt/mink/vendor/autoload.php');
require_once(__DIR__ . '/../../unittests/Framework/PapayaTestCase.php');

class PapayaMinkTestCase extends \PHPUnit_Framework_TestCase {

  /**
   * The mink session object
   *
   * @var \Behat\Mink\Session
   */
  private $_session = NULL;

  /**
   * The path where the taken screenshots will be saved
   *
   * @var string
   */
  private $_screenshotPath = '';

  /**
   * The testcase execution settings including the mink driver settings
   * and the screenshot path parameter
   *
   * @var array|NULL
   */
  private $_settings = NULL;

  /**
   * Is taken a default testcase screenshot in test, to protect overriding
   * at tear down which screenshot is taken there
   * @var bool TRUE for screenshot is already taken in test, FALSE to take a screenshot at tear down
   */
  private $_tookTestcaseScreenshot = FALSE;

  /**
   * Initialize the  testcase, create a mink session and prepare the screenshot path
   */
  protected function setUp() {
    PapayaTestCase::registerPapayaAutoloader();
    $this->createMinkSession();
    $this->prepareScreenshotPath();
  }

  /**
   * If here is an (active) session make a screenshot of the current state and stop the session.
   */
  protected function tearDown() {
    if (isset($this->_session) && $this->_session->isStarted()) {
      if (!$this->_tookTestcaseScreenshot) {
        $this->takeScreenshot(NULL, FALSE);
      }
      $this->_session->stop();
    }
  }

  /**
   * gets the webdriver url from given config xml or from constants
   *
   * @return string URL to the host
   * @throws Exception
   */
  private function getWebdriverHostUrl() {
    $wbdriverHost = 'http://%s:%s/wd/hub';

    $parameters = $this->getExecutionParameters();

    if (empty($parameters['hub-address']) || empty($parameters['hub-port'])) {
      if ((!defined('WD_HUB_URL') || !defined('WD_HUB_PORT')) &&
          file_exists(__DIR__.'/../conf.inc.php')) {
        include(__DIR__.'/../conf.inc.php');
      }
      if (empty($parameters['hub-address']) && defined('WD_HUB_URL')) {
        $parameters['hub-address'] = WD_HUB_URL;
      }
      if (empty($parameters['hub-port']) && defined('WD_HUB_PORT')) {
        $parameters['hub-port'] = WD_HUB_PORT;
      }
    }
    if (empty($parameters['hub-address']) || empty($parameters['hub-port'])) {
      throw new InvalidArgumentException(
        'Missing/Invalid url and port for the location of the hub. Maybe it is not configured?'
      );
    } else {
      $wbdriverHost = sprintf(
        $wbdriverHost,
        $parameters['hub-address'],
        $parameters['hub-port']
      );
    }

    return $wbdriverHost;
  }

  /**
   * sets the screenshotpath taken from the testsuite config xml
   */
  private function prepareScreenshotPath() {
    $parameters = $this->getExecutionParameters();
    if (!empty($parameters['screenshot-path'])) {
      $this->_screenshotPath = $parameters['screenshot-path'];
      if (substr($this->_screenshotPath, -1) != '/') {
        $this->_screenshotPath .= '/';
      }
    }
  }

  /**
   * @param string $value
   * @return boolean
   */
  private function isParameterFlagTrue($value) {
    if (!empty($value) &&
        ($value == 'yes' || $value == 'on' || $value == 'true')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Parses the data from $_SERVER given from config file or user input
   *
   * @return array
   * @throws InvalidArgumentException
   */
  private function getParsedExecutionParametersFromUserInput() {
    $settings = array(
      'browser' => empty($_SERVER['mink-browser']) ? 'firefox' : $_SERVER['mink-browser'],
      'version' => empty($_SERVER['mink-version']) ? '' : $_SERVER['mink-version'],
      'platform' => empty($_SERVER['mink-platform']) ? '' : $_SERVER['mink-platform'],
      'hub-address' => empty($_SERVER['mink-hub-address']) ? '' : $_SERVER['mink-hub-address'],
      'hub-port' => empty($_SERVER['mink-hub-port']) ? '' : $_SERVER['mink-hub-port'],
      'screenshot-path' =>
        empty($_SERVER['mink-screenshot-path']) ? '' : $_SERVER['mink-screenshot-path'],
      'compare-screenshots' =>
      empty($_SERVER['mink-compare-screenshots']) ? FALSE :
        $this->isParameterFlagTrue($_SERVER['mink-compare-screenshots'])
    );

    $mapping = array(
      '--mink-browser' => 'browser',
      '--mink-version' => 'version',
      '--mink-platform' => 'platform',
      '--mink-screenshot-path' => 'screenshot-path',
      '--mink-hub-address' => 'hub-address',
      '--mink-hub-port' => 'hub-port',
      '--mink-compare-screenshots' => 'compare-screenshots'
    );

    foreach ($_SERVER['argv'] as $index => $parameter) {
      if (isset($mapping[$parameter])) {
        if ($mapping[$parameter] == 'compare-screenshots') {
          $settings[$mapping[$parameter]] =
            $this->isParameterFlagTrue($_SERVER['argv'][$index + 1]);
        } else {
          $settings[$mapping[$parameter]] = $_SERVER['argv'][$index + 1];
        }
      }
    }

    if (empty($settings['browser'])) {
      throw new InvalidArgumentException(
        'Missing user input argument for browser specification.'
      );
    } elseif (!preg_match('(^[a-zA-Z ]+$)D', $settings['browser'])) {
      throw new InvalidArgumentException(
        'Invalid user input argument for browser specification.'
      );
    }

    if (!preg_match('(^[0-9\.]*$)D', $settings['version'])) {
      throw new InvalidArgumentException(
        'Invalid user input argument for browser version specification.'
      );
    }

    if (!preg_match('(^[a-zA-Z]*$)D', $settings['platform'])) {
      throw new InvalidArgumentException(
        'Invalid user input argument for platform (operating system) specification.'
      );
    }

    return $settings;
  }

  /**
   * get the execution parameters for browser spezification
   *
   * @return array
   */
  private function getExecutionParameters() {
    if (NULL === $this->_settings) {
      $this->_settings = $this->getParsedExecutionParametersFromUserInput();
    }
    return $this->_settings;
  }

 /**
  * creates the driver for the browser
  *
  * @return \Behat\Mink\Driver\Selenium2Driver
  * @throws \Behat\Mink\Exception\DriverException
  */
  private function getDriver() {
    $parameters = $this->getExecutionParameters();

    $driver = new \Behat\Mink\Driver\Selenium2Driver(
      strtolower($parameters['browser']),
      array(
        'platform' => empty($parameters['platform']) ? NULL : strtoupper($parameters['platform']),
        'seleniumProtocol' => 'WebDriver',
        'browserVersion' => empty($parameters['version']) ? NULL : $parameters['version'],
        'browserName' => strtolower($parameters['browser']),
        'browser' => sprintf('%s %s', $parameters['browser'], $parameters['version']),
        'version' => $parameters['version']
      ),
      $this->getWebdriverHostUrl()
    );
    if ($driver instanceof \Behat\Mink\Driver\Selenium2Driver) {
      return $driver;
    }
    throw new \Behat\Mink\Exception\DriverException(
      'Can not use this browser settings. Maybe unknown combination for the driver?'
    );
  }

  /**
   * creates and starts the session object
   */
  private function createMinkSession() {
    $this->generateSession($this->getDriver());
    $this->startSession();
  }

  /**
   * Generate browser session
   *
   * @param \Behat\Mink\Driver\Selenium2Driver $driver
   */
  private function generateSession($driver) {
    $this->_session = new \Behat\Mink\Session($driver);
  }

  /**
   * get the current session object
   *
   * @return \Behat\Mink\Session
   */
  public function getSession() {
    return $this->_session;
  }

  /**
   * Starts the session
   */
  private function startSession() {
    $this->_session->start();
  }

  /**
   * builds the screenshot path
   *
   * @param array $screenshotPath
   * @return string
   */
  private function buildScreenshotPath(array $screenshotPath) {
    $path = $this->_screenshotPath.implode('/', $screenshotPath);
    $directoryExists = is_dir($path);
    if (!$directoryExists) {
      mkdir($path,0777,TRUE);
    }
    return $path;
  }

  /**
   * builds the screenshot reference path
   *
   * @param array $screenshotPath
   * @return string
   */
  private function buildReferencePath(array $screenshotPath) {
    $path = $this->_screenshotPath.implode('/', $screenshotPath).'/_reference';
    $directoryExists = is_dir($path);
    if (!$directoryExists) {
      mkdir($path,0777,TRUE);
    }
    return $path;
  }

  /**
   * Formats the screenshot path to array from given browsersettings
   *
   * @return array
   */
  private function formatScreenshotPathAsArray() {
    $settings = $this->getExecutionParameters();
    $browser = trim($settings['browser']);

    if (FALSE !== strpos($browser, ' ')) {
      $splittedBrowserName = explode(' ', $browser);
      $browserName = (
        $this->formatFirstCharToUpperCase($splittedBrowserName[0]).
        $this->formatFirstCharToUpperCase($splittedBrowserName[1])
      );
    } else {
      $browserName = $this->formatFirstCharToUpperCase($browser);
    }

    $screenshotPath = array(
      'screenshots',
      $browserName,
      $settings['version'],
      $this->formatFirstCharToUpperCase($settings['platform'])
    );

    return $screenshotPath;
  }

  /**
   * Gets the driver settings for browser and platform specification
   *
   * @return array
   */
  public function getSettings() {
    return $this->_settings;
  }

  /**
   * Takes a screenshot and compares it with the reference screenshot
   *
   * @param string $screenshotName The name of the screenshot
   */
  public function assertScreenshot($screenshotName = NULL) {
    $this->takeScreenshot($screenshotName, TRUE);
  }

  /**
   * Take a screenshot of the page to test
   *
   * @param string $screenshotName
   */
  public function takeScreenshot($screenshotName = NULL, $compare = FALSE) {
    if (empty($this->_screenshotPath)) {
      return;
    }

    if (empty($screenshotName)) {
      $screenshotName = $this->getName();
      $this->_tookTestcaseScreenshot = TRUE;
    }

    if (0 === strpos($screenshotName, 'test')) {
      $screenshotName = lcfirst(substr($screenshotName, 4));
    }

    $screenshotPath = $this->formatScreenshotPathAsArray();

    $screenshotFilePath = $this->buildScreenshotPath($screenshotPath);
    $referenceFilePath = $this->buildReferencePath($screenshotPath);

    $filename = sprintf(
      '%s/%s.png',
      $screenshotFilePath,
      $screenshotName
    );

    $referenceFileName = sprintf(
      '%s/%s.png',
      $referenceFilePath,
      $screenshotName
    );

    $file = $this->_session->getDriver()->getScreenshot();

    if (!file_exists($filename) || md5($file) != md5(file_get_contents($filename))) {
      file_put_contents(
        $filename,
        $file
      );
    }

    if ($compare) {
      $this->compareScreenshotWithReference($file, $referenceFileName);
    }
  }

  /**
   * Compares a taken screenshot with a reference file
   *
   * @param string $newScreenshotFile The current screenshot, NOT a file path
   * @param string $referenceScreenshotFileName The reference screenshot file name, including path
   */
  private function compareScreenshotWithReference($newScreenshotFile, $referenceScreenshotFileName) {
    if (!$this->_settings['compare-screenshots']) {
      return;
    }

    $referenceScreenshotFile = NULL;

    if (file_exists($referenceScreenshotFileName)) {
      $referenceScreenshotFile = file_get_contents($referenceScreenshotFileName);
    }

    $this->assertEquals(md5($referenceScreenshotFile), md5($newScreenshotFile));
  }

  /**
   * Formats the given string to lower but first char to upper case
   *
   * @param string $string
   * @return string
   */
  public function formatFirstCharToUpperCase($string) {
    if (!empty($string)) {
      $string = ucfirst(strtolower($string));
    }
    return $string;
  }

  /**
   * get the page from url
   *
   * @param string $baseUrl
   * @return Behat\Mink\Element\DocumentElement
   */
  public function getVisitPage($pageUrl) {
    $this->_session->visit($pageUrl);
    return $this->getCurrentPage();
  }

  /**
   * Visit page from url
   *
   * @param string $pageUrl
   */
  public function visitPage($pageUrl) {
    $this->_session->visit($pageUrl);
  }

  /**
   * Gets the current page
   *
   * @return Behat\Mink\Element\DocumentElement
   */
  public function getCurrentPage() {
    return $this->_session->getPage();
  }

  /**
   * Waits some time or JS condition comes true
   *
   * @param integer $time
   * @param string $jsCondition
   */
  public function wait($time, $jsCondition = 'false') {
    $this->_session->wait((int)$time, $jsCondition);
  }

  /**
   * checks an element of existence and equal of expected value by xpath
   *
   * @param string $expected
   * @param DocumentElement $page
   * @param string $xpath
   * @param string $message
   */
  public function assertElementExistsAndEqualsByXpath($expected, $page, $xpath, $message = '') {
    $element = $page->find('xpath', $xpath);
    $this->assertNotEmpty($element, 'Element does not exist.');
    $this->assertEquals($expected, $element->getText(), $message);
  }

  /**
   * checks an element of existence and equal of expected value by css
   *
   * @param string $expected
   * @param DocumentElement $page
   * @param string $cssSelector
   * @param string $message
   */
  public function assertElementExistsAndEqualsByCss($expected, $page, $cssSelector, $message = '') {
    $element = $page->find('css', $cssSelector);
    $this->assertNotEmpty($element, 'Element does not exist.');
    $this->assertEquals($expected, $element->getText(), $message);
  }

  /**
   * checks an element for existence
   *
   * @param DocumentElement $page
   * @param string $xpath
   * @param string $message
   */
  public function assertElementExistsByXpath($page, $xpath, $message = '') {
    $element = $page->find('xpath', $xpath);
    $this->assertNotEmpty($element, $message);
  }

  /**
   * checks an element for existence
   *
   * @param DocumentElement $page
   * @param string $cssSelector
   * @param string $message
   */
  public function assertElementExistsByCss($page, $cssSelector, $message = '') {
    $element = $page->find('css', $cssSelector);
    $this->assertNotEmpty($element, $message);
  }


  /**
   * replaces invalid chars for filenames
   *
   * @param string $replacement
   * @param integer $length
   * @return string
   */
  public function replaceInvalidCharsForFilenames($text, $length = 100) {

    return PapayaUtilFile::normalizeName($text, $length, '', '');
  }


  /**
   * switch back to the main window e.g. from popup<br/>
   * <b>window have to be closed before calling this function</b>
   */
  public function switchBackToMainWindow() {
    try {
      $this->wait(2000);  // provoke a NoSuchWindow exception
      $exception = NULL;
      $this->assertNotNull($exception, 'Window not closed');
      $this->getSession()->switchToWindow();
    } catch(WebDriver\Exception\NoSuchWindow $exception) {
      $this->assertInstanceOf('WebDriver\Exception\NoSuchWindow', $exception);
      $this->getSession()->switchToWindow();
    }
  }
}
