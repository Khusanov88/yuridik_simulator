<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssistantChatRequest;
use App\Services\LegalAssistant;
use Illuminate\Http\JsonResponse;

class AssistantChatController extends Controller
{
    /**
     * Answer a question, draft a document or analyse an attached one.
     */
    public function __invoke(AssistantChatRequest $request, LegalAssistant $assistant): JsonResponse
    {
        $answer = $assistant->answer(
            $request->string('mode', 'general')->toString(),
            trim($request->string('question')->toString()),
            trim($request->string('file_text')->toString()),
            $request->string('history')->toString(),
            $request->audio(),
        );

        return response()->json([
            'ok' => $answer !== null,
            'answer' => $answer ?? "Server bilan bog'lanishda vaqtinchalik uzilish yuz berdi. Iltimos, qayta urinib ko'ring.",
        ]);
    }
}
