<?php
// Call Phly_TwitterTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    set_include_path(
        dirname(__FILE__) . '/../../library'
        . PATH_SEPARATOR . get_include_path()
    );
    define("PHPUnit_MAIN_METHOD", "Phly_TwitterSearcTest::main");
}

set_include_path(
        dirname(__FILE__) . '/../../library'
        . PATH_SEPARATOR . get_include_path()
    );


require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

/** Phly_Twitter */
require_once 'Phly/Twitter/Search.php';

/** Zend_Http_Client */
require_once 'Zend/Http/Client.php';

/** Zend_Http_Client_Adapter_Test */
require_once 'Zend/Http/Client/Adapter/Test.php';

/**
 * Test Class for Phly_Twitter_Search
 */
class Phly_TwitterSearchTest extends PHPUnit_Framework_TestCase
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Phly_TwitterSearchTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->twitter = new Phly_Twitter_Search();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {}

    public function testSetResponseTypeToJSON()
    {
        $this->twitter->setResponseType('json');
        $this->assertEquals('json', $this->twitter->getResponseType());
    }

    public function testSetResponseTypeToATOM()
    {
        $this->twitter->setResponseType('atom');
        $this->assertEquals('atom', $this->twitter->getResponseType());
    }

    public function testInvalidResponseTypeShouldThrowException()
    {
        try {
            $this->twitter->setResponseType('xml');
            $this->fail('Setting an invalid response type should throw an exception');
        } catch(Exception $e) {
        }
    }

    public function testValidResponseTypeShouldNotThrowException()
    {
        try {
            $this->twitter->setResponseType('atom');
        } catch(Exception $e) {
            $this->fail('Setting a valid response type should not throw an exception');
        }
    }

    public function testSearchTrendsReturnsArray()
    {
        $response = $this->twitter->trends();
        $this->assertType('array', $response);
    }

    public function testJsonSearchContainsWordReturnsArray()
    {
        $this->twitter->setResponseType('json');
        $response = $this->twitter->search('zend');
        $this->assertType('array', $response);

    }

    public function testAtomSearchContainsWordReturnsObject()
    {
        $this->twitter->setResponseType('atom');
        $response = $this->twitter->search('zend');

        $this->assertTrue($response instanceof Zend_Feed_Atom);

    }

    public function testJsonSearchRestrictsLanguageReturnsArray()
    {
        $this->twitter->setResponseType('json');
        $response = $this->twitter->search('zend', array('lang' => 'de'));
        $this->assertType('array', $response);
        $this->assertTrue((isset($response['results'][0]) && $response['results'][0]['iso_language_code'] == "de"));
    }

    public function testAtomSearchRestrictsLanguageReturnsObject()
    {
        $this->twitter->setResponseType('atom');
        /* @var $response Zend_Feed_Atom */
        $response = $this->twitter->search('zend', array('lang' => 'de'));

        $this->assertTrue($response instanceof Zend_Feed_Atom);
        $this->assertTrue((strpos($response->link('self'), 'lang=de') !== false));

    }

    public function testJsonSearchReturnThirtyResultsReturnsArray()
    {
        $this->twitter->setResponseType('json');
        $response = $this->twitter->search('zend', array('rpp' => '30'));
        $this->assertType('array', $response);
        $this->assertTrue((count($response['results']) == 30));
    }

    public function testAtomSearchReturnThirtyResultsReturnsObject()
    {
        $this->twitter->setResponseType('atom');
        /* @var $response Zend_Feed_Atom */
        $response = $this->twitter->search('zend', array('rpp' => '30'));

        $this->assertTrue($response instanceof Zend_Feed_Atom);
        $this->assertTrue(($response->count() == 30));

    }

    public function testAtomSearchShowUserReturnsObject()
    {
        $this->twitter->setResponseType('atom');
        /* @var $response Zend_Feed_Atom */
        $response = $this->twitter->search('zend', array('show_user' => 'true'));

        $this->assertTrue($response instanceof Zend_Feed_Atom);

    }
}

?>