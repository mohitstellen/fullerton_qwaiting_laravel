<?php

namespace App\Http\Controllers\Sso;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Settings as OneLoginSettings;
use OneLogin\Saml2\Error as SamlError;
use Illuminate\Support\Facades\Session;



class JumpCloudController extends Controller
{

private function buildSettings(Request $request): array
    {
        $config = config('sso_jumpcloud');

        $base = $request->getSchemeAndHttpHost();
 
        $config['sp']['entityId'] = $base . '/sso/metadata';
        $config['sp']['assertionConsumerService']['url'] = $base . '/sso/acs';
        $config['sp']['singleLogoutService']['url'] = $base . '/sso/logout';

        // if (!isset($config['security'])) {
        //     $config['security'] = [];
        // }
        // $config['security']['requestedAuthnContext'] = false;
        // // For local/dev, don't require matching InResponseTo (prevents "Session expired").
        // // Consider enabling in production when HTTPS cookies are stable.
        // $config['security']['rejectUnsolicitedResponsesWithInResponseTo'] = false;

        return $config;
    }

    public function metadata(Request $request)
    {
        $auth = new OneLoginAuth($this->buildSettings($request));
        $metadata = $auth->getSettings()->getSPMetadata();

        if ($errors = $auth->getSettings()->validateMetadata($metadata)) {
            return response()->json(['error' => $errors], 500);
        }

        return response($metadata, 200)->header('Content-Type', 'application/xml');
    }

    public function login(Request $request)
    {
        try {
            // dd($request);
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session()->start();
            }
            $auth = new OneLoginAuth($this->buildSettings($request));
            $loginUrl = $auth->login(null, [], true, false, false, true);
            return redirect()->away($loginUrl);
        } catch (SamlError $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acs(Request $request)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session()->start();
        }

        $auth = new OneLoginAuth($this->buildSettings($request));
 
        $auth->processResponse();

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            return abort(400, 'SAML ACS error: ' . implode(', ', $errors));
        }
        if (!$auth->isAuthenticated()) {
            return redirect('/login')->with('error', 'SAML Authentication failed');
        }

        $attributes = $auth->getAttributes();
        dd($attributes);
        $nameId = $auth->getNameId();
        $email = $attributes['email'][0] ?? $nameId ?? null;
        if (!$email) {
            return abort(400, 'Email not provided by IdP. Ensure NameID or email attribute is configured.');
        }

        $firstName = $attributes['givenName'][0] ?? $attributes['first_name'][0] ?? null;
        $lastName  = $attributes['sn'][0] ?? $attributes['last_name'][0] ?? null;
        $displayName = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: ($attributes['name'][0] ?? $email);
        // $user = User::firstOrCreate(
        //     ['email' => $email],
        //     [
        //         'name' => $displayName,
        //         'password' => bcrypt(Str::random(32)),
        //     ]
        // );

        // Auth::login($user);

        // return redirect()->route('tenant.dashboard');
    }

    public function logout(Request $request)
    {
        $auth = new OneLoginAuth($this->buildSettings($request));
        $returnTo = url('/');
        return redirect($auth->logout($returnTo));
    }
}
