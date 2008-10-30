<?php
// Call Phly_TwitterTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Phly_TwitterTest::main');
}

set_include_path(
        dirname(__FILE__) . '/../../library'
        . PATH_SEPARATOR . get_include_path()
    );

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';

/** Phly_Twitter */
require_once 'Phly/Twitter.php';

/** Zend_Http_Client */
require_once 'Zend/Http/Client.php';

/** Zend_Http_Client_Adapter_Test */
require_once 'Zend/Http/Client/Adapter/Test.php';

/**
 * Test class for Phly_Twitter.
 */
class Phly_TwitterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Change these to your login credentials for testing
     */
    const TWITTER_USER = 'zftestuser';
    const TWITTER_PASS = 'zftestuser';

    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Phly_TwitterTest');
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
        $this->twitter = new Phly_Twitter(self::TWITTER_USER, self::TWITTER_PASS);

        $adapter = new Zend_Http_Client_Adapter_Test();
        $client = new Zend_Http_Client(null, array(
            'adapter' => $adapter
        ));
        $this->adapter = $adapter;
        Phly_Twitter::setHttpClient($client);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->adapter);
    }

    /**
     * @return void
     */
    public function testConstructorShouldSetUsernameAndPassword()
    {
        $this->assertEquals('zftestuser', $this->twitter->getUsername());
        $this->assertEquals('zftestuser', $this->twitter->getPassword());
    }

    /**
     * @return void
     */
    public function testUsernameAccessorsShouldAllowSettingAndRetrievingUsername()
    {
        $this->twitter->setUsername('foo');
        $this->assertEquals('foo', $this->twitter->getUsername());
    }

    /**
     * @return void
     */
    public function testPasswordAccessorsShouldAllowSettingAndRetrievingPassword()
    {
        $this->twitter->setPassword('foo');
        $this->assertEquals('foo', $this->twitter->getPassword());
    }

    /**
     * @return void
     */
    public function testOverloadingGetShouldReturnObjectInstanceWithValidMethodType()
    {
        try {
            $return = $this->twitter->status;
            $this->assertSame($this->twitter, $return);
        } catch (Exception $e) {
            $this->fail('Property overloading with a valid method type should not throw an exception');
        }
    }

    /**
     * @return void
     */
    public function testOverloadingGetShouldthrowExceptionWithInvalidMethodType()
    {
        try {
            $return = $this->twitter->foo;
            $this->fail('Property overloading with an invalid method type should throw an exception');
        } catch (Exception $e) {
        }
    }

    /**
     * @return void
     */
    public function testOverloadingGetShouldthrowExceptionWithInvalidFunction()
    {
        try {
            $return = $this->twitter->foo();
            $this->fail('Property overloading with an invalid function should throw an exception');
        } catch (Exception $e) {
        }
    }

    /**
     * @return void
     */
    public function testMethodProxyingDoesNotThrowExceptionsWithValidMethods()
    {
        try {
            $this->twitter->status->publicTimeline();
        } catch (Exception $e) {
            $this->fail('Method proxying should not throw an exception with valid methods; exception: ' . $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testMethodProxyingThrowExceptionsWithInvalidMethods()
    {
        try {
            $this->twitter->status->foo();
            $this->fail('Method proxying should throw an exception with invalid methods');
        } catch (Exception $e) {
        }
    }

    public function testVerifiedCredentials()
    {
        $rawHttpResponse = "HTTP/1.1 200 OK\r\n"
                    . "Date: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Server: hi\r\n"
                    . "Last-modified: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Status: 200 OK\r\n"
                    . "Content-type: application/xml; charset=utf-8\r\n"
                    . "Expires: Tue, 31 Mar 1981 05:00:00 GMT\r\n"
                    . "Connection: close\r\n"
                    . "\r\n"
                    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n"
                    . "<authorized>true</authorized>";
        $this->adapter->setResponse($rawHttpResponse);

        $response = $this->twitter->account->verifyCredentials();
        $this->assertTrue($response instanceof Zend_Rest_Client_Result);
        $httpClient    = Phly_Twitter::getHttpClient();
        $httpRequest   = $httpClient->getLastRequest();
        $httpResponse  = $httpClient->getLastResponse();
        $this->assertTrue($httpResponse->isSuccessful(), $httpResponse->getStatus() . ': ' . var_export($httpRequest, 1) . '\n' . $httpResponse->getHeadersAsString());
    }

    public function testPublicTimelineStatusReturnsResults()
    {
        $rawHttpResponse = "HTTP/1.1 200 OK\r\n"
                    . "Date: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Server: hi\r\n"
                    . "Last-modified: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Status: 200 OK\r\n"
                    . "Content-type: application/xml; charset=utf-8\r\n"
                    . "Expires: Tue, 31 Mar 1981 05:00:00 GMT\r\n"
                    . "Connection: close\r\n"
                    . "\r\n"
                    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n"
                    . "<statuses type=\"array\">\r\n"
                    . "    <status>\r\n"
                    . "      <created_at>Fri Oct 24 17:36:27 +0000 2008</created_at>\r\n"
                    . "      <id>973926380</id>\r\n"
                    . "      <text>just bought some pants!</text>\r\n"
                    . "      <source>web</source>\r\n"
                    . "      <truncated>false</truncated>\r\n"
                    . "      <in_reply_to_status_id />\r\n"
                    . "      <in_reply_to_user_id />\r\n"
                    . "      <favorited>false</favorited>\r\n"
                    . "    </status><status>\r\n"
                    . "      <created_at>Fri Oct 24 17:36:26 +0000 2008</created_at>\r\n"
                    . "      <id>973926379</id>\r\n"
                    . "      <text>I fight the Pen Tool. I admit it. Learn the Pen Tool, Love the Pen Tool.  #expressionDesign</text>\r\n"
                    . "      <source>twhirl</source>\r\n"
                    . "      <truncated>false</truncated>\r\n"
                    . "      <in_reply_to_status_id />\r\n"
                    . "      <in_reply_to_user_id />\r\n"
                    . "      <favorited>false</favorited>\r\n"
                    . "    </status>\r\n"
                    . "</statuses>";
        $this->adapter->setResponse($rawHttpResponse);

        $response = $this->twitter->status->publicTimeline();
        $this->assertTrue($response instanceof Zend_Rest_Client_Result);
        $httpClient    = Phly_Twitter::getHttpClient();
        $httpRequest   = $httpClient->getLastRequest();
        $httpResponse  = $httpClient->getLastResponse();
        $this->assertTrue($httpResponse->isSuccessful(), $httpResponse->getStatus() . ': ' . var_export($httpRequest, 1) . '\n' . $httpResponse->getHeadersAsString());
        $this->assertTrue(isset($response->status));

    }

    public function testUsersFeaturedStatusReturnsResults()
    {
        $rawHttpResponse = "HTTP/1.1 200 OK\r\n"
                    . "Date: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Server: hi\r\n"
                    . "Last-modified: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Status: 200 OK\r\n"
                    . "Content-type: application/xml; charset=utf-8\r\n"
                    . "Expires: Tue, 31 Mar 1981 05:00:00 GMT\r\n"
                    . "Connection: close\r\n"
                    . "\r\n"
                    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n"
                    ."<users type=\"array\">\r\n"
                    ."<user>\r\n"
                    ."  <id>16539957</id>\r\n"
                    ."  <name>NatHistoryWhale</name>\r\n"
                    ."  <screen_name>NatHistoryWhale</screen_name>\r\n"
                    ."  <location>New York, NY</location>\r\n"
                    ."  <description>I am the whale on the ceiling of the Natural History Museum in New York City.</description>\r\n"
                    ."  <profile_image_url>http://s3.amazonaws.com/twitter_production/profile_images/61049789/whale_normal.jpg</profile_image_url>\r\n"
                    ."  <url>http://www.amnh.org/exhibitions/permanent/ocean/03_oceanlife/f1_bluewhale.php</url>\r\n"
                    ."  <protected>false</protected>\r\n"
                    ."  <followers_count>270</followers_count>\r\n"
                    ."  <status>\r\n"
                    ."    <created_at>Wed Oct 22 19:30:27 +0000 2008</created_at>\r\n"
                    ."    <id>970959211</id>\r\n"
                    ."    <text>No we don't @not_vonnegut but that's not to say that we couldn't.</text>\r\n"
                    ."    <source>web</source>\r\n"
                    ."    <truncated>false</truncated>\r\n"
                    ."    <in_reply_to_status_id></in_reply_to_status_id>\r\n"
                    ."    <in_reply_to_user_id></in_reply_to_user_id>\r\n"
                    ."    <favorited>false</favorited>\r\n"
                    ."  </status>\r\n"
                    ."</user>\r\n"
                    ."<user>\r\n"
                    ."  <id>1468401</id>\r\n"
                    ."  <name>Sockamillion</name>\r\n"
                    ."  <screen_name>sockington</screen_name>\r\n"
                    ."  <location>Waltham, MA</location>\r\n"
                    ."  <description>I am Jason Scott's Cat.</description>\r\n"
                    ."  <profile_image_url>http://s3.amazonaws.com/twitter_production/profile_images/29315072/IMG_3738_normal.jpg</profile_image_url>\r\n"
                    ."  <url>http://album.cow.net/index.cgi?d=2005.11.30.CATS</url>\r\n"
                    ."  <protected>false</protected>\r\n"
                    ."  <followers_count>2816</followers_count>\r\n"
                    ."  <status>\r\n"
                    ."    <created_at>Fri Oct 24 19:29:30 +0000 2008</created_at>\r\n"
                    ."    <id>974081794</id>\r\n"
                    ."    <text>I AGAIN INVITE SOCKS ARMY TO SEND GIFTS I will pose with them SOCKS ARMY c/o Jason Scott / 738 Main Street #383 / Waltham, MA 02451</text>\r\n"
                    ."    <source>web</source>\r\n"
                    ."    <truncated>false</truncated>\r\n"
                    ."    <in_reply_to_status_id></in_reply_to_status_id>\r\n"
                    ."    <in_reply_to_user_id></in_reply_to_user_id>\r\n"
                    ."    <favorited>false</favorited>\r\n"
                    ."  </status>\r\n"
                    ."</user>\r\n"
                    ."</users>";
        $this->adapter->setResponse($rawHttpResponse);

        $response = $this->twitter->user->featured();
        $this->assertTrue($response instanceof Zend_Rest_Client_Result);

        $httpClient    = Phly_Twitter::getHttpClient();
        $httpRequest   = $httpClient->getLastRequest();
        $httpResponse  = $httpClient->getLastResponse();
        $this->assertTrue($httpResponse->isSuccessful(), $httpResponse->getStatus() . ': ' . var_export($httpRequest, 1) . '\n' . $httpResponse->getHeadersAsString());
        $this->assertTrue(isset($response->status));
    }

    public function testRateLimitStatusReturnsResults()
    {
        $rawHttpResponse = "HTTP/1.1 200 OK\r\n"
                    . "Date: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Server: hi\r\n"
                    . "Last-modified: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Status: 200 OK\r\n"
                    . "Content-type: application/xml; charset=utf-8\r\n"
                    . "Expires: Tue, 31 Mar 1981 05:00:00 GMT\r\n"
                    . "Connection: close\r\n"
                    . "\r\n"
                    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n"
                    . "<hash>\r\n"
                    . "  <remaining-hits type=\"integer\">99</remaining-hits>\r\n"
                    . "  <hourly-limit type=\"integer\">100</hourly-limit>\r\n"
                    . "  <reset-time-in-seconds type=\"integer\">1224897090</reset-time-in-seconds>\r\n"
                    . "  <reset-time type=\"datetime\">2008-10-25T01:11:30+00:00</reset-time>\r\n"
                    . "</hash>\r\n";
        $this->adapter->setResponse($rawHttpResponse);

        /* @var $response Zend_Rest_Client_Result */
        $response = $this->twitter->account->rateLimitStatus();
        $this->assertTrue($response instanceof Zend_Rest_Client_Result);

        $httpClient    = Phly_Twitter::getHttpClient();
        $httpRequest   = $httpClient->getLastRequest();
        $httpResponse  = $httpClient->getLastResponse();
        $this->assertTrue($httpResponse->isSuccessful(), $httpResponse->getStatus() . ': ' . var_export($httpRequest, 1) . '\n' . $httpResponse->getHeadersAsString());

        $remaining_hits = $response->toValue($response->{'remaining-hits'});

        $this->assertType('numeric', $remaining_hits);
        $this->assertGreaterThan(0, $remaining_hits);
    }

    public function testAccountEndSession()
    {
        $response = $this->twitter->account->endSession();
        $this->assertTrue($response);
    }

    public function testFriendshipCreate()
    {
        $rawHttpResponse = "HTTP/1.1 200 OK\r\n"
                    . "Date: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Server: hi\r\n"
                    . "Last-modified: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Status: 200 OK\r\n"
                    . "Content-type: application/xml; charset=utf-8\r\n"
                    . "Expires: Tue, 31 Mar 1981 05:00:00 GMT\r\n"
                    . "Connection: close\r\n"
                    . "\r\n"
                    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n"
                    ."<user>\r\n"
                    ."  <id>16935247</id>\r\n"
                    ."  <name>zftestuser1</name>\r\n"
                    ."  <screen_name>zftestuser1</screen_name>\r\n"
                    ."  <location></location>\r\n"
                    ."  <description></description>\r\n"
                    ."  <profile_image_url>http://static.twitter.com/images/default_profile_normal.png</profile_image_url>\r\n"
                    ."  <url></url>\r\n"
                    ."  <protected>false</protected>\r\n"
                    ."  <followers_count>1</followers_count>\r\n"
                    ."  <status>\r\n"
                    ."    <created_at>Thu Oct 23 20:48:33 +0000 2008</created_at>\r\n"
                    ."    <id>972616227</id>\r\n"
                    ."    <text>Oh Boy</text>\r\n"
                    ."    <source>web</source>\r\n"
                    ."    <truncated>false</truncated>\r\n"
                    ."    <in_reply_to_status_id></in_reply_to_status_id>\r\n"
                    ."    <in_reply_to_user_id></in_reply_to_user_id>\r\n"
                    ."    <favorited>false</favorited>\r\n"
                    ."  </status>\r\n"
                    ."</user>\r\n";
        $this->adapter->setResponse($rawHttpResponse);

        $response = $this->twitter->friendship->create('zftestuser1');
        $this->assertTrue($response instanceof Zend_Rest_Client_Result);
        $this->assertEquals('zftestuser1', $response->toValue($response->screen_name));

        $rawHttpResponse = "HTTP/1.1 200 OK\r\n"
                    . "Date: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Server: hi\r\n"
                    . "Last-modified: Fri, 24 Oct 2008 17:24:52 GMT\r\n"
                    . "Status: 200 OK\r\n"
                    . "Content-type: application/xml; charset=utf-8\r\n"
                    . "Expires: Tue, 31 Mar 1981 05:00:00 GMT\r\n"
                    . "Connection: close\r\n"
                    . "\r\n"
                    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n"
                    . "<hash>\r\n"
                    . "  <request>/friendships/create/zftestuser1.xml</request>\r\n"
                    . "  <error>Could not follow user: zftestuser1 is already on your list.</error>\r\n"
                    . "</hash>";
        $this->adapter->setResponse($rawHttpResponse);

        $response = $this->twitter->friendship->create('zftestuser1');
        $this->assertTrue($response instanceof Zend_Rest_Client_Result);
        $this->assertTrue(isset($response->error));
        $this->assertEquals(sprintf("Could not follow user: %s is already on your list.", 'zftestuser1'), $response->toValue($response->error));
    }
}

// Call Phly_TwitterTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Phly_TwitterTest::main') {
    Phly_TwitterTest::main();
}

