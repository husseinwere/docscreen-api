<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee\Employee;
use App\Models\Employer\Employer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $is_internal = $request->query('is_internal');

        $employer = Employer::where('user_id', Auth::id())->first();

        $query = Employee::where('employer_id', $employer->id)->where('status', 'ACTIVE');

        if($is_internal) {
            $is_internal = filter_var($is_internal, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_internal', $is_internal);
        }
        
        return $query->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function show(string $id)
    {
        return Employee::find($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required',
            'email' => 'required',
            'is_internal' => 'required'
        ]);
        $data = $request->all();

        $employer = Employer::where('user_id', Auth::id())->first();
        $data['employer_id'] = $employer->id;

        $createdEmployee = Employee::create($data);

        if($createdEmployee){
            return response($createdEmployee, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'is_internal' => 'required',
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Max file size is 2MB
        ]);

        // Store the uploaded file temporarily
        $file = $request->file('file');
        $filePath = $file->getPathname();

        $employer = Employer::where('user_id', Auth::id())->first();
        $employer_id = $employer->id;

        // Read the Excel file
        try {
            $data = $request->all();

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

            //store employees
            for($i=0; $i<count($excelData); $i++) {
                if($i != 0) {
                    $row = $excelData[$i];
                    $first_name = $row[0];
                    $last_name = $row[1];
                    $email = $row[2];
                    $dob = $row[3];

                    if($first_name && $last_name && $email && $dob) {  
                        $is_internal = filter_var($data['is_internal'], FILTER_VALIDATE_BOOLEAN);
                        $employee = [
                            'employer_id' => $employer_id,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'dob' => $dob,
                            'is_internal' => $is_internal
                        ];

                        Employee::create($employee);
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

        $employee = Employee::find($id);
        $updatedEmployee = $employee->update($data);

        if($updatedEmployee){
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
        $employee = Employee::find($id);
        $employee->status = 'DELETED';
        if($employee->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
