<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$doc = \App\Models\FileDocument::create([
    'name' => 'test excel',
    'file_path' => 'test.xlsx',
    'type' => 'excel',
    'created_by' => $user->id,
]);

$request = \Illuminate\Http\Request::create('/documents/view/' . $doc->id, 'GET');
$response = $app->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
$doc->delete();
