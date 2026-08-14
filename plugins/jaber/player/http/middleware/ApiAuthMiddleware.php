<?php

namespace Jaber\Player\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // الحصول على المفتاح من ملف البيئة
        $apiKey = $request->header('X-API-Key');
        
        if (!$apiKey) {
            $apiKey = $request->query('api_key');
        }

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-Key header or api_key parameter.'
            ], 401);
        }

        // التحقق من المفتاح من ملف .env
        if ($apiKey !== env('API_SECRET_KEY')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key.'
            ], 401);
        }

        return $next($request);
    }
}