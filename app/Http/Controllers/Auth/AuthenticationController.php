<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Responsable\ResponseError;
use App\Responsable\ResponseSuccess;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email', Rule::unique('users')],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'numeric',
                Rule::in(Role::query()->whereIn('name', ['Property Owner', 'Simple User'])->get()->pluck('id')->toArray())]
        ]);
        /**
         * @var User $user
         */
        $user = User::create(Arr::except($data, ['password', 'password_confirmation']) + ['password' => bcrypt($data['password'])]);

        return new ResponseSuccess([
            'access_token' => $user->createToken('client')->plainTextToken
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'max:255', 'email'],
            'password' => ['required', 'string', Password::defaults()],
        ]);
        /**
         * @var User $user
         */
        $user = User::query()->where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return new ResponseError('The credential is invalid', statusCode: Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('client')->plainTextToken;

        return new ResponseSuccess(['access_token' => $token]);
    }
}
