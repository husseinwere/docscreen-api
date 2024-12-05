<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Mail\UserCreated;
use App\Models\Employee\DocumentType;
use App\Models\Employer\Employer;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class EmployerController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        return Employer::where('status', 'ACTIVE')->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function store(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string|unique:users,email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
            'function' => 'required|string',
            'billing_email' => 'required|string',
            'kvk_number' => 'required|string',
            'organization' => 'required|string',
            'address' => 'required|string',
            'location' => 'required|string',
            'postcode' => 'required|string'
        ]);

        $password = str_pad(random_int(11, 9999), 4, '0', STR_PAD_LEFT);
        $fields['password'] = bcrypt($password);
        // $fields['password'] = bcrypt('1234');
        $fields['account_type'] = 'EMPLOYER';

        $user = User::create($fields);

        if($user) {
            $fields['user_id'] = $user->id;
            $employer = Employer::create($fields);

            $diplomaType = [
                'employer_id' => $employer->id,
                'title' => 'DUO DIPLOMA UITTREKSEL'
            ];
            DocumentType::create($diplomaType);

            $url = env('FRONTEND_URL') . "login";

            $accountCredentials = [
                'name' => $fields['first_name'],
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

        $employer = Employer::find($id);
        $updatedEmployer = $employer->update($data);

        if($updatedEmployer) {
            $user = User::find($employer->user_id);
            $user->email = $employer->email;
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
        $employer = Employer::find($id);
        $employer->status = 'DELETED';

        if($employer->save()) {
            $user = User::find($employer->user_id);
            $user->status = 'DELETED';
            $user->save();

            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
