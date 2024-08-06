<?php

namespace App\Services\TMH;

use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use App\Models\User;
use App\Utils\Constants\AppConstants;
use App\Utils\Helpers\AuthHelpers;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use App\Utils\Traits\RecordFilterTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubmissionService
{
    use RecordFilterTrait;

    /**
     * @param $createSubmissionRequest
     * @return JsonResponse
     */
    public function addNewSubmission($createSubmissionRequest): JsonResponse
    {
        try {
            DB::beginTransaction();

            if (!$createSubmissionRequest['profile_picture']) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => "Profile picture is required"],
                    "Profile picture is required",
                    400
                );
            }
            $profileUrl = $this->getPetProfileUrl($createSubmissionRequest);

            $submission = Submission::create([
                'email' => $createSubmissionRequest['email'],
                'name' => $createSubmissionRequest['name'],
                'birth_date' => $this->formatDateString($createSubmissionRequest['birth_date']),
                'death_date' => $this->formatDateString($createSubmissionRequest['death_date']),
                'location' => $createSubmissionRequest['location'],
                'contributions' => $createSubmissionRequest['contributions'],
                'death_reason' => $createSubmissionRequest['death_reason'],
                'profile_picture' => $profileUrl
            ]);

            DB::commit();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new SubmissionResource($submission),
                "Submitted successfully",
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            if ($e->getCode() === '23000') {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'A martyr with such name exists'],
                    'A martyr with such name exists',
                    400
                );
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'An error occurred. Please try again later',
                400
            );
        }
    }

    /**
     * @param $submissionRequest
     * @return \Illuminate\Contracts\Foundation\Application|UrlGenerator|Application|string
     */
    public function getPetProfileUrl($submissionRequest): \Illuminate\Contracts\Foundation\Application|UrlGenerator|string|Application
    {
        $martyrName = $submissionRequest['name'];
        $image = $submissionRequest['profile_picture'];
        $birthDate = Carbon::parse($submissionRequest['birth_date'])->format('Y-m-d');
        $deathDate = Carbon::parse($submissionRequest['death_date'])->format('Y-m-d');
        $deathReason = $submissionRequest['death_reason'];

        $key = $martyrName . ' rose ' . $birthDate . ' died ' . $deathDate. ' reason ' . $deathReason;
        $constructName = AppConstants::$appName . '-' . $key . '-' . Carbon::now() . '.' . $image->extension();
        $imageName = Str::lower(preg_replace('/[\/\s]+/', '-', $constructName));

        $image->move(public_path('images/martyr_profiles'), $imageName);

        return url('images/martyr_profiles/' . $imageName);
    }

    /**
     * @param $postId
     * @param $updatePostRequest
     * @return JsonResponse
     */
    public function editPost($postId, $updatePostRequest): JsonResponse
    {
        try {
            $post = Submission::findOrFail($postId);

            // Check if the authenticated user owns the post
            if ($post->user_id !== Auth::id()) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    null,
                    'You are not authorized to edit this thread',
                    403
                );
            }

            DB::beginTransaction();

            switch($updatePostRequest['type']){
                case 'description':
                    $post->description = trim($updatePostRequest['description']);
                    break;
                case 'title':
                    $post->title = trim($updatePostRequest['title']);
                    break;
                case 'tags':
                    $post->tags = trim($updatePostRequest['tags']);
                    // Update post tags
                    $tags = trim($updatePostRequest['tags']);
                    $tagsArray = explode(',', $tags);
                    $tagsArray = array_map('trim', $tagsArray);

                    // Delete existing post tags
                    $post->postTags()->delete();

                    // Create new post tags
                    foreach ($tagsArray as $tag) {
                        PostTag::create([
                            'post_id' => $post->id,
                            'tag' => $tag
                        ]);
                    }
                    break;
            }

            $post->save();

            DB::commit();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new SubmissionResource($post),
                'Thread updated successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error updating thread',
                500
            );
        }
    }

    /**
     * @param $queryParams
     * @return JsonResponse
     */
    public function getPosts($queryParams): JsonResponse
    {
        try {
            $postsQuery = Submission::orderBy('created_at', 'desc');

            $this->applyPostFilters($postsQuery, $queryParams);

            $pageSize = $queryParams['page_size'] ?? 10;
            $currentPage = $queryParams['page_number'] ?? 1;
            $posts = $postsQuery->paginate($pageSize, ['*'], 'page', $currentPage);

            return ResponseHelpers::ConvertToPagedJsonResponseWrapper(
                SubmissionResource::collection($posts->items()),
                'Submissions retrieved successfully',
                200,
                $posts
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToPagedJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving Threads',
                500
            );
        }
    }
}
