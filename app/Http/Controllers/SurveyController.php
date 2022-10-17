<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyAnswerRequest;
use App\Models\Survey;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SurveyQuestion;
use Illuminate\Support\Facades\File;
use App\Http\Resources\SurveyResource;
use App\Http\Requests\StoreSurveyRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdateSurveyRequest;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestionAnswer;

class SurveyController extends Controller
{
	public function index(Request $request)
	{
		$user = $request->user();
		$surveys = Survey::where('user_id', $user->id)->paginate(5);

		return SurveyResource::collection($surveys);
	}

	public function store(StoreSurveyRequest $request)
	{
		$validated_data = $request->validated();

		if (isset($validated_data['image'])) {
			$image_path = $this->saveImage($validated_data['image']);
			$validated_data['image'] = $image_path;
		}

		$survey = Survey::create(Arr::except($validated_data, ['questions']));

		foreach ($validated_data['questions'] as $question) {
			$question['survey_id'] = $survey->id;
			$this->createQuestion($question);
		}

		return new SurveyResource($survey);
	}

	public function show(Survey $survey)
	{
		$user = request()->user() ?? null;
		if (!$user || $user->id !== $survey->user_id) {
			return abort(
				403,
				'Unauthorized action.',
			);
		}

		return new SurveyResource($survey);
	}

	public function showPublic(Survey $survey)
	{
		return new SurveyResource($survey);
	}

	public function update(UpdateSurveyRequest $request, Survey $survey)
	{
		$validated_data = $request->validated();

		if (isset($validated_data['image'])) {
			$image_path = $this->saveImage($validated_data['image']);
			$validated_data['image'] = $image_path;

			if ($survey->image) {
				$absolute_path = public_path($survey->image);
				File::delete($absolute_path);
			}
		}

		$survey->update(Arr::except($validated_data, ['questions']));

		$existing_ids = $survey->questions()->pluck('id')->toArray();
		$new_ids = Arr::pluck($validated_data['questions'], 'id');

		$to_delete = array_diff($existing_ids, $new_ids);
		$to_add = array_diff($new_ids, $existing_ids);

		SurveyQuestion::destroy($to_delete);

		foreach ($validated_data['questions'] as $question) {
			if (in_array($question['id'], $to_add)) {
				$question['survey_id'] = $survey->id;
				$this->createQuestion($question);
			}
		}

		$question_map = collect($validated_data['questions'])->keyBy('id');
		foreach ($survey->questions as $question) {
			if (isset($question_map[$question->id])) {
				$this->updateQuestion($question, $question_map[$question->id]);
			}
		}

		return new SurveyResource($survey);
	}

	public function destroy(Survey $survey)
	{
		$user = request()->user() ?? null;
		if (!$user || $user->id !== $survey->user_id) {
			return abort(
				403,
				'Unauthorized action.',
			);
		}

		if ($survey->image) {
			$absolute_path = public_path($survey->image);
			File::delete($absolute_path);
		}

		$survey->delete();

		return response(
			'',
			204
		);
	}

	private function saveImage($image)
	{
		if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
			$image = substr($image, strpos($image, ',') + 1);
			$type = strtolower($type[1]);

			if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
				throw new \Exception('invalid image type');
			}

			$image = str_replace(' ', '+', $image);
			$image = base64_decode($image);

			if ($image === false) {
				throw new \Exception('image decoding failed');
			}
		} else {
			throw new \Exception('image not valid');
		}

		$dir = 'images/';
		$file = Str::random() . '.' . $type;
		$absolute_path = public_path($dir);
		$relative_path = $dir . $file;

		if (!File::exists($absolute_path)) {
			File::makeDirectory($absolute_path, 0755, true);
		}

		file_put_contents($relative_path, $image);

		return $relative_path;
	}

	private function createQuestion($data)
	{
		if (is_array($data['data'])) {
			$data['data'] = json_encode($data['data']);
		}

		$validator = Validator::make(
			$data,
			[
				'question' => 'required|string',
				'type' => 'required|in:' . implode(',', Survey::TYPES),
				'description' => 'nullable|string',
				'data' => 'present',
				'survey_id' => 'exists:surveys,id',
			],
		);

		$validated_data = $validator->validated();

		return SurveyQuestion::create($validated_data);
	}

	private function updateQuestion(SurveyQuestion $surveyQuestion, $data)
	{
		if (is_array($data['data'])) {
			$data['data'] = json_encode($data['data']);
		}

		$validator = Validator::make(
			$data,
			[
				'id' => 'exists:survey_questions,id',
				'question' => 'required|string',
				'type' => 'required|in:' . implode(',', Survey::TYPES),
				'description' => 'nullable|string',
				'data' => 'present',
				// 'survey_id' => 'exists:surveys,id',
			],
		);

		$validated_data = $validator->validated();

		return $surveyQuestion->update($validated_data);
	}

	public function storeAnswer(StoreSurveyAnswerRequest $request, Survey $survey)
	{
		$validated_data = $request->validated();

		$survey_answer = SurveyAnswer::create([
			'survey_id' => $survey->id,
			'start_date' => date('Y-m-d H:i:s'),
			'end_date' => date('Y-m-d H:i:s'),
		]);

		foreach ($validated_data['answers'] as $question_id => $answer) {
			$question = SurveyQuestion::query()
				->where([
					'id' => $question_id,
					'survey_id' => $survey->id
				])
				->get();

			if (!$question) {
				return response(
					"Invalid question ID: \"$question_id\"",
					400,
				);
			}

			$data = [
				'survey_question_id' => $question_id,
				'survey_answer_id' => $survey_answer->id,
				'answer' => is_array($answer) ? json_encode($answer) : $answer,
			];

			SurveyQuestionAnswer::create($data);
		}

		return response(
			"",
			201,
		);
	}
}
