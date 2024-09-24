<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\UserCreated;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function store(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'phone' => 'required|string'
        ]);

        $createdBy = Auth::user();
        $fields['created_by'] = $createdBy->id;

        $fields['slug'] = $this->createSlug($fields['name']);

        $password = str_pad(random_int(11, 9999), 4, '0', STR_PAD_LEFT);
        $fields['password'] = bcrypt($password);
        // $fields['password'] = bcrypt('1234');

        $user = User::create($fields);

        if($user) {
            $accountCredentials = [
                'name' => $user->name,
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

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        //CHECK EMAIL
        $user = User::where('email', $fields['email'])->where('status', 'ACTIVE')->first();

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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $name = $request->query('name');

        $query = User::where('status', 'ACTIVE');

        if($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        
        return $query->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return User::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $user = User::find($id);
        $updatedUser = $user->update($data);

        if($updatedUser) {
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resetPassword(string $id)
    {
        $user = User::find($id);
        
        $password = str_pad(random_int(11, 9999), 4, '0', STR_PAD_LEFT);
        $encryptedPassword = bcrypt($password);

        $user->password = $encryptedPassword;

        if($user->save()) {
            $accountCredentials = [
                'name' => $user->name,
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->status = 'DELETED';

        if($user->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createSlug($string) {
        // Convert the string to lowercase
        $slug = strtolower($string);

        // Add random int at the end
        $slug .= " " . str_pad(random_int(11, 9999), 4, '0', STR_PAD_LEFT);
        
        // Replace any non-alphanumeric characters (excluding spaces) with nothing
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        
        // Replace multiple spaces or hyphens with a single space
        $slug = preg_replace('/[\s-]+/', ' ', $slug);
        
        // Replace spaces with hyphens
        $slug = preg_replace('/\s/', '-', $slug);
        
        // Trim any leading or trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }

    public function getUserBySlug(string $slug) {
        return User::where('slug', $slug)->where('status', 'ACTIVE')->first();
    }
    
}
