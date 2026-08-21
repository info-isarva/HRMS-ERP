<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array<int, string>
	 */
	protected $except = [
		'sync/password/from-attendance',
		'get-manual-notifications-data',
		'api/*',
		'api/notifications/*',
		'api/notifications/mark-read',
		'api/notifications/mark-all-read',
	];
}
