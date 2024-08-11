<?php

namespace App\Services\TMH;

use App\Http\Resources\MartyrResource;
use App\Models\Martyr;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MartyrService
{
    use DateFilterTrait;

    /**
     * @param $queryParams
     * @return JsonResponse
     */
    public function getMartyrs($queryParams): JsonResponse
    {
        try {
            $martyrQuery = Martyr::query();
            $martyrQuery->where('is_active', true);

            $this->applyFilters($martyrQuery, $queryParams);

            $orderBy = $queryParams['order_by'] ?? 'created_at desc';
            list($orderColumn, $orderDirection) = $this->parseOrderBy($orderBy);
            if (in_array($orderDirection, ['asc', 'desc']) && !empty($orderColumn)) {
                $martyrQuery->orderBy($orderColumn, $orderDirection);
            }

            $pageSize = $queryParams['page_size'] ?? 10;
            $currentPage = $queryParams['page_number'] ?? 1;
            $martyrs = $martyrQuery->paginate($pageSize, ['*'], 'page', $currentPage);

            return ResponseHelpers::ConvertToPagedJsonResponseWrapper(
                MartyrResource::collection($martyrs->items()),
                'Martyrs retrieved successfully',
                200,
                $martyrs
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToPagedJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving martyrs',
                500
            );
        }
    }

    /**
     * Parse the order_by parameter to get the column and direction.
     *
     * @param string $orderBy
     * @return array
     */
    private function parseOrderBy(string $orderBy): array
    {
        $orderMapping = [
            'thm_name_asc' => ['name', 'asc'],
            'thm_oldest_asc' => ['death_date', 'asc'],
            'thm_latest_desc' => ['death_date', 'desc'],
            'created_at_desc' => ['created_at', 'desc']
        ];

        return $orderMapping[$orderBy] ?? ['created_at', 'desc'];
    }

    /**
     * @param $query
     * @param $params
     * @return void
     * @throws Exception
     */
    private function applyFilters($query, $params)
    {
        if (!empty($params['period_from']) || !empty($params['period_to'])){
            $periodFrom = $this->formatDateString($params['period_from']);
            $periodTo = $this->formatDateString($params['period_to']);
            $this->applyDateFilters($query, $periodFrom, $periodTo);
        }

        if (!empty($params['country'])) {
            $query->where('location', $params['country']);
        }

        if (!empty($params['reason'])) {
            $query->where('death_reason', str_replace('_',' ',$params['reason']));
        }

        if(!empty($params['search_term'])){
            $this->applySearchTermFilter($query, $params['search_term']);
        }
    }

    /**
     * @param $query
     * @param $searchTerm
     */
    private function applySearchTermFilter($query, $searchTerm)
    {
        $query->where(function ($query) use ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('contributions', 'like', '%' . $searchTerm . '%')
                ->orWhere('death_reason', 'like', '%' . $searchTerm . '%');
        });
    }

    /**
     * @param $martyrRequest
     * @return JsonResponse
     */
    public function getMartyrBySlug($martyrRequest): JsonResponse
    {
        try {
            $slug = $martyrRequest['martyr_id'] ?? null;
            if (!$slug) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Slug is required'],
                    'Invalid request',
                    400
                );
            }

            $martyr = Martyr::where('slug', $slug)->where('is_active', true)->first();

            if (!$martyr) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Martyr not found'],
                    'Martyr not found',
                    404
                );
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new MartyrResource($martyr),
                'Martyr retrieved successfully',
                200
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving martyr',
                500
            );
        }
    }

    /**
     * @param array $requestData
     * @return JsonResponse
     */
    public function lightMartyrsCandle($requestData): JsonResponse
    {
        try {
            $slug = $requestData['martyr_id'] ?? null;
            Log::info($requestData['tmh_ip_address']);
            $martyr = Martyr::where('slug', $slug)->where('is_active', true)->first();

            if (!$martyr) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Martyr not found'],
                    'Martyr not found',
                    404
                );
            }

            // Increase the candle count
            $martyr->candles = ($martyr->candles ?? 0) + 1;
            $martyr->save();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new MartyrResource($martyr),
                'Candle lit successfully',
                200
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error lighting candle',
                500
            );
        }
    }


}
