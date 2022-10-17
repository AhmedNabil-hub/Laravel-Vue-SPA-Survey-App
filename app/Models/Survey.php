<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Sluggable\SlugOptions;

class Survey extends Model
{
	use HasFactory, HasSlug;

	protected $guarded = [];

	const TYPES = [
		'text',
		'textarea',
		'select',
		'radio',
		'checkbox',
	];

	public function getSlugOptions(): SlugOptions
	{
		return SlugOptions::create()
			->generateSlugsFrom('title')
			->saveSlugsTo('slug');
	}

	public function questions()
	{
		return $this->hasMany(SurveyQuestion::class);
	}

	public function surveyAnswers()
	{
		return $this->hasMany(SurveyAnswer:: class);
	}
}
