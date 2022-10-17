<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'slug' => $this->slug,
			'status' => $this->status !== 'draft',
			'description' => $this->description,
			'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
			'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
			'expire_date' => Carbon::parse($this->expire_date)->format('Y-m-d H:i:s'),
			'image_url' => $this->image ? URL::to($this->image) : null,
			'questions' => SurveyQuestionResource::collection($this->questions),
			'questions_count' => $this->questions()->count() ?? 0,
		];
	}
}
