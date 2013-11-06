<?php

require_once('RebusItem.php');

class RebusCategory {

    /**
     * @var array $labels A keyed array of labels for translation
     */
    public $labels = array();

    /**
     * @var stdClass $category The category object
     */
    public $category;


    /**
     * Constuctor
     *
     * @param stdClass $category A Rebus Category
     * @param array    $labels   A keyed array of labels for translation
     */
    public function __construct($category, $labels) {
        $this->category = $category;
        $this->labels = $labels;
        $this->augment();
    }

    /**
     * Augment
     *
     * Sets Item Populated property and loads Rebus Item Object
     *
     * @return void
     */
    public function augment() {
        if(isset($this->category->items) && !empty($this->category->items)) {
            $this->category->itemsIsPopulated = true;
            $this->category->{'title-norm'} = $this->normaliseTitle($this->category->title);
            $this->category->itemsTotal = count($this->category->items);
            foreach ($this->category->items as $i => $item) {
                 $this->category->items[$i] = new RebusItem($item, $this->labels);
            }
        }
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
        return isset($this->labels[$text]) ? $this->labels[$text] : false;
    }

    /**
     * Get the Title
     *
     * @return function A closure function which will return a translated value
     * if the key exists or the original string  if it does not
     */
    public function _title() {
        $view = $this;
        $category = $this->category;
        return function() use ($category, $view) {
            $title = strtolower(str_replace(" ", "", $category->title));
            $translation = $view->translate("label_category_" . $title);
            return (false !== $translation) ? $translation : $category->title;
        };
    }

    /**
     * Normalise Title
     *
     * @return string The title value lowercased and stipped of spaces and
     * punctuation
     */
    public function normaliseTitle($title) {
        return strtolower(
                preg_replace(array("/^\s+|\s+$/", "/[^\w\s]|_/", "/\s+/"), "", $title)
        );
    }

}