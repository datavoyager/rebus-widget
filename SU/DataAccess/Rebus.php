<?php
/**
 * @package SU_DataAccess
 * @author l.osullivan
 */

/**
 * @see SU_Http_Client
 */
require_once 'SU/Http/Client.php';

/**
 * @see SU_Exception
 */
require_once 'SU/Exception.php';

/**
 * @package SU_DataAccess
 * @author l.osullivan
 */
class SU_DataAccess_Rebus
{


    /**
     * @var SU_Http_Client
     */
    private $httpClient;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        try {
            $config = $ini_array = parse_ini_file(__DIR__ . "/../config.ini");
        } catch (Exception $e) {
            throw new SU_Exception("Error loading Configuration");
        }

        if (!isset($config['api'])) {
            throw new SU_Exception("Error loading API uri");
        }

        $this->baseUri = $config['api'];
        $this->httpClient = new SU_Http_Client();
        $this->httpClient->setTimeout(5);
    }

    /**
     *
     * @param string $queryString A url
     *
     * @return SimpleXMLObject
     * @throws SU_Exception
     *
     * @access private
     */
    public function getXml($queryString)
    {
        $uri = $this->baseUri . "?" . $queryString;

        $response = $this->httpClient
            ->setUri($uri)
            ->request();

        $xmlString = $response->getBody();

        // SimpleXML produces an E_WARNING error message for each error
        // found in the XML data - Suppress with @
        if (! $xml = @simplexml_load_string($xmlString)) {
            throw new SU_Exception("Error parsing XML");
        }
        return $xml;
    }

	/**
     * Builds a query string
     *
     * @param $params A keyed array of query fields and values (Optional)
     *
     * @return string A urlencoded query string
     * @access private
     */
    private function buildQueryString($params) {
		$queryString = "";
        $total = count($params);
        $i = 1;
        foreach ($params as $variable => $value) {
        	$queryString .= urlencode($variable) . "=" . urlencode($value);
        	if ($i < $total) {
        		$queryString .= "&";
        	}
        	$i++;
        }
        return $queryString;
    }

    /**
     * Get Lists By Course Id
     *
     * @param string $courseId A SU Course Identifier
     * @throws SU_Exception
     *
     * @return array An array of list data
     * @access pubic
     */
    public function getListsByCourseId(
        $courseId, $requirePublished = true, $sortByCategoryHeading = false
    ) {

        $lists = array();
        $query = array(
            'service' => "lists_by_course_identifier",
            'course_identifier' => $courseId,
            'query_type' => "full"
        );

        $xml = $this->getXml($this->buildQueryString($query));

        if (!empty($xml->error)) {
            $error = (string) $xml->error;
            throw new SU_Exception("Rebus Error: $error");
        }

        foreach ($xml->lists->list as $rebusList) {

            $listId = !empty($rebusList->list_id)
                ? (string) $rebusList->list_id : null;
            $published = !empty($rebusList->published)
                ? (string) $rebusList->published: "N";
            $isPublish = (strtoupper($published) == "Y")
                ? true : false;
            $requirePublished = ($requirePublished === true)
                ? $isPublish : true;

            if ($listId != null &&  $requirePublished === true) {

                $list = new stdClass();
                $list->list_id = (string) $rebusList->list_id;
                $list->org_unit_id = (string) $rebusList->org_unit_id;
                $list->org_unit_name = (string) $rebusList->org_unit_name;
                $list->list_year = (string) $rebusList->year;
                $list->list_name = (string) $rebusList->list_name;
                $list->published = (string)  $published;
                $list->no_students = (string) $rebusList->no_students;
                $list->creation_date = (string) $rebusList->creation_date;
                $list->last_updated = (string) $rebusList->last_updated;
                $list->course_identifier = (string) $rebusList->course_identifier;
                $list->associated_staff = (string) $rebusList->associated_staff;

                $items = array();
                $rebusItems = !empty($rebusList->items->item)
                    ? $rebusList->items->item : null;

                if ($rebusItems != null) {
                     $generate = $this->processItems($list, $rebusItems, $sortByCategoryHeading);
                     $lists[] = $generate;
                } else {
                    $lists[] = $list;
                }
            }
        }
        return $lists;
    }

    /**
     * Process items
     *
     * @param Array   $list                  A rebus list
     * @param Array   $rebusItems            An array of rebus items
     * @param boolean $sortByCategoryHeading Wether or not to sort items by category
     *
     * @return array $list
     * @access private
     */
    private function processItems($list, $rebusItems, $sortByCategoryHeading) {

        foreach ($rebusItems as $rebusItem) {
            $items[] = $this->buildItems($rebusItem);
        }

        foreach($items as $key => $item) {
            $items[$key]->openUrl = $this->buildOpenUrl($item);
        }

        if ($sortByCategoryHeading === true) {
            $list->categories = $this->sortByCategoryHeading($items);
        } else {
            $list->items = $items;
        }

        return $list;
    }

    /**
     * Recursive build items from simple xml nodes
     *
     * @param object  $rebusItem A rebus item simplexml object
     * @param boolean $rec       Whether or not this is a recursive loop
     *
     * @return array $item
     * @access private
     */
    private function buildItems($rebusItem, $rec = false) {

        $item = new stdClass();
        foreach ($rebusItem as $node => $value) {
            $key = (string) $node;
            if ($value->count() > 0) {
                $item->$key = $this->buildItems($value, true);
            } elseif (!empty($value) && "\n" !== $value) {
                $value = (string) $value;
                if (true === $rec) {
                    $item->{$key}[] = mb_convert_encoding((string) $value, "ISO-8859-1", "UTF-8");
                } else {
                    $item->$key = mb_convert_encoding((string) $value, "ISO-8859-1", "UTF-8");
                }

            }
        }
        return $item;
    }

    /**
     * Sort items vy category headings
     *
     * @param array $items An array of rebus items
     *
     * @return array $generate
     * @access private
     */
    private function sortByCategoryHeading($items) {
        $generate = array();
        $sort = array();
        foreach ($items as $item) {
            $sort[$item->category_heading]['title'] = $item->category_heading;
            $sort[$item->category_heading]['items'][] = $item;
        }

        foreach ($sort as $category) {
            $cat = new stdClass();
            $cat->title = $category['title'];
            $cat->items = $category['items'];
            $generate[] = $cat;
        }
        return $generate;
    }

    /**
     * Build Open Url
     *
     * @param array $item A rebus Item
     *
     * @return string An openUrl Query string
     * @access private
     */
    private function buildOpenUrl($item) {

        $openUrlId = array();
        $openUrlId['url_ver']= "Z39.88-2004";
        $openUrlId['rfr_id'] = "info:sid/ifindreading.swan.ac.uk:rebus";

        $openUrl = array();

        // Standard Fields

        $openUrl['volume'] = (isset($item->volume) && !empty($item->volume))
            ? $item->volume : null;

        $openUrl['issue'] = (isset($item->issue) && !empty($item->issue))
            ? $item->issue : null;

        $openUrl['spage'] = (isset($item->start_page) && !empty($item->start_page))
            ? $item->start_page : null;

        $openUrl['epage'] = (isset($item->end_page) && !empty($item->end_page))
            ? $item->end_page : null;

        // $openUrl['pages']; Not Available

        $openUrl['isbn'] = (isset($item->print_control_no) && !empty($item->print_control_no))
        ? $item->print_control_no : null;

        $openUrl['date'] = (isset($item->year) && !empty($item->year))
            ? $item->year : null;

        $materialType = isset($item->material_type) ?
            $item->material_type : false;

        // Not available
        //$openUrl['artnum'];
        //$openUrl['coden'];
        //$openUrl['chron'];
        //$openUrl['ssn'];
        //$openUrl['quarter'];
        //$openUrl['part'];

        // Not consistent

        //$openUrl['aulast'];
        //$openUrl['aufirst'];
        //$openUrl['auinit'];
        //$openUrl['auinit1'];
        //$openUrl['auinitm'];
        //$openUrl['ausuffix'];
        //$openUrl['au'];
        //$openUrl['aucorp'];

        // Journal, Article, Book
        if ("Article" == $materialType ) {
            $openUrl['genre'] = "article";
            $openUrl['jtitle'] = (isset($item->secondary_title) && !empty($item->secondary_title))
                ? $item->secondary_title : null;
            $openUrl['atitle'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;

            $openUrl['issn']  = (isset($item->print_control_no) && !empty($item->print_control_no))
                ? $item->print_control_no : null;

            $openUrl['eissn']  = (isset($item->elec_control_no) && !empty($item->elec_control_no))
                ? $item->elec_control_no : null;

            //$openUrl['stitle']; Not Available

           $openUrlId['rft_val_fmt'] = "info:ofi/fmt:kev:mtx:journal";

        } elseif ("Journal" == $materialType ) {

            $openUrl['genre'] = "journal";
            $openUrl['jtitle'] = (isset($item->secondary_title) && !empty($item->secondary_title))
                ? $item->secondary_title : null;

            $openUrl['issn']  = (isset($item->print_control_no) && !empty($item->print_control_no))
                ? $item->print_control_no : null;

            $openUrl['eissn']  = (isset($item->elec_control_no) && !empty($item->elec_control_no))
                ? $item->elec_control_no : null;

           $openUrlId['rft_val_fmt'] = "info:ofi/fmt:kev:mtx:journal";

        } elseif ("eBook" == $materialType) {

            $openUrl['genre'] = "book";
            $openUrl['title'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['btitle'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['isbn']  = (isset($item->elec_control_no) && !empty($item->elec_control_no))
                ? $item->elec_control_no : null;
           $openUrlId['rft_val_fmt'] = "info:ofi/fmt:kev:mtx:book";

        } elseif ("Book" == $materialType) {

            $openUrl['genre'] = "book";
            $openUrl['title'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['btitle'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['isbn']  = (isset($item->print_control_no) && !empty($item->print_control_no))
                ? $item->print_control_no : null;
           $openUrl['rft_val_fmt'] = "info:ofi/fmt:kev:mtx:book";

        } elseif ("Book chapter" == $materialType) {

            $openUrl['genre'] = "book";
            $openUrl['title'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['btitle'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['isbn']  = (isset($item->print_control_no) && !empty($item->print_control_no))
                ? $item->print_control_no : null;
            $openUrl['isbn']  = (isset($item->elec_control_no) && !empty($item->elec_control_no))
            ? $item->elec_control_no : $openUrl['isbn'];
           $openUrlId['rft_val_fmt'] = "info:ofi/fmt:kev:mtx:book";

        } else {
            $openUrl['genre'] = "unknown";
            $openUrl['title'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['btitle'] = (isset($item->title) && !empty($item->title))
                ? $item->title : null;
            $openUrl['isbn']  = (isset($item->print_control_no) && !empty($item->print_control_no))
                ? $item->print_control_no : null;

           $openUrl['issn']  = (isset($item->print_control_no) && !empty($item->print_control_no))
               ? $item->print_control_no : null;

           $openUrl['eissn']  = (isset($item->elec_control_no) && !empty($item->elec_control_no))
               ? $item->elec_control_no : null;

           $openUrlId['rft_val_fmt'] = "info:ofi/fmt:kev:mtx:unknown";
        }

        $generateUrl = array();
        foreach ($openUrlId as $key => $element) {
            if (null !== $element) {
                $generateUrl[] = $key . "=" . urlencode($element);
            }
        }
        foreach ($openUrl as $key => $element) {
            if (null !== $element) {
                $generateUrl[] = "rft." . $key . "=" . urlencode($element);
            }
        }
        return implode("&amp;", $generateUrl);
    }

    /**
     * Set Base Uri
     *
     * @param string $baseUri A base uri string
     *
     * @return void
     */
    public function setBaseUri($baseUri) {
        $this->baseUri = $baseUri;
    }
}
