<?php
/**
 * @package SU_Http
 * @author g.s.j.hywel
 * @author l.osullivan
 */

/**
 * @package SU_Http
 * @author g.s.j.hywel
 * @author l.osullivan
 */
class SU_Http_Response
{
    /**
     * @var Zend_Http_Response
     */
    private $zendHttpResponse;

    /**
     * @param Zend_Http_Response $zendHttpResponse
     * @deprecated
     */
    public function setZendHttpResponse(Zend_Http_Response $zendHttpResponse) {
        $this->zendHttpResponse = $zendHttpResponse;
    }

    /**
     * @return integer
     */
    public function getStatus() {
        return $this->zendHttpResponse->getStatus();
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->zendHttpResponse->getBody();
    }

    public function getContentType()
    {
        return $this->zendHttpResponse->getHeader('Content-type');
    }
}
