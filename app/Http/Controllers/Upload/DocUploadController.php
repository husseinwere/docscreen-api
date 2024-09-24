<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Mail\UploadRequested;
use App\Models\Upload\DocUpload;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DocUploadController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        $query = DocUpload::with('user');

        $userId = Auth::id();
        $user = User::find($userId);
        if($user->account_type == 1) {
            $query->where('user_id', $userId);
        }

        return $query->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'document' => 'required|mimes:pdf,doc,docx|max:8192'
        ]);
        $data = $request->all();

        $upload = [
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'email' => $data['email']
        ];

        if($request->hasFile('document')) {
            $document = $request->file('document');
            $path = $document->store('uploads', 'public');
            $docUrl = asset('storage/' . $path);
            $upload['document_url'] = $docUrl;
        }

        $createdUpload = DocUpload::create($upload);

        if($createdUpload){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function requestUpload(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required'
        ]);
        $data = $request->all();

        $user = User::find(Auth::id());

        $url = env('FRONTEND_URL') . "uploads/" . $user->slug . "?email=" . $data['email'] . "&name=" . $data['name'];
        
        $userDetails = [
            'name' => $data['name'],
            'email' => $data['email'],
            'organization' => $user->name,
            'url' => $url
        ];
        Mail::to($data['email'])->send(new UploadRequested($userDetails));

        return response(null, Response::HTTP_CREATED);
    }

    public function bulkRequestUpload(Request $request)
    {
        // Validate the uploaded file (check for Excel or CSV file types)
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Max file size is 2MB
        ]);

        // Store the uploaded file temporarily
        $file = $request->file('file');
        $filePath = $file->getPathname();

        // Read the Excel file
        try {
            // Load the uploaded Excel file
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Initialize an array to store rows and cells
            $excelData = [];

            // Iterate over the rows
            foreach ($sheet->getRowIterator() as $row) {
                // Initialize an array for each row
                $rowData = [];

                // Iterate over the cells in the current row
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Iterate over all cells, even empty ones

                foreach ($cellIterator as $cell) {
                    $cellValue = $cell->getValue(); // Get the cell value
                    $rowData[] = $cellValue; // Add the cell value to the row data array
                }

                // Add the row data to the main excelData array
                $excelData[] = $rowData;
            }

            //send emails
            for($i=0; $i<count($excelData); $i++) {
                if($i != 0) {
                    $row = $excelData[$i];
                    $name = $row[0];
                    $email = $row[1];

                    if($name && $email) {
                        $user = User::find(Auth::id());

                        $url = env('FRONTEND_URL') . "uploads/" . $user->slug . "?email=" . $email . "&name=" . $name;
                        
                        $userDetails = [
                            'name' => $name,
                            'email' => $email,
                            'organization' => $user->name,
                            'url' => $url
                        ];

                        Mail::to($email)->send(new UploadRequested($userDetails));
                    }
                }
            }

            return response(null, Response::HTTP_CREATED);
        }
        catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return response()->json([
                'message' => 'Error loading the file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $upload = DocUpload::find($id);
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
        $upload = DocUpload::find($id);

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
