<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    //
    public function serverError($e){
        // Log the exception for debugging
        Log::error($e->getMessage());

        return response()->json([
            'status' => 'server_error',
            'message' => 'Something  went wrong. Please try again later.',
        ], 500);
    }

    public function validationError($errors){
        return response()->json([
            'message' => 'Validation failed!',
            'errors' => $errors,
        ], 422);
    }
}
