<?php
namespace App\Http\Controllers;

class AdminApiController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Admin API']);
    }
}
