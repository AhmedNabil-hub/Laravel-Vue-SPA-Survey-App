<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
	public function register(Request $request)
	{
		$validated_data = $request->validate(
			[
				'name' => 'required|string',
				'email' => 'required|email|string|unique:users,email',
				'password' => [
					'required',
					'confirmed',
					Password::min(8)->mixedCase()->numbers()->symbols(),
				],
			],
		);

		$validated_data['password'] = bcrypt($validated_data['password']);

		$user = User::create($validated_data);
		$token = $user->createToken('main')->plainTextToken;

		return response([
			'user' => $user,
			'token' => $token,
		]);
	}

	public function login(Request $request)
	{
		$validated_data = $request->validate(
			[
				'email' => 'required|email|string|exists:users,email',
				'password' => [
					'required',
				],
				'remember' => 'boolean',
			],
		);

		$remember = $validated_data['remember'] ?? false;
		unset($validated_data['remember']);

		if (!auth()->attempt($validated_data, $remember)) {
			return response(
				[
					'error' => 'The provided credentials are not correct',
				],
				422
			);
		}

		$user = auth()->user();
		$token = $user->createToken('main')->plainTextToken;

		return response([
			'user' => $user,
			'token' => $token,
		]);
	}

	public function logout()
	{
		$user = auth()->user();

		$user->currentAccessToken()->delete();

		return response([
			'success' => true
		]);
	}
}
