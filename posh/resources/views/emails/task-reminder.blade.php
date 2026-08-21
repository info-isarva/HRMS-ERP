<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Task Reminder</title>
	<style>
		body { font-family: Arial, Helvetica, sans-serif; background:#f6f8fb; color:#333; margin:0; padding:0; }
		.email-wrapper { width:100%; padding:30px 16px; }
		.email-container { max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
		.email-header { background:#0b63d1; color:#fff; padding:18px 24px; }
		.email-body { padding:24px; }
		.btn { display:inline-block; background:#0b63d1; color:#fff !important; padding:10px 18px; text-decoration:none; border-radius:6px;  }
		.meta { color:#6b7280; font-size:13px; }
		.footer { padding:16px 24px; font-size:12px; color:#9aa0a6; background:#fbfdff; }
		.label { color:#6b7280; font-size:13px; }
	</style>
</head>
<body>
	<div class="email-wrapper">
		<div class="email-container">
			<div class="email-header">
				<strong>{{ config('app.name') }}</strong>
			</div>
			<div class="email-body">
				<h2 style="margin-top:0;">Task Reminder</h2>
				<p class="meta">Hi {{ $user->name }},</p>

				<p style="font-size:15px;">This is a friendly reminder for the task <strong>"{{ $task->name }}"</strong>.</p>

				@if(!empty($task->description))
					<p><span class="label">Description:</span><br>{{ $task->description }}</p>
				@endif

				<table width="100%" style="margin:16px 0;border-collapse:collapse;">
					<tr>
						<td style="padding:8px 0;"><strong>Due At</strong></td>
						<td style="padding:8px 0; text-align:right;">{{ \Carbon\Carbon::parse($task->due_at)->format('d M Y, H:i') }}</td>
					</tr>
					<tr>
						<td style="padding:8px 0;"><strong>Priority</strong></td>
						<td style="padding:8px 0; text-align:right;">{{ ucfirst($task->priority) }}</td>
					</tr>
					@if(!empty($task->status))
					<tr>
						<td style="padding:8px 0;"><strong>Status</strong></td>
						<td style="padding:8px 0; text-align:right;">{{ $task->status }}</td>
					</tr>
					@endif
				</table>

				@if($relatedItem)
					@php
						$relatedTitle = $relatedItem->title ?? ($relatedItem->name ?? 'N/A');
						$contact = $relatedItem->person ?? null;
						$contactName = null;
						if ($contact) {
							$contactName = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
						}
					@endphp

					<hr style="border:none;border-top:1px solid #eef2f7;margin:18px 0;">

					<p style="margin:0 0 8px 0;"><strong>{{ ucfirst($task->related_type) }}:</strong> {{ $relatedTitle }}</p>
					@if($contactName)
						<p style="margin:0 0 8px 0;"><strong>Contact:</strong> {{ $contactName }}@if(!empty($contact->job_title)) &nbsp;•&nbsp; <span class="label">{{ $contact->job_title }}</span>@endif</p>
					@endif
				@endif

				@php
					$link = $task->related_type === 'lead' ? route('leads.show', $task->related_id) : route('deals.show', $task->related_id);
				@endphp

				<p style="margin-top:18px;">
					<a class="btn" href="{{ $link }}" style="background:#0b63d1;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:6px;display:inline-block;">Open {{ ucfirst($task->related_type) }}</a>
				</p>
			</div>
			<div class="footer">
				<div>{{ config('app.name') }} — Manage your tasks efficiently.</div>
			</div>
		</div>
	</div>
</body>
</html>
