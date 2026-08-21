<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Notifications\DatabaseNotification;
use App\Models\TaskReminder;

echo "Recent Notifications:\n";
$notes = DatabaseNotification::latest()->limit(20)->get();
foreach ($notes as $n) {
    $data = json_decode($n->data, true);
    $msg = $data['message'] ?? ($data['title'] ?? json_encode($data));
    $read = $n->read_at ? $n->read_at : '(unread)';
    echo "{$n->id} | user:{$n->notifiable_id} | {$read} | {$n->created_at} | {$msg}\n";
}

echo "\nRecent TaskReminders:\n";
$rem = TaskReminder::latest()->limit(20)->get();
foreach ($rem as $r) {
    echo "{$r->id} | task:{$r->task_id} | user:{$r->user_id} | remind_at:{$r->remind_at} | notification_sent:".($r->notification_sent?1:0)." | email_sent:".($r->email_sent?1:0)."\n";
}
