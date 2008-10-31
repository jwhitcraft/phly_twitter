<?php

/** Zend_Http_Client */
require_once 'Zend/Http/Client.php';

/** Zend_Uri_Http */
require_once 'Zend/Uri/Http.php';

/** Zend_Json */
require_once 'Zend/Json.php';

/** Zend_Feed */
require_once 'Zend/Feed.php';

/**
 * Phly_Twitter
 *
 * @uses       Zend_Http_Client
 * @package    Phly
 * @category   Twitter
 * @copyright  Copyright (C) 2007 - Present, Jon Whitcraft
 * @author     Jon Whitcraft <jon.zf@mac.com>
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 * @version    $Id: $
 */

class Phly_Twitter_Search extends Zend_Http_Client
{
    /**
     * Return Type
     * @var String
     */
    protected $_responseType = 'json';

    /**
     * Response Format Types
     * @var array
     */
    protected $_responseTypes = array(
        'atom',
        'json'
    );

    /**
     * Uri Compoent
     *
     * @var Zend_Uri_Http
     */
    protected $_uri;

    /**
     * Constructor
     *
     * @param  string $returnType
     * @return void
     */
    public function __construct($responseType = 'json')
    {
        $this->setResponseType($responseType);
        $this->_uri = Zend_Uri_Http::fromString("http://search.twitter.com");

        $this->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8');
    }

    /**
     * set responseType
     *
     * @param string $responseType
     * @throws Phly_Twitter_Exception
     * @return Phly_Twitter_Search
     */
    public function setResponseType($responseType = 'json')
    {
        if(!in_array($responseType, $this->_responseTypes, TRUE)) {
            include_once 'Phly/Twitter/Exception.php';
            throw new Phly_Twitter_Exception('Invalid Response Type');
        }
        $this->_responseType = $responseType;
        return $this;
    }

    /**
     * Retrieve responseType
     *
     * @return string
     */
    public function getResponseType()
    {
        return $this->_responseType;
    }

    /**
     * Get the current twitter trends.  Currnetly only supports json as the return.
     *
     * @return array
     */
    public function trends()
    {
        $this->_uri->setPath('/trends.json');
        $this->setUri($this->_uri);
        $response     = $this->request();

        return Zend_Json::decode($response->getBody());
    }

    public function search($query, array $params = array())
    {

        $this->_uri->setPath('/search.' . $this->_responseType);
        $this->_uri->setQuery(null);

        $_query = array();

        $_query['q'] = $query;

        foreach($params as $key=>$param) {
            switch($key) {
                case 'geocode':
                case 'lang':
                    $_query[$key] = $param;
                    break;
                case 'rpp':
                    $_query[$key] = (intval($param) > 100) ? 100 : intval($param);
                    break;
                case 'since_id':
                case 'page':
                    $_query[$key] = intval($param);
                    break;
                case 'show_user':
                    $_query[$key] = 'true';
            }
        }

        $this->_uri->setQuery($_query);

        $this->setUri($this->_uri);
        $response     = $this->request();

        switch($this->_responseType) {
            case 'json':
                return Zend_Json::decode($response->getBody());
                break;
            case 'atom':
                return Zend_Feed::importString($response->getBody());
                break;
        }

        return ;
    }
}
