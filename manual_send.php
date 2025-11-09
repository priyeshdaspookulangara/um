<?php
require_once 'functions.php';
require_once 'TemplateManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

$post_data = json_decode(file_get_contents('php://input'), true);

if (!isset($post_data['template_id']) || !isset($post_data['to_address']) || !isset($post_data['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: template_id, to_address, data']);
    exit();
}

$templateManager = new TemplateManager();
$template = $templateManager->getTemplateById($post_data['template_id']);

if (!$template) {
    http_response_code(404);
    echo json_encode(['error' => 'Template not found']);
    exit();
}

$data = $post_data['data'];
$data['to_address'] = $post_data['to_address'];

$subject = substitute_variables($template['subject'], $data);
$body = substitute_variables($template['body'], $data);

if (send_notification_email($data['to_address'], $subject, $body)) {
    echo json_encode(['success' => 'Email sent successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send email']);
}
