<?php

function handleError($httpStatus, $message) {
    header($httpStatus);
    $template = file_get_contents(PATH_TO_TEMPLATES . '/error.mustache');
    $view = (object) array('message' => $message);
    $m = new Mustache_Engine();
    echo $m->render($template, $view);
}
