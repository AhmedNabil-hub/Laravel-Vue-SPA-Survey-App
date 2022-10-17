<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveyAnswerResource;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function index(Request $request)
	{
		$user = $request->user();

		$total = Survey::query()
			->where('user_id', $user->id)
			->count();

		$latest = Survey::query()
			->where('user_id', $user->id)
			->latest('created_at')
			?->first();

		$total_answers = SurveyAnswer::query()
			->whereHas('survey', function($query) use ($user) {
				$query->where('user_id', $user->id);
			})
			->count();

		$latest_answers = SurveyAnswer::query()
			->whereHas('survey', function ($query) use ($user) {
				$query->where('user_id', $user->id);
			})
			->orderBy('end_date', 'DESC')
			->limit(5)
			->getModels('survey_answers.*');

		return [
			'total_surveys' => $total,
			'latest_survey' => $latest ? new SurveyResource($latest) : null,
			'total_answers' => $total_answers,
			'latest_answers' => SurveyAnswerResource::collection($latest_answers),
		];
	}
}
