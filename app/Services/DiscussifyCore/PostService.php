<?php

namespace App\Services\DiscussifyCore;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Follow;
use App\Models\Forum;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\View;
use App\Models\User;
use App\Utils\Constants\AppConstants;
use App\Utils\Helpers\AuthHelpers;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use App\Utils\Traits\PostTrait;
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

class PostService
{
    use RecordFilterTrait;
    use PostTrait;

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
            $profileUrl = $this->getPetProfileUrl($createSubmissionRequest['profile_picture'], $createSubmissionRequest['name']);

            $pet = Pet::create([
                'name' => $createSubmissionRequest['name'],
                'nickname' => $createSubmissionRequest['nickname'],
                'species' => $createSubmissionRequest['species'],
                'breed' => $createSubmissionRequest['breed'],
                'description' => $createSubmissionRequest['description'],
                'date_of_birth' => $createSubmissionRequest['date_of_birth'],
                'profile_url' => $profileUrl
            ]);

            DB::commit();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new PetProfileResource($pet),
                "Pet profile created successfully",
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            if ($e->getCode() === '23000') {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'The pet name must be unique for this user.'],
                    'Error:A pet with a similar name already exists in your profile',
                    400
                );
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet profile creation',
                400
            );
        }
    }

    /**
     * @param $image
     * @param $martyrName
     * @return \Illuminate\Contracts\Foundation\Application|UrlGenerator|Application|string
     */
    public function getPetProfileUrl($image, $martyrName): \Illuminate\Contracts\Foundation\Application|UrlGenerator|string|Application
    {
        $constructName = AppConstants::$appName . '-' . $martyrName . '-' . Carbon::now() . '.' . $image->extension();
        $imageName = Str::lower(str_replace(' ', '-', $constructName));
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
            $post = Post::findOrFail($postId);

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
                new PostResource($post),
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
            $postsQuery = Post::with('user', 'forum')
                ->orderBy('created_at', 'desc');

            $this->applyPostFilters($postsQuery, $queryParams);

            $pageSize = $queryParams['page_size'] ?? 10;
            $currentPage = $queryParams['page_number'] ?? 1;
            $posts = $postsQuery->paginate($pageSize, ['*'], 'page', $currentPage);

            $this->checkIfUserHasViewedPostOrFollowedPostAuthor($posts);

            return ResponseHelpers::ConvertToPagedJsonResponseWrapper(
                PostResource::collection($posts->items()),
                'Threads retrieved successfully',
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

    /**
     * @param $slug
     * @return JsonResponse
     */
    public function getPostBySlug($slug): JsonResponse
    {
        try {
            $post = Post::with('user')
                ->where('slug', $slug)
                ->firstOrFail();

            $likesCount = $post->postLikes->count();
            $likingUsersUrls = $post->postLikes->pluck('user.profile_url')
                ->unique()
                ->toArray();
            $post->unsetRelation('postLikes');

            $post->views += 1;
            $post->save();

            $userHasFollowedAuthor = false;
            if (Auth::guard('api')->user()){
                $userId = Auth::guard('api')->id();
                if (!$post->views()->where('user_id', $userId)->exists()){
                    $post->views()->create([
                        'user_id' => $userId,
                        'viewable_id' => $post->id,
                        'viewable_type' => get_class($post),
                    ]);
                    $post->increment('views');
                }

                // Check if the user has followed the post author
                $userHasFollowedAuthor = Follow::where('user_id', $userId)
                    ->where('followable_id', $post->user_id)
                    ->where('followable_type', 'App\\Models\\User')
                    ->exists();
            }

            $responseData = [
                'post' => new PostResource($post),
                'userHasFollowedAuthor' => $userHasFollowedAuthor,
                'postLikes' => [
                    'likes' => $likesCount,
                    'users' => $likingUsersUrls,
                ]
            ];

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $responseData,
                'Thread retrieved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving Thread',
                500
            );
        }
    }

    /**
     * @return JsonResponse
     */
    public function getCoverPosts(): JsonResponse
    {
        try {
            $posts = Post::with('user', 'forum')
                ->where('is_system',0)
                ->take(4)
                ->get();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                PostResource::collection($posts),
                'Threads retrieved successfully',
                200
            );
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving threads',
                500
            );
        }
    }
}
