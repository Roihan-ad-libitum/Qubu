<?php

require_once __DIR__ . '/Session.php';

class Csrf
{
    public static function token(): string
    {
        $token = Session::get('csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    public static function isValid(mixed $submittedToken): bool
    {
        $storedToken = Session::get('csrf_token');
        return is_string($submittedToken)
            && is_string($storedToken)
            && $storedToken !== ''
            && hash_equals($storedToken, $submittedToken);
    }
}
