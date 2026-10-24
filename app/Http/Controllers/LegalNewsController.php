<?php

namespace App\Http\Controllers;

use App\Services\LegalAssistant;
use Illuminate\Http\JsonResponse;

class LegalNewsController extends Controller
{
    /**
     * Generate a fresh digest of recent legislative changes.
     */
    public function __invoke(LegalAssistant $assistant): JsonResponse
    {
        $news = $assistant->latestNews();

        return response()->json(['ok' => $news !== [], 'news' => $news]);
    }
}
