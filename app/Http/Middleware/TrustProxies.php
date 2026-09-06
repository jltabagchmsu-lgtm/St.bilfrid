<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for the application.
     *
     * Caddy (and FrankenPHP / Docker) terminates TLS and forwards the
     * request to PHP, so Laravel must trust the `X-Forwarded-*` headers
     * to correctly detect HTTPS, host, port and client IP.
     *
     * Using '*' trusts the calling IP (the proxy in front of PHP), which
     * is the Laravel default skeleton behaviour and correct for a
     * single-proxy Caddy setup. Override with TRUSTED_PROXIES env for
     * stricter CIDR allowlists (e.g. "127.0.0.1,10.0.0.0/8,172.16.0.0/12").
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The proxy header mappings.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PREFIX
        | Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Get the trusted proxies, allowing an env override.
     *
     * @return array<int, string>|string|null
     */
    protected function proxies()
    {
        $proxies = env('TRUSTED_PROXIES', $this->proxies);

        // Allow explicitly disabling via TRUSTED_PROXIES=null / empty.
        if ($proxies === 'null' || $proxies === 'false' || $proxies === '') {
            return null;
        }

        if ($proxies === '*' || $proxies === '**') {
            return $proxies;
        }

        if (is_string($proxies)) {
            return array_map('trim', explode(',', $proxies));
        }

        return $proxies;
    }
}
