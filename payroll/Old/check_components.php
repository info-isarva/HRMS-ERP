<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
use App\Models\SalaryComponent;

$components = SalaryComponent::all();
foreach ($components as $c) {
    echo $c->id . ": " . $c->name . " (" . $c->type . ")\n";
}
