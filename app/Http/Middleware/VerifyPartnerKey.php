<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPartnerKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') && ! $request->hasHeader('X-Partner-Key')) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Відхилено: відсутній обов\'язковий заголовок авторизації X-Partner-Key.',
            ], 403);
        }

        if (! $request->wantsJson()) {
            return $next($request);
        }

        $response = $next($request);

        $response->headers->set('X-Railway-Security', 'Verified');

        return $response;
    }
}
