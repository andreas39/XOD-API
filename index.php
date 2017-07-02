<?php

define('SITE_ROOT', dirname(__FILE__));

require SITE_ROOT . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'config.php';

require SITE_ROOT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

Bootstrap::init();