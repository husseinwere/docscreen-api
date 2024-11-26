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

            $url = $data['document_title'] == 'DIPLOMA CERTIFICATE' ? env('VERIFICATION_URL') . "?id=" . $data['employee_id'] : env('FRONTEND_URL') . "employees/" . $data['employee_id'];
            
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

    public function storeBulk(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'required|integer|exists:employees,id',
            'document_title' => 'required'
        ]);

        // Get the authenticated user
        $user = User::find(Auth::id());

        // Loop through each employee ID and create a submission request
        foreach ($request->employee_ids as $employee_id) {
            $data = [
                'employee_id' => $employee_id,
                'document_title' => $request->input('document_title')
            ];

            // Create a submission request for each employee
            $createdRequest = SubmissionRequest::create($data);

            if ($createdRequest) {
                $employee = Employee::find($employee_id);

                $url = $data['document_title'] == 'DIPLOMA CERTIFICATE' ? env('VERIFICATION_URL') . "?id=" . $employee_id : env('FRONTEND_URL') . "employees/" . $employee_id;

                $userDetails = [
                    'name' => $employee->first_name,
                    'email' => $employee->email,
                    'organization' => $user->name,
                    'url' => $url
                ];

                // Send email notification to each employee
                Mail::to($employee->email)->send(new UploadRequested($userDetails));
            }
        }

        // Return success response
        return response(null, Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $request = SubmissionRequest::find($id);
        $updatedRequest = $request->update($data);

        if($updatedRequest) {
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
