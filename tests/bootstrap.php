<?php

putenv('DB_DATABASE=golden_connect_testing');
$_ENV['DB_DATABASE'] = 'golden_connect_testing';
$_SERVER['DB_DATABASE'] = 'golden_connect_testing';

require __DIR__.'/../vendor/autoload.php';
