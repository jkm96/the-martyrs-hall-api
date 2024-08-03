<?php

namespace App\Http\Controllers\TMH;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\CreateSubmissionRequest;
use App\Http\Requests\Submissions\FetchMartyrsFormRequest;
use App\Http\Requests\Submissions\UpdatePostRequest;
use App\Services\TMH\MartyrService;
use App\Services\TMH\SubmissionService;
use Illuminate\Http\JsonResponse;

class MartyrController extends Controller
{

    private MartyrService $_martyrService;

    public function __construct(MartyrService $martyrService)
    {

        $this->_martyrService = $martyrService;
    }

    /**
     * @param FetchMartyrsFormRequest $postsRequest
     * @return JsonResponse
     */
    public function getMartyrs(FetchMartyrsFormRequest $postsRequest): JsonResponse
    {
        return $this->_martyrService->getMartyrs($postsRequest);
    }

    /**
     * @param $slug
     * @return JsonResponse
     */
    public function getMartyrBySlug($slug): JsonResponse
    {
        return $this->_martyrService->getMartyrBySlug($slug);
    }
}
