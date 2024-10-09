<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Mail\UploadRequested;
use App\Models\Employee\Employee;
use App\Models\Employee\SubmissionRequest;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SubmissionRequestController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $employee_id = $request->query('employee_id');

        return SubmissionRequest::where('employee_id', $employee_id)->where('status', 'ACTIVE')
                                ->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'document_title' => 'required'
        ]);
        $data = $request->all();

        $createdRequest = SubmissionRequest::create($data);

        if($createdRequest){
            $user = User::find(Auth::id());
            $employee = Employee::find($data['employee_id']);

            $url = env('FRONTEND_URL') . "employees/" . $data['employee_id'];
            
            $userDetails = [
                'name' => $employee->first_name,
                'email' => $employee->email,
                'organization' => $user->name,
                'url' => $url
            ];
            
            Mail::to($employee->email)->send(new UploadRequested($userDetails));

            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
