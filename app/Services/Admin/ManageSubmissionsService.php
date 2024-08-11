<?php

namespace App\Services\Admin;

use App\Http\Resources\UserResource;
use App\Models\Martyr;
use App\Models\Submission;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ManageSubmissionsService
{
    use DateFilterTrait;

    /**
     * @param $queryParams
     * @return JsonResponse
     */
    public function getSubmissions($queryParams)
    {
        try {
            $pageSize = $queryParams['page_size'] ?? 10;
            $currentPage = $queryParams['page_number'] ?? 1;

            $query = Submission::orderBy('created_at', 'desc');
            $this->applyFilters($query, $queryParams);
            $users = $query->paginate($pageSize, ['*'], 'page', $currentPage);

            return ResponseHelpers::ConvertToPagedJsonResponseWrapper(
                UserResource::collection($users->items()),
                'submissions retrieved successfully',
                200,
                $users
            );
        } catch (Exception $e) {
            Log::error('Exception when retrieving submissions: ' . $e->getMessage());

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving submissions',
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * @param $query
     * @param $params
     * @return void
     */
    private function applyFilters($query, $params): void
    {
        Log::info($params);
        $this->applyDateFilters($query, $params['period_from'] ?? null, $params['period_to'] ?? null);
        $this->applySearchTermFilter($query, $params['search_term'] ?? null);
    }

    /**
     * @param $query
     * @param $searchTerm
     * @return void
     */
    private function applySearchTermFilter($query, $searchTerm)
    {
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('contributions', 'like', '%' . $searchTerm . '%')
                    ->orWhere('death_reason', 'like', '%' . $searchTerm . '%');
            });
        }
    }

    /**
     * @param $approveSubmissionRequest
     * @return JsonResponse
     */
    public function approveSubmission($approveSubmissionRequest): JsonResponse
    {
        try {
            // Find the submission by ID
            $submission = Submission::find($approveSubmissionRequest['submission_id']);

            if (!$submission) {
                // Return an error response if submission not found
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Submission not found'],
                    'Error approving submission',
                    404
                );
            }

            // Check if a Martyr record already exists with the same name, birth_date, death_date, and death_reason
            $existingMartyr = Martyr::where('name', $submission->name)
                ->where('birth_date', $submission->birth_date)
                ->where('death_date', $submission->death_date)
                ->where('death_reason', $submission->death_reason)
                ->first();

            if ($existingMartyr) {
                // Return a response indicating the martyr already exists
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Martyr already exists'],
                    'Error approving submission',
                    409
                );
            }

            // Copy details to the Martyrs table
            $martyr = new Martyr([
                'email' => $submission->email,
                'name' => $submission->name,
                'birth_date' => $submission->birth_date,
                'death_date' => $submission->death_date,
                'location' => $submission->location,
                'contributions' => $submission->contributions,
                'death_reason' => $submission->death_reason,
                'profile_picture' => $submission->profile_picture,
                'is_active' => true, // Set as active
            ]);

            $martyr->save();

            // Update the submission to mark it as approved
            $submission->is_approved = true;
            $submission->approved_at = now();
            $submission->save();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['message' => 'Submission approved successfully'],
                'Submission approved',
                200
            );
        } catch (Exception $e) {
            Log::error('Exception when approving submission: ' . $e->getMessage());

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => 'An error occurred while approving the submission'],
                'Error approving submission',
                500
            );
        }
    }

    /**
     * @param $submissionRequest
     * @return JsonResponse
     */
    public function getSubmissionById($submissionRequest)
    {
        try {
            $submission = Submission::findOrFail($submissionRequest['submission_id']);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new UserResource($submission),
                'Submission fetched successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error fetching submission details',
                500
            );
        }
    }
}
