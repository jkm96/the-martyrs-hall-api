<?php

namespace App\Http\Controllers\TMH;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSiteContentRequest;
use App\Http\Requests\FetchSiteContentRequest;
use App\Services\TMH\SiteContentService;
use Illuminate\Http\JsonResponse;

class SiteContentController extends Controller
{
    private SiteContentService $_contentService;

    public function __construct(SiteContentService $contentService)
    {
        $this->_contentService = $contentService;
    }

    /**
     * @param FetchSiteContentRequest $contentRequest
     * @return JsonResponse
     */
    public function getSiteContent(FetchSiteContentRequest $contentRequest): JsonResponse
    {
        return $this->_contentService->fetchSiteContentByType($contentRequest);
    }

    /**
     * @param $contentId
     * @return JsonResponse
     */
    public function getSiteContentById($contentId): JsonResponse
    {
        return $this->_contentService->fetchSiteContentById($contentId);
    }

    /**
     * @param CreateSiteContentRequest $siteContentRequest
     * @return JsonResponse
     */
    public function createContent(CreateSiteContentRequest $siteContentRequest): JsonResponse
    {
        return $this->_contentService->addSiteContent($siteContentRequest);
    }

    /**
     * @param CreateSiteContentRequest $siteContentRequest
     * @param $contentId
     * @return JsonResponse
     */
    public function updateSiteContent(CreateSiteContentRequest $siteContentRequest, $contentId): JsonResponse
    {
        return $this->_contentService->editSiteContent($siteContentRequest, $contentId);
    }
}
