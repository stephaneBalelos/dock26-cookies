<?php

namespace Dock26Cookies\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Acorn REST endpoint works 🚀'
        ], 200);
    }
}