<?php

namespace App\Http\Controllers;

use App\Models\RawDataModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Gemini\Laravel\Facades\Gemini;
use Parsedown;

class ResponseController extends Controller
{
    //

    public function generateResponse(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'prompt' => 'required|min:3'
            ]);

            if ($validator->fails()) {
                // Handle validation failure
                return $this->validationError($validator->errors());
            }

            $prompt = $request->prompt;
            
            // conditions what function to call
            if(strtolower($prompt) === 'generate the fcr for supervisor 1'){
                $raw_data = $this->generateFcrForSupervisor('Supervisor 1');

                // ask a question ai base on the results of queried raw data
                $prompt_to_ai = "Base on this data: ".$raw_data.", can you generate a summary for FCR of supervisor 1.";
                $ai_response = $this->askAi($prompt_to_ai);

                return response()->json([
                    'graph' => false,
                    'raw_data' => $raw_data,
                    'ai_response' => $ai_response,
                ]);
            }
            
            // if prompt given is not available in our app
            return response()->json([
                'message' => 'This prompt is out my scope for now. Try something else.'
            ], 400);
        } catch (\Exception $e){
            return $this->serverError($e);
        }
    }

    private function askAi($prompt){
        try {
            $parsedown = new Parsedown();
            $result = Gemini::geminiPro()->generateContent($prompt);
            $text = $result->text();
            $html_format = $parsedown->text($text);
            
            return [
                'text' => $text,
                'html_format' => $html_format,
            ];
        } catch (\Exception $e) {
            return $this->serverError($e);
        }
    }

    private function generateFcrForSupervisor($supervisor){
        try {
            return RawDataModel::selectRaw("
                COALESCE(SUM(recontacts_with_same_driver), 0) AS total_recontacts_with_same_driver,
                COALESCE(SUM(recontacts), 0) AS total_recontacts,
                100 - (COALESCE(SUM(recontacts_with_same_driver), 0) / NULLIF(COALESCE(SUM(recontacts), 0), 0) * 100) AS recontact_percentage
            ")
            ->where('supervisor', $supervisor)
            ->first();
        } catch (\Exception $e) {
            return $this->serverError($e);
        }
    }
}
