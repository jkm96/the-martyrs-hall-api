<?php

namespace App\Http\Controllers\TMH;

use App\Http\Controllers\Controller;
use App\Http\Requests\Martyrs\FetchMartyrsFormRequest;
use App\Http\Requests\Martyrs\LightMartyrsCandleRequest;
use App\Http\Requests\Martyrs\RetrieveMartyrRequest;
use App\Services\TMH\MartyrService;
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
     * @param RetrieveMartyrRequest $martyrRequest
     * @return JsonResponse
     */
    public function retrieveMartyrById(RetrieveMartyrRequest $martyrRequest): JsonResponse
    {
        return $this->_martyrService->getMartyrBySlug($martyrRequest);
    }


    /**
     * Light a martyr's candle by increasing the candle count.
     *
     * @param LightMartyrsCandleRequest $martyrRequest
     * @return JsonResponse
     */
    public function lightMartyrsCandle(LightMartyrsCandleRequest $martyrRequest): JsonResponse
    {
        return $this->_martyrService->lightMartyrsCandle($martyrRequest);
    }
}
