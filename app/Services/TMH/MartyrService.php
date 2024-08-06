<?php

namespace App\Services\TMH;

use App\Http\Resources\MartyrResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Martyr;
use App\Models\Submission;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;

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
        if (empty($orderBy)) {
            return ['created_at', 'desc'];
        }

        $parts = explode(' ', trim($orderBy));
        $column = $parts[0] ?? 'created_at';
        $direction = strtolower($parts[1] ?? 'desc');

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        return [$column, $direction];
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

    public function getMartyrBySlug($slug)
    {
    }
}
