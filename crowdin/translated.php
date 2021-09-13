<?php

$env = parse_ini_file('.env.php');
$apiKey = $env['apiKey'] ?? '';
$crowdinApiKey = $env['crowdinApiKey'] ?? '';
$projectIds = explode(',', $env['projectIds'] ?? []);

if (empty($apiKey) || empty($crowdinApiKey) || empty($projectIds)) {
	http_response_code(500);
	die('Configuration error.');
}

if (empty($_SERVER['HTTP_X_API_KEY'])	|| $apiKey !== $_SERVER['HTTP_X_API_KEY']) {
	http_response_code(400);
	die('API key validation failed.');
}
	
$json = file_get_contents('php://input');

try {
	$data = json_decode($json, true, JSON_THROW_ON_ERROR);
} catch(Exception $e) {
	http_response_code(400);
	die('Error decoding input: ' . $e->getMessage());
}

// It's possible that crowdin send more then one event
// We only support one event because Crowdin only support one export at the time
$event = $data;

// validate request
if (empty($data)) {
	http_response_code(400);
	die('No input provided');
}

require_once __DIR__ . '/helper.php';

if (($event['event'] ?? '') !== 'project.approved') {
	http_response_code(400);
	die('Error event not supported: ' . $event['event'] ?? '');
}

if (!in_array($event['project_id'] ?? '', $projectIds)) {
	http_response_code(400);
	die('Error project not supported: ' . $event['project_id'] ?? '');
}

if (empty($event['language'])) {
	http_response_code(400);
	die('Error language not provided');
}

// The Crowdin Enterprise address and JSON Structure
$url = 'https://joomla.crowdin.com/api/v2/projects/' . $event['project_id'] . '/translations/builds';
$parameter = [
	"targetLanguageIds" => [$event['language']],
	"skipUntranslatedStrings" => true,
	"skipUntranslatedFiles" => false,
	"exportWithMinApprovalsCount" => 1
];

$result = RestCurl::post($url, $parameter);

try {
	$resultDecoded = json_decode($result['data'], true, JSON_THROW_ON_ERROR);

} catch(Exception $e) {
	http_response_code(500);
	die('Crodin API Error unable to decode response: ' . print_r($result, true));
}

if ($result['http_code'] >= 400) {
	http_response_code(500);
	die('Crodin API Error:' . print_r($resultDecoded, true));
}

die('Success:'  . print_r($resultDecoded, true));