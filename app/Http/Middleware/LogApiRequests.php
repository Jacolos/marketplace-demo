<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiLog;
use Throwable;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        // Loguj tylko ścieżki API
        if ($request->is('api/*')) {
            $this->writeLog($request, $response, microtime(true) - $start);
        }

        return $response;
    }

    protected function writeLog(Request $request, Response $response, float $elapsed): void
    {
        try {
            // user() może być ustawiony przez Twój ApiAuthentication (via setUserResolver)
            $customer = $request->user() ?: $request->attributes->get('auth_customer');

            ApiLog::create([
                'endpoint'        => ltrim($request->path(), '/'),
                'method'          => $request->method(),
                'ip_address'      => $request->ip(),
                'customer_id'     => optional($customer)->id, // może być null
                'request_headers' => $this->filterHeaders($request->headers->all()),
                'request_body'    => $this->safeRequestBody($request),
                'response_code'   => $response->getStatusCode(),
                'response_body'   => $this->safeResponseBody($response),
                'response_time'   => $elapsed * 1000, // ms
            ]);
        } catch (Throwable $e) {
            // Nigdy nie psuj odpowiedzi błędem loggera
            logger()->warning('API log failed', [
                'error' => $e->getMessage(),
                'endpoint' => $request->path(),
            ]);
        }
    }

    protected function filterHeaders(array $headers): array
    {
        // nie logujemy wrażliwych nagłówków (np. X-API-Key)
        $allowed = ['content-type', 'accept', 'user-agent', 'referer'];
        $out = [];
        foreach ($headers as $k => $v) {
            if (in_array(strtolower($k), $allowed, true)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    protected function safeRequestBody(Request $request): array
    {
        // usuń potencjalnie wrażliwe dane
        return collect($request->all())->except(['password', 'api_key', 'X-API-Key'])->toArray();
    }

    protected function safeResponseBody(Response $response)
    {
        $contentType = $response->headers->get('Content-Type') ?? '';
        $body = $response->getContent();

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($body, true);
            if (is_array($decoded) && isset($decoded['data']) && is_array($decoded['data'])) {
                // nie rozdmuchuj logów
                $decoded['data'] = array_slice($decoded['data'], 0, 10);
            }
            return $decoded ?? $body;
        }

        return mb_substr($body, 0, 1000);
    }
}
