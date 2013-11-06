<?php

require_once realpath(__DIR__ . '/../../..') . '/config/config.php';
require PATH_TO_HELPERS . '/handle-error.php';
require_once('RebusCategory.php');

class RebusTemplate {

    /**
     *
     * @var array $lists An Array of Rebus List Categories and Items
     */
    public $lists = array();

    /**
     * @var string locale The locale
     */
    public $locale = "en-gb";

    /**
     *
     * @var array $locales An array of valid locales
     */
    public $locales = array("en-gb", "cy");

    /**
     *
     * @var $labels array A keyed array of label values
     */
    public $labels = array(
        'en-gb' => array (
            'label_Admin' => "Create / Edit List",
            'label_by' => "by",
            'label_Check_Availability' => "Check Availability",
            'label_in' => "in",
            'label_Issue' => "Issue",
            'label_Feedback' => "Feedback",
            'label_Filters' => "Filters",
            'label_Full_Text' => "Full Text",
            'label_module_id' => "Module ID",
            'label_Note' => "Note",
            'label_Pages' => "Pages",
            'label_Refresh' => "Refresh",
            'label_Results' => "Results",
            'label_Remove_Filters' => "Remove Filters",
            'label_RSS' => "RSS",
            'label_RSS_Feed' => "RSS Feed",
            'label_Tag' => "Tag",
            'label_View_Document' => "View Document",
            'label_Volume' => "Volume",
            'msg_no_items' => "Sorry! No items were found",
            'msg_no_lists' => "Sorry! No Reading lists were found",
            'label_category_essentialreading' => "Essential reading",
            'label_category_recommendedreading' => "Recommended reading",
            'label_category_backgroundreading' => "Background reading",
            'label_category_otherreading' => "Other reading"
         ),
        'cy' => array (
            'label_Admin' => "Creu / Golygu Rhestr",
            'label_by' => "gan",
            'label_Check_Availability' => "Gwirio Argaeledd",
            'label_in' => "yn",
            'label_Issue' => "Rhifyn",
            'label_Feedback' => "Adborth",
            'label_Filters' => "Hidlau",
            'label_Full_Text' => "Testun Llawn",
            'label_module_id' => "Rhif Adnabod y Modiwl",
            'label_Note' => "Nodyn",
            'label_Pages' => "Tudalennau",
            'label_Refresh' => "Adnewyddu",
            'label_Results' => "Canlyniadau",
            'label_Remove_Filters' => "Tynnu Hidlau",
            'label_RSS' => "RSS",
            'label_RSS_Feed' => "Porthiant RSS",
            'label_Tag' => "Tag",
            'label_View_Document' => "Gweld Dogfen",
            'label_Volume' => "Cyfrol",
            'msg_no_items' => "Ymddiheuriadau! Ni chanfuwyd eitemau",
            'msg_no_lists' => "Ymddiheuriadau! Ni chanfuwyd rhestrau darllen",
            'label_category_essentialreading' => "Deunydd darllen hanfodol",
            'label_category_recommendedreading' => "Deunydd darllen a argymhellir",
            'label_category_backgroundreading' => "Deunydd darllen cefndirol",
            'label_category_otherreading' => "Deunydd darllen arall"
         )
    );

    public $currentLabels = false;

    /**
     * Constuctor
     *
     * @param string $locale The current locale
     */
    public function __construct($locale = 'en-gb') {
        $this->m = new Mustache_Engine();
        $this->setLocale($locale);

    }

    /**
     * Set Lists
     * @param array $lists An array of rebus lists
     *
     * @return $this
     */
    public function setLists($lists)
    {
        $this->lists = $lists;
        $this->augment();
        return $this;
    }

    /**
     * Set Locale
     *
     * @param string $locale A locale
     *
     * @return void
     */
    public function setLocale($locale) {
        if(in_array($locale, $this->locales)) {
            $this->locale = $locale;
        }
        $this->currentLabels = $this->labels[$this->locale];
    }

    /**
     * Get Lavbels
     *
     * @return array An array of labels for the current locale
     */
    public function getLabels()
    {
        if (false === $this->currentLabels) {
            $this->setLocale();
        }
        return $this->currentLabels;
    }
    /**
     * Augment
     *
     * Sets Category Populated property and loads Rebus Category Object
     *
     * @return void
     */
    public function augment() {
        foreach ($this->lists as $listKey => $list) {
            if (isset($list->categories) && !empty($list->categories)) {
                $list->categoryIsPopulated = true;
            }
            foreach ($list->categories as $i => $category) {
               $list->categories[$i] = new RebusCategory($category, $this->currentLabels);
            }
        }
    }

    /**
     * Update Labels
     * Replace the default labels with custom values
     *
     * @param array $labels A keyed array of labels
     */
    public function updateLabels($labels) {
        foreach ($labels as $lang => $labelSet) {
            if(isset($this->labels[$lang])) {
                foreach($labelSet as $label => $value) {
                    if(isset($this->labels[$lang][$label])) {
                        $this->labels[$lang][$label] = $value;
                    }
                }
            }
        }
    }

    /**
     * Translate function
     *
     * @return string A translated value if the key exists or the original string
     * if it does not
     */
    public function _() {
        if (false === $this->currentLabels) {
            $this->setLocale();
        }
        $labels = $this->currentLabels;
        return function($text) use ($labels) {
            return isset($labels[$text]) ? $labels[$text] : $text;
        };
    }

    /**
     * Render
     * Render the template using mustache
     */
    public function render() {
        $template = file_get_contents(PATH_TO_TEMPLATES . '/rebus/course-reading-list.mustache');
        return $this->m->render($template, $this);
    }
}