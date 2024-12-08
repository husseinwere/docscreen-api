<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\UserCreated;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        //CHECK EMAIL
        $user = User::where('email', $fields['email'])->where('status', 'ACTIVE')->first();
        $user->account_type == 'ADMIN' ? $user->load('admin') : $user->load('employer');

        //CHECK PASSWORD
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Incorrect email or password'
            ], 401);
        }

        $token = $user->createToken('HussYana', ['*'], now()->addDay());
        $token = $token->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function logout(Request $request) {
        if($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response([
            'message' => 'Logged out'
        ], 201);
    }

    public function resetPassword(string $id)
    {
        $user = User::with(['admin', 'employer'])->where('id', $id)->first();
        
        $password = str_pad(random_int(11, 9999), 4, '0', STR_PAD_LEFT);
        $encryptedPassword = bcrypt($password);

        $user->password = $encryptedPassword;
        $userDetails = $user->account_type == 'ADMIN' ? $user->admin : $user->employer;
        $name = $user->account_type == 'ADMIN' ? $userDetails->name : $userDetails->first_name;

        if($user->save()) {
            $accountCredentials = [
                'name' => $name,
                'email' => $user->email,
                'password' => $password,
                'url' => env('FRONTEND_URL')
            ];
            Mail::to($user->email)->send(new UserCreated($accountCredentials));

            return response(null, 201);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
