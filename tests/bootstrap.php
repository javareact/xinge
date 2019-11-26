<?php

use Xinge\Tests\Xinge\XingePushTest;

$appId         = getenv('appId');
$secretKey     = getenv('secretKey');
$accessId      = getenv('accessId');
$xingePushTest = new XingePushTest($appId, $secretKey, $accessId);