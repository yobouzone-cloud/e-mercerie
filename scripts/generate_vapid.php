<?php
require __DIR__ . '/../vendor/autoload.php';
use Minishlink\WebPush\VAPID;
$keys = VAPID::createVapidKeys();
echo "PUBLIC=" . $keys['publicKey'] . PHP_EOL;
echo "PRIVATE=" . $keys['privateKey'] . PHP_EOL;
