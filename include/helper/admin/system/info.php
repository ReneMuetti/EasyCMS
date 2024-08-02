<?php
$renderer -> loadTemplate('admin' . DS . 'default' . DS . 'system_information.htm');
$pageContent  = $renderer -> renderTemplate();

$pageIdentifier = 'system-information';