<?php
$renderer -> loadTemplate('admin' . DS . 'default' . DS . 'dashboard.htm');

$pageContent = $renderer -> renderTemplate();

$pageIdentifier = 'dashboard';