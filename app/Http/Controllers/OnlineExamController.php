<?php

namespace App\Http\Controllers;

use App\Enums\AttemptStatus;
use App\Http\Requests\SubmitAttemptRequest;
use App\Models\OnlineExamAttempt;
use App\Services\OnlineExamSubmissionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineExamController extends Controller
{
    public function start(OnlineExamAttempt $attempt): JsonResponse
    {
        if ($attempt->status === AttemptStatus::NOT_STARTED) {
            $attempt->update([
                'status' => AttemptStatus::IN_PROGRESS,
                'started_at' => Carbon::now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return response()->json([
            'message' => 'Exam started',
            'attempt' => $attempt,
        ]);
    }

    public function saveAnswer(Request $request, OnlineExamAttempt $attempt): JsonResponse
    {
        abort_if($attempt->status !== AttemptStatus::IN_PROGRESS, 403, 'Attempt is not active.');

        $answers = $attempt->answers ?? [];
        $questionId = $request->input('question_id');
        $answer = $request->input('answer');

        if ($questionId) {
            $answers[$questionId] = $answer;
            $attempt->update(['answers' => $answers]);
        }

        return response()->json([
            'status' => 'success',
            'saved' => true,
        ]);
    }

    public function submit(SubmitAttemptRequest $request, OnlineExamAttempt $attempt, OnlineExamSubmissionService $service): JsonResponse
    {
        abort_if($attempt->status !== AttemptStatus::IN_PROGRESS, 403, 'Attempt is not active.');

        $answers = $request->input('answers', $attempt->answers ?? []);
        $updatedAttempt = $service->submitAttempt($attempt, $answers, false, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'Attempt submitted successfully.',
            'total_obtained' => $updatedAttempt->total_obtained,
            'total_maximum' => $updatedAttempt->total_maximum,
            'percentage' => $updatedAttempt->percentage,
        ]);
    }
}