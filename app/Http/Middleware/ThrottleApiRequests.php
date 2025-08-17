<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleApiRequests
{
    public function __construct(private RateLimiter $limiter) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request):Response  $next
     * @param  int|string|null  $maxAttempts   // opcjonalny override z route middleware: throttle.api:100,60
     * @param  int|string|null  $decaySeconds  // w sekundach
     */
    public function handle(Request $request, Closure $next, $maxAttempts = null, $decaySeconds = null): Response
    {
        // Możliwość wyłączenia throttlingu w testach
        if (app()->environment('testing') && config('api.throttle.disable_in_testing', true)) {
            return $next($request);
        }

        $max   = (int) ($maxAttempts   ?? config('api.throttle.max_attempts', 60));
        $decay = (int) ($decaySeconds  ?? config('api.throttle.decay_seconds', 60));

        $key = $this->keyFor($request);

        if ($this->limiter->tooManyAttempts($key, $max)) {
            $retryAfter = $this->limiter->availableIn($key); // PUBLICZNA
            return $this->tooMany($max, $retryAfter);
        }

        // Zliczamy bieżącą próbę z czasem wygaśnięcia okna
        $this->limiter->hit($key, $decay);

        /** @var Response $response */
        $response = $next($request);

        // Nagłówki informacyjne
        $remaining = $this->limiter->remaining($key, $max);

        $response->headers->set('X-RateLimit-Limit', (string) $max);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $remaining));

        return $response;
    }

    /**
     * Zbuduj podpis zapytania do limitowania.
     */
    protected function keyFor(Request $request): string
    {
        $id = $request->header('X-API-Key') ?: $request->ip();

        return 'api:'.sha1(
            $id.'|'.$request->method().'|'.$request->path()
        );
    }

    /**
     * Odpowiedź 429 Too Many Requests z nagłówkami.
     */
    protected function tooMany(int $limit, int $retryAfter): Response
    {
        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
        ], 429, [
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Limit' => (string) $limit,
            'X-RateLimit-Remaining' => '0',
        ]);
    }
}
