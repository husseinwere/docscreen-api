<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Employer\Employer;
use App\Models\Employer\RequestLog;
use Carbon\Carbon;
use Illuminate\Http\Response;

class RequestLogController extends Controller
{
    public function index(string $id)
    {
        return RequestLog::where('employer_id', $id)->latest()->limit(10)->get();
    }

    public function incrementRequestCount(string $id)
    {
        if(Employer::find($id)) {
            $currentMonth = Carbon::now()->format('Y-m');

            $log = RequestLog::where('employer_id', $id)->where('month', $currentMonth)->first();

            if ($log) {
                $log->increment('count');

                return response(null, Response::HTTP_CREATED);
            }
            else {
                $createdLog = RequestLog::create([
                    'employer_id' => $id,
                    'month' => $currentMonth,
                    'count' => 1
                ]);

                if($createdLog){
                    return response(null, Response::HTTP_CREATED);
                }
                else {
                    return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }
    }
}
