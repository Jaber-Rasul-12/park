<?php

namespace Jaber\Player\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    /**
     * API Key ثابت - يمكنك تغييره إلى أي قيمة تريدها
     */
    protected $apiKey = 'azadi_park_jaber_ali_12122121'; 

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // الحصول على المفتاح من Header أو من Query Parameter
        $apiKey = $request->header('X-API-Key');
        
        if (!$apiKey) {
            $apiKey = $request->query('api_key');
        }

        // التحقق من وجود المفتاح
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-Key header or api_key parameter.'
            ], 401);
        }

        // التحقق من صحة المفتاح
        if ($apiKey !== $this->apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key.'
            ], 401);
        }

        // المفتاح صحيح، تابع التنفيذ
        return $next($request);
    }
}