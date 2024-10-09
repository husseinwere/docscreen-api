<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserCreated;
use App\Models\Admin\Admin;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        return Admin::where('status', 'ACTIVE')->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function store(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
        ]);


        $password = str_pad(random_int(11, 9999), 4, '0', STR_PAD_LEFT);
        $fields['password'] = bcrypt($password);
        // $fields['password'] = bcrypt('1234');
        $fields['account_type'] = 'ADMIN';

        $user = User::create($fields);

        if($user) {
            $fields['user_id'] = $user->id;
            Admin::create($fields);

            $url = env('FRONTEND_URL') . "login";

            $accountCredentials = [
                'name' => $fields['name'],
                'email' => $user->email,
                'password' => $password,
                'url' => $url
            ];
            Mail::to($user->email)->send(new UserCreated($accountCredentials));

            return response(null, 201);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $admin = Admin::find($id);
        $updatedAdmin = $admin->update($data);

        if($updatedAdmin) {
            $user = User::find($admin->user_id);
            $user->email = $admin->email;
            $user->save();

            return response(null, Response::HTTP_OK);
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
        $admin = Admin::find($id);
        $admin->status = 'DELETED';

        if($admin->save()) {
            $user = User::find($admin->id);
            $user->status = 'DELETED';
            $user->save();

            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
