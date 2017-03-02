<?php
$request = json_decode(file_get_contents('php://input'), true);

$data = [
	'icon'     => 'https://developer.joomla.org/images/jenkins_icon.png',
	'activity' => 'Jenkins',
	'body'     => $request['name'] . ' Build [#' . $request['build']['number'] . '](' . $request['build']['full_url'] . ') completed with status: **' . $request['build']['status'] . '**',
];

$data_string = json_encode($data);

$url = 'https://hooks.glip.com/webhook/b1e761fd-26be-4cf2-88a0-d043e60ceac4';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
	$ch,
	CURLOPT_HTTPHEADER,
	[
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string),
	]
);

curl_exec($ch);

curl_close($ch);
