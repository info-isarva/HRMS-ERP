<?php

namespace App\Services;

use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * SSO token claims and module URLs bound to the resolved tenant (Phase 5).
 */
class TenantSsoService
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    /**
     * @param  array<string, mixed>  $extraUserFields
     * @return array<string, mixed>
     */
    public function claimsForUser(JWTSubject $user, array $extraUserFields = []): array
    {
        $claims = [
            'exp' => now()->addMinutes(5)->timestamp,
            'jti' => Str::uuid(),
            'user' => array_merge([
                'id' => $user->getAuthIdentifier(),
                'email' => $user->email,
                'hmac' => hash_hmac(
                    'sha256',
                    $user->getAuthIdentifier().$user->email,
                    env('JWT_HMAC_SECRET')
                ),
            ], $extraUserFields),
        ];

        if ($tenant = $this->tenantContext->tenant()) {
            $claims['tenant_id'] = $tenant->id;
            $claims['company_code'] = $tenant->company_code;
        }

        return $claims;
    }

    public function tokenForUser(JWTSubject $user, array $extraUserFields = []): string
    {
        return JWTAuth::customClaims($this->claimsForUser($user, $extraUserFields))
            ->fromUser($user);
    }

    public function storeHubTokens(JWTSubject $user): string
    {
        $token = $this->tokenForUser($user);

        session([
            'payroll_token' => $token,
            'attendance_token' => $token,
            'posh_token' => $token,
        ]);

        return $token;
    }

    public function moduleUrl(string $module): string
    {
        $configKey = match ($module) {
            'payroll', 'attendance', 'crm', 'posh' => $module,
            default => throw new \InvalidArgumentException("Unknown SSO module [{$module}]"),
        };

        return rtrim((string) config("services.{$configKey}.url"), '/');
    }

    public function ssoAuthenticateUrl(string $module): string
    {
        return $this->moduleUrl($module).'/sso-authenticate';
    }
}
