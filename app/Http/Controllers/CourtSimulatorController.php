<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourtRequest;
use App\Http\Requests\CourtTurnRequest;
use App\Services\LegalAssistant;
use Illuminate\Http\JsonResponse;

class CourtSimulatorController extends Controller
{
    public function __construct(private LegalAssistant $assistant) {}

    /**
     * React to the advocate's written or spoken speech.
     */
    public function turn(CourtTurnRequest $request): JsonResponse
    {
        $turn = $this->assistant->courtTurn(
            $request->courtContext(),
            trim($request->string('user_text')->toString()),
            $request->audio(),
        );

        if ($turn === null) {
            return response()->json(['ok' => false, 'error' => "Aloqada uzilish bo'ldi."]);
        }

        return response()->json(['ok' => true, ...$turn]);
    }

    /**
     * Generate a new case for the selected court and level.
     */
    public function storeCase(CourtRequest $request): JsonResponse
    {
        return response()->json($this->assistant->newCase($request->courtContext()));
    }

    public function verdict(CourtRequest $request): JsonResponse
    {
        return $this->resultResponse($this->assistant->verdict($request->courtContext()));
    }

    public function hint(CourtRequest $request): JsonResponse
    {
        return $this->resultResponse($this->assistant->hint($request->courtContext()));
    }

    private function resultResponse(?string $result): JsonResponse
    {
        return response()->json([
            'ok' => $result !== null,
            'result' => $result ?? 'Uzilish yuz berdi.',
        ]);
    }
}
