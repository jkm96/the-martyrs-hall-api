<?php

namespace App\Http\Controllers\THM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\CreateSubmissionRequest;
use App\Http\Requests\Submissions\FetchPostsFormRequest;
use App\Http\Requests\Submissions\UpdatePostRequest;
use App\Services\DiscussifyCore\PostService;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    private PostService $_postService;

    public function __construct(PostService $PostService)
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
     * @param FetchPostsFormRequest $postsRequest
     * @return JsonResponse
     */
    public function getPosts(FetchPostsFormRequest $postsRequest): JsonResponse
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
