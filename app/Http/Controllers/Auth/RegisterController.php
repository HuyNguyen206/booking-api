<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Responsable\ResponseSuccess;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
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
}
