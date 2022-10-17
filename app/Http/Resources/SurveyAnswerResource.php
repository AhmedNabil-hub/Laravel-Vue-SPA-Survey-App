<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyAnswerResource extends JsonResource
{
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'survey' => new SurveyResource($this->survey),
			'end_date' => Carbon::parse($this->end_date)->format('Y-m-d H:i:s'),
		];
	}
}
