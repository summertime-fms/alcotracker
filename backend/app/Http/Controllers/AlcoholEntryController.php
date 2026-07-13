<?php

namespace App\Http\Controllers;

use App\Enums\AlcoholType;
use App\Http\Requests\AlcoholEntry\StoreAlcoholEntryRequest;
use App\Http\Requests\AlcoholEntry\UpdateAlcoholEntryRequest;
use App\Http\Resources\AlcoholEntryResource;
use App\Models\AlcoholEntry;
use App\Services\AlcoholEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlcoholEntryController extends Controller
{
    public function __construct(
        protected AlcoholEntryService $alcoholEntryService
    ) {}

    /**
     * Получить список записей об алкоголе
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['date', 'start_date', 'end_date']);

        $entries = $this->alcoholEntryService->getUserEntries(
            $request->user()->id,
            $filters
        );

        return AlcoholEntryResource::collection($entries);
    }

    /**
     * Создать новую запись об алкоголе
     *
     * @param StoreAlcoholEntryRequest $request
     * @return JsonResponse
     */
    public function store(StoreAlcoholEntryRequest $request): JsonResponse
    {
        $entry = $this->alcoholEntryService->createEntry(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Запись успешно создана',
            'data' => new AlcoholEntryResource($entry),
        ], 201);
    }

    /**
     * Получить конкретную запись об алкоголе
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $entry = AlcoholEntry::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => new AlcoholEntryResource($entry),
        ]);
    }

    /**
     * Обновить запись об алкоголе
     *
     * @param UpdateAlcoholEntryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateAlcoholEntryRequest $request, int $id): JsonResponse
    {
        $entry = AlcoholEntry::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $entry = $this->alcoholEntryService->updateEntry(
            $entry,
            $request->validated()
        );

        return response()->json([
            'message' => 'Запись успешно обновлена',
            'data' => new AlcoholEntryResource($entry),
        ]);
    }

    /**
     * Удалить запись об алкоголе
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $entry = AlcoholEntry::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $this->alcoholEntryService->deleteEntry($entry);

        return response()->json([
            'message' => 'Запись успешно удалена',
        ]);
    }

    /**
     * Получить статистику за период
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $statistics = $this->alcoholEntryService->getStatistics(
            $request->user()->id,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($statistics);
    }

    /**
     * Получить список доступных типов алкоголя
     *
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'data' => AlcoholType::options(),
        ]);
    }

    /**
     * Получить детальную статистику по дням
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function detailedStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'group_by' => ['nullable', 'string'],
            'alcohol_type' => ['nullable', 'string', 'in:' . implode(',', AlcoholType::values())],
        ]);

        $statistics = $this->alcoholEntryService->getDetailedStatistics(
            $request->user()->id,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('group_by', 'amount'),
            $request->input('alcohol_type')
        );

        return response()->json($statistics);
    }

    /**
     * Получить статистику по чистому спирту
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pureAlcoholStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'group_by' => ['nullable', 'string'],
        ]);

        $statistics = $this->alcoholEntryService->getPureAlcoholStatistics(
            $request->user()->id,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('group_by', 'amount')
        );

        return response()->json($statistics);
    }

    /**
     * Получить статистику по дням недели
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function weekdayStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'metric' => ['nullable', 'string', 'in:volume,pure_alcohol'],
        ]);

        $statistics = $this->alcoholEntryService->getWeekdayStatistics(
            $request->user()->id,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('metric', 'pure_alcohol')
        );

        return response()->json($statistics);
    }
}
