<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../script/TestApp.php';

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('TestApp');
$bootstrap->run($app);