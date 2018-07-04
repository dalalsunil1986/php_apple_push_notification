<?php

require_once "lib/ApplePushNotification.php";
use apple_push_notification\ApplePushNotification;

$APNObj = new ApplePushNotification("","DEVCertificates.pem");

$APNObj->sendPush("5d3ebd9945f886598bd8c5f05d6fa8951997b9268dc6eb9e91c9a9d21bc1bb65","This is a test message",array("event_id"=>1));