<?php
/**
 * @package SU_Http
 * @author g.s.j.hywel
 * @author l.osullivan
 */

/**
 * @see SU_Exception
 */
require_once 'SU/Exception.php';

/**
 * @see SU_Http_Response
 */
require_once 'SU/Http/Response.php';

/**
 * @link http://framework.zend.com/manual/en/zend.http.client.html
 */
require_once 'Zend/Http/Client.php';

/**
 * @package SU_Http
 * @author g.s.j.hywel
 * @author l.osullivan
 */
class SU_Http_Client {

    /**
     * @var Zend_Http_Client
     */
    private $zendHttpClient;

    /**
     * @param string $uri
     */
    public function __construct($uri = null) {
        $this->zendHttpClient = new Zend_Http_Client($uri);
    }

    /**
     * @param string $uri
     * @return SU_Http_Client
     */
    public function setUri($uri) {
        $this->zendHttpClient->setUri($uri);
        return $this;
    }

    /**
     *
     * @throws SU_Exception
     * @return SU_Http_Response
     */
    public function request($method = null) {

        try {
            $zendHttpResponse = $this->zendHttpClient->request($method);
        } catch (Exception $e) {
            throw new SU_Exception($e->getMessage());
        }

        if ($zendHttpResponse->isError()) {
            throw new SU_Exception("{$zendHttpResponse->getStatus()} {$zendHttpResponse->getMessage()}");
        }

        $response = new SU_Http_Response();
        $response->setZendHttpResponse($zendHttpResponse);
        return $response;
    }

    /**
     * Set timeout in seconds
     *
     * @param integer $timeout
     * @return SU_Http_Client
     */
    public function setTimeout($timeout) {
        $this->zendHttpClient->setConfig(array(
            'timeout' => $timeout
        ));
        return $this;
    }

    /**
     * Add Raw Data
     *
     * @param string $data
     * @return SU_Http_Client
     */
    public function setRawData($data) {
        $this->zendHttpClient->setRawData($data);
        return $this;
    }

    /**
     * Set Enc Type
     *
     * @param string $encType
     * @return SU_Http_Client
     */
    public function setEncType($encType) {
        $this->zendHttpClient->setEncType($encType);
        return $this;
    }
}
