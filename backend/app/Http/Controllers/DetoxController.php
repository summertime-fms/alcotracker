<?php

namespace App\Http\Controllers;

use App\Services\DetoxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetoxController extends Controller
{
    public function __construct(
        protected DetoxService $detoxService
    ) {}

    /**
     * Получить данные для раздела «Здоровье и Детокс»
     */
    public function insights(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $insights = $this->detoxService->getInsights(
            $request->user()->id,
            isset($validated['year']) ? (int) $validated['year'] : null
        );

        return response()->json($insights);
    }
}
