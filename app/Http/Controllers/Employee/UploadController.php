<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee\SubmissionRequest;
use App\Models\Employee\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $employee_id = $request->query('employee_id');

        return Upload::where('employee_id', $employee_id)->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'document_title' => 'required',
            'document' => 'required|mimes:pdf,doc,docx|max:8192'
        ]);
        $data = $request->all();

        $upload = [
            'employee_id' => $data['employee_id'],
            'document_title' => $data['document_title'],
        ];

        if($request->hasFile('document')) {
            $document = $request->file('document');
            $path = $document->store('uploads', 'public');
            $docUrl = asset('storage/' . $path);
            $upload['document'] = $docUrl;
        }

        $createdUpload = Upload::create($upload);

        if($createdUpload){
            return response(null, Response::HTTP_CREATED);
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

        $upload = Upload::find($id);
        $updatedUpload = $upload->update($data);

        if($updatedUpload){
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
        $upload = Upload::find($id);

        $splitPath = explode('/', $upload->document_url);
        $docName = end($splitPath);
        Storage::delete('public/uploads/' . $docName);

        if($upload->delete()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
