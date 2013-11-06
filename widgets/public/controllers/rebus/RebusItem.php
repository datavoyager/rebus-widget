<?php

class RebusItem {

    /**
     *
     * @var string $iFindUrl The iFind Record Url
     */
    public $ifindUrl = '//ifind.swan.ac.uk/discover/Record/';

    /**
     *
     * @var string $ifindCoverUrl The iFind Cover Generator Url
     */
    public $ifindCoverUrl = '//ifind.swan.ac.uk/discover/bookcover.php';

    /**
     *
     * @var string $igetItUrl The iGetIt Base Url
     */
    public $igetItUrl = '//igetit.swan.ac.uk/swansea/';

    /**
     * @var stdClass $item The item object
     */
    public $item;

    /**
     *
     * @var string $images The Image directory
     */
    public $images = '//www.swan.ac.uk/widgets/images/rebus/';

    /**
     * @var array $labels A keyed array of labels for translation
     */
    public $labels = array();


    /**
     * Constuctor
     *
     * @param stdClass $category A Rebus Category
     * @param array    $labels   A keyed array of labels for translation
     */
    public function __construct($item, $labels) {
        $this->item = $item;
        $this->labels = $labels;
    }

    /**
     * Translate function
     *
     * @param string $text A text key
     *
     * @return string A translated value if the key exists or the original string
     * if it does not
     */
    public function translate($text) {
            return isset($this->labels[$text]) ? $this->labels[$text] : $text;
    }


    public function _cover() {

        $item = $this->item;
        $view = $this;

        return function() use ($item, $view) {
            $ctrlNo = isset($item->print_control_no) ? $item->print_control_no : '';
            $format = (isset($item->material_type)) ? strtolower($item->material_type) : '';
            if(isset($item->lms_print_id) && !empty($item->lms_print_id)) {
                return $view->getCover($ctrlNo, $item->title, $format, $item->lms_print_id);
            } elseif (isset($item->lms_elec_id) && !empty($item->lms_elec_id)) {
                return $view->getCover($ctrlNo, $item->title, $format, $item->lms_elec_id);
            } else {
                return $view->getCover($ctrlNo, $item->title, $format);
            }
        };
    }

    /**
     * Get the Cover
     *
     * @return function A closure function which will return a cover url
     */
    public function getCover($isn, $title, $format, $id = false) {

        return false !== $id
            ? '<a target="new" href="' .
                $this->ifindUrl . $id .'">' .
                '<img src="' . $this->ifindCoverUrl . '?isn=' . $isn. '&size=small&contenttype=' . $format . '" alt="' . $title . '"/></a>'
            : '<img src="' . $this->ifindCoverUrl . '?isn=' . $isn. '&size=small&contenttype=' . $format . '" alt="' . $title . '"/>';
    }

    /**
     * Get the Cover
     *
     * @return function A closure function which will return a title link
     */
    public function _title() {

        $item = $this->item;
        $view = $this;

        return function() use ($item, $view) {
            if(isset($item->lms_print_id) && !empty($item->lms_print_id)) {
                return $view->getTitle($item->title, $item->lms_print_id);
            } elseif (isset($item->lms_elec_id) && !empty($item->lms_elec_id)) {
                return $view->getTitle($item->title, $item->lms_elec_id);
            } else {
                return $view->getTitle($item->title);
            }
        };
    }

    /**
     *
     * @param string $title The item title
     * @param string $id    An iFind Record Id
     *
     * @return string A html string
     */
    public function getTitle($title, $id = false) {
        return false !== $id
            ? '<a target="new" href="' . $this->ifindUrl . $id .'">' . $title . '</a>'
            : $title;
    }

    /**
     * Get the Cover
     *
     * @return function A closure function which will return an Open Url
     */
    public function _openUrl() {
        $item = $this->item;
        $view = $this;
        return function() use ($item, $view) {
            if(empty($item->lms_elec_id) && empty($item->lms_print_id) && empty($item->url) && !empty($item->openUrl)) {
                return '<a href="' . $view->igetItUrl . '?' . $item->openUrl . '" target="new">' .
                '<img src="' . $view->images . 'iGetit.png" alt="' . $view->translate("label_Check_Availability") . '" /></a>';
            }
        };
    }

    /**
     * Get the Cover
     *
     * @return function A closure function which will return a Safe Url
     */
    public function _safeUrl() {
        $item = $this->item;
        $view = $this;
        return function() use ($item, $view) {
            return isset($item->url)
                ? '<div><p><a class="btn" href="' .htmlspecialchars_decode($item->url) . '" target="new">'
                . '<img src="' . $view->images . 'icon_weblink.png" alt="' . $view->translate("label_View_Document") . '"/>&nbsp;'. $view->translate("label_Full_Text")
                . '</a></div>' : '';
        };
    }


}