<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchSubmissionsFormRequest;
use App\Http\Requests\Submissions\ApproveSubmissionRequest;
use App\Services\Admin\ManageSubmissionsService;
use Illuminate\Http\JsonResponse;

class ManageSubmissionsController extends Controller
{

    /**
     * @var ManageSubmissionsService
     */
    private ManageSubmissionsService $_manageSubmissionService;

    public function __construct(ManageSubmissionsService $manageSubmissionService)
    {
        $this->_manageSubmissionService = $manageSubmissionService;
    }

    /**
     * @param FetchSubmissionsFormRequest $SubmissionsRequest
     * @return JsonResponse
     */
    public function getSubmissions(FetchSubmissionsFormRequest $SubmissionsRequest)
    {
        return $this->_manageSubmissionService->getSubmissions($SubmissionsRequest);
    }

    /**
     * @param ApproveSubmissionRequest $approveSubmissionRequest
     * @return JsonResponse
     */
    public function approveSubmission(ApproveSubmissionRequest $approveSubmissionRequest)
    {
        return $this->_manageSubmissionService->approveSubmission($approveSubmissionRequest);
    }

    /**
     * @param $SubmissionId
     * @return JsonResponse
     */
    public function getSubmissionById($SubmissionId)
    {
        return $this->_manageSubmissionService->getSubmissionById($SubmissionId);
    }

    /**
     * @param $SubmissionId
     * @return JsonResponse
     */
    public function toggleSubmissionStatus($SubmissionId)
    {
        return $this->_manageSubmissionService->toggleSubmission($SubmissionId);
    }
}
