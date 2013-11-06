<?php
/**
 * @package SU_Http
 * @author g.s.j.hywel
 */

/**
 * @package SU_Http
 * @author g.s.j.hywel
 */
class SU_Http_Link {

    private $source;
    private $target;

    public function __construct($source, $target) {
        $this->source = $source;
        $this->target = $target;
    }

    public function getAbsoluteTarget() {

        if (strpos($this->target, 'http') === 0) {
            return $this->target;
        }

        if (strpos($this->target, '/') === 0) {
            return $this->prependSchemeHostAndPort();
        }

        $absoluteUri = $this->removeDoubleDots($this->dirname($this->source) . $this->target);

        return $absoluteUri;
    }

    private function prependSchemeHostAndPort() {
            $components = parse_url($this->source);
            if (array_key_exists('port', $components)) {
                return sprintf("%s://%s:%d%s", $components['scheme'], $components['host'], $components['port'], $this->target);
            }
            return sprintf("%s://%s%s", $components['scheme'], $components['host'], $this->target);
    }

    private function dirname($uri) {
        return preg_replace('%[^/]+$%', '', $uri);
    }

    private function removeDoubleDots($uri) {
        while (preg_match('%/\.\./%', $uri)) {
            $uri = preg_replace('%[^/\.]+/\.\./%', '', $uri, 1);
        }
        return $uri;
    }

}
