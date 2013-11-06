<?php

require_once realpath(__DIR__ . '/../../..') . '/config/config.php';
require_once('RebusTemplate.php');

// Validate request

$clean = new stdClass();

if (! array_key_exists('course', $_GET)) {
    handleError('HTTP/1.1 400 Bad Request', 'Required parameter "course" not specified');
    exit();
} else {
    $clean->course = (strpos($_GET['course'], "_") !== false)
        ? preg_replace('/^.*_/', "", $_GET['course']): $_GET['course'];
}

if (array_key_exists('callback', $_GET)) {
    $clean->callback = $_GET['callback'];
}

$clean->locale = array_key_exists('locale', $_GET) ? $_GET['locale'] : "en-gb";


// Fetch Data

try {
    require_once '../../../../SU/DataAccess/Rebus.php';
    $dao = new SU_DataAccess_Rebus();
    $template = new RebusTemplate($clean->locale);
    $view = $template->setLists(
        $dao->getListsByCourseId($clean->course, true, true)
    );
    $labels = $template->getLabels();

} catch (SU_Exception $e) {
    handleError('HTTP/1.0 404 Not Found', $e->getMessage());
    exit();
}

// Render

$data = (object) array(
    'html' => $view->render(),
    'labels' => $labels
);

if (isset($clean->callback)) {
    header('Content-Type: text/javascript');
    header('X-XSS-Protection: 0');
    printf('%s(%s)', $clean->callback, json_encode($data));
} else {
    print $data->html;
}
