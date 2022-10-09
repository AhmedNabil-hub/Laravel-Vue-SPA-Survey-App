<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Resources\SurveyResource;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;

class SurveyController extends Controller
{
	public function index(Request $request)
	{
		$user = $request->user();
		$surveys = Survey::where('user_id', $user->id)->paginate();

		return SurveyResource::collection($surveys);
	}

	public function store(StoreSurveyRequest $request)
	{
		$validated_date = $request->validated();

		if (isset($validated_date['image'])) {
			$image_path = $this->saveImage($validated_date['image']);
			$validated_date['image'] = $image_path;
		}

		$survey = Survey::create($validated_date);

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

	public function update(UpdateSurveyRequest $request, Survey $survey)
	{
		$validated_date = $request->validated();

		if (isset($validated_date['image'])) {
			$image_path = $this->saveImage($validated_date['image']);
			$validated_date['image'] = $image_path;

			if($survey->image) {
				$absolute_path = public_path($survey->image);
				File::delete($absolute_path);
			}
		}

		$survey->update($validated_date);

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

		if($survey->image) {
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
}
