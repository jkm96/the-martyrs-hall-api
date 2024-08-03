<?php

namespace App\Http\Controllers\TMH;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\CreateSubmissionRequest;
use App\Http\Requests\Submissions\FetchMartyrsFormRequest;
use App\Http\Requests\Submissions\UpdatePostRequest;
use App\Services\TMH\SubmissionService;
use Illuminate\Http\JsonResponse;

class SubmissionController extends Controller
{
    private SubmissionService $_postService;

    public function __construct(SubmissionService $PostService)
    {
        $this->_postService = $PostService;
    }

    /**
     * @param CreateSubmissionRequest $createSubmissionRequest
     * @return JsonResponse
     */
    public function createSubmission(CreateSubmissionRequest $createSubmissionRequest): JsonResponse
    {
        return $this->_postService->addNewSubmission($createSubmissionRequest);
    }

    /**
     * @param $postId
     * @param UpdatePostRequest $updatePostRequest
     * @return JsonResponse
     */
    public function updatePost($postId,UpdatePostRequest $updatePostRequest): JsonResponse
    {
        return $this->_postService->editPost($postId,$updatePostRequest);
    }

    /**
     * @param FetchMartyrsFormRequest $postsRequest
     * @return JsonResponse
     */
    public function getPosts(FetchMartyrsFormRequest $postsRequest): JsonResponse
    {
        return $this->_postService->getPosts($postsRequest);
    }

    /**
     * @param $slug
     * @return JsonResponse
     */
    public function getPostBySlug($slug): JsonResponse
    {
        return $this->_postService->getPostBySlug($slug);
    }
}
