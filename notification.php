<?php
require __DIR__ . './vendor/autoload.php';

$options = array(
    'cluster' => 'ap2',
    'useTLS' => true
);
$pusher = new Pusher\Pusher(
    '0efd8a2b41e70eba694f',
    '0fc3959916defd1ee531',
    '1994632',
    $options
);

$data['message'] = 'hello world';
$pusher->trigger('my-channel', 'my-event', $data);
