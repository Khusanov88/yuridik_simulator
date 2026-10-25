<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdvisorRequest;
use App\Http\Requests\CourtRequest;
use App\Http\Requests\CourtTurnRequest;
use App\Services\CourtHall;
use Illuminate\Http\JsonResponse;

class CourtHallController extends Controller
{
    public function __construct(private CourtHall $courtHall) {}

    /**
     * React to the advocate's speech and score it.
     */
    public function turn(CourtTurnRequest $request): JsonResponse
    {
        $turn = $this->courtHall->turn(
            $request->courtContext(),
            trim($request->string('user_text')->toString()),
            $request->audio(),
        );

        if ($turn === null) {
            return response()->json(['ok' => false, 'error' => "Server bilan aloqada qisqa uzilish bo'ldi. Qayta urinib ko'ring."]);
        }

        return response()->json(['ok' => true, ...$turn]);
    }

    /**
     * Generate a new case for the selected court and level.
     */
    public function storeCase(CourtRequest $request): JsonResponse
    {
        $case = $this->courtHall->newCase($request->courtContext());

        if ($case === null) {
            return response()->json(['ok' => false, 'error' => "Yangi kazus yaratishda uzilish bo'ldi."]);
        }

        return response()->json(['ok' => true, ...$case]);
    }

    public function hint(CourtRequest $request): JsonResponse
    {
        $hint = $this->courtHall->hint($request->courtContext());

        return response()->json(['ok' => $hint !== null, 'hint' => $hint ?? 'Maslahat olishda xatolik.']);
    }

    public function verdict(CourtRequest $request): JsonResponse
    {
        $verdict = $this->courtHall->verdict($request->courtContext());

        return response()->json(['ok' => $verdict !== null, 'verdict' => $verdict ?? "Hukm chiqarishda uzilish bo'ldi."]);
    }

    public function advise(AdvisorRequest $request): JsonResponse
    {
        $answer = $this->courtHall->advise(
            $request->string('mode', CourtHall::ADVISOR_MODES[0])->toString(),
            trim($request->string('question')->toString()),
            $request->audio(),
        );

        return response()->json(['ok' => $answer !== null, 'answer' => $answer ?? "Javob olishda uzilish bo'ldi."]);
    }
}
