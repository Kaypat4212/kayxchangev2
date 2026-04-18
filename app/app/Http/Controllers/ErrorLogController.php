<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErrorLogController extends Controller
{
    public function log(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string',
            'details' => 'nullable|array'
        ]);

        Log::error($data['message'], $data['details'] ?? []);

        return response()->json(['status' => 'success']);
    }
}