<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$json = json_encode(['email' => 'admin@crm.com', 'password' => 'password']);
$request = Illuminate\Http\Request::create('/api/login', 'POST', [], [], [], [
    'CONTENT_TYPE' => 'application/json',
    'HTTP_ACCEPT' => 'application/json',
], $json);

$response = $kernel->handle($request);
echo $response->getContent();
