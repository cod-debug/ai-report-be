<?php

namespace App\Http\Controllers;

use App\Models\RawDataModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;
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
            
            // conditions what function to call
            if(strtolower($prompt) === 'generate the fcr for supervisor 2'){
                $raw_data = $this->generateFcrForSupervisor('Supervisor 2');

                // ask a question ai base on the results of queried raw data
                $prompt_to_ai = "Base on this data: ".$raw_data.", can you generate a summary for FCR of supervisor 2.";
                $ai_response = $this->askAi($prompt_to_ai);

                return response()->json([
                    'graph' => false,
                    'raw_data' => $raw_data,
                    'ai_response' => $ai_response,
                ]);
            }

            // conditions what function to call
            if(strtolower($prompt) === 'generate quarter view of aht in bar graph'){
                $raw_data = $this->ahtQuarterView()->collect()->map(function ($quarter) {
                    $quarter->average_handling_time = number_format(($quarter->total_acw_duration_seconds + $quarter->total_hold_duration_minutes * 60  + $quarter->total_talk_duration_seconds) / $quarter->total_answered_calls / 60, 2);
                    return $quarter;
                });

                // (ACW Duration (H)+(Hold Duration (H)*60)+Talk Duration (H) (s)) / # Answered (H)/60
                // ask a question ai base on the results of queried raw data
                $prompt_to_ai = "Base on this data: ".$raw_data.", can you generate a summary quarter view of AHT. AHT means average handling time";
                $ai_response = $this->askAi($prompt_to_ai);

                return response()->json([
                    'graph' => 'bar',
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

    public function additionalPrompt(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'prompt' => 'required|min:3',
                'raw_data' => 'required'
            ]);

            if ($validator->fails()) {
                // Handle validation failure
                return $this->validationError($validator->errors());
            }

            $prompt = "Based on this raw_data: ".json_encode($request->raw_data).", please answer this user's additional prompt: ".$request->prompt;
            $ai_response = $this->askAi($prompt);

            return response()->json([
                'ai_response' => $ai_response,
            ]);
        } catch (\Exception $e) {
            return $this->serverError($e);
        }
    }

    private function askAi($prompt){
        try {
            $parsedown = new Parsedown();
            $client = new Client(config('gemini.api_key'));
            $result = $client->generativeModel(config('gemini.model'))->generateContent(
                new TextPart($prompt)
            );
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
                COALESCE(SUM(recontacts_eligible), 0) AS total_recontacts,
                COALESCE(SUM(answered), 0) AS total_answered_calls,
                COALESCE(100 - (COALESCE(SUM(recontacts_with_same_driver), 0) / NULLIF(COALESCE(SUM(recontacts_eligible), 0), 0) * 100), 0) AS recontact_percentage
            ")
            ->where('supervisor', $supervisor)
            ->first();
        } catch (\Exception $e) {
            return $this->serverError($e);
        }
    }
    
    private function ahtQuarterView(){
        try {
            return RawDataModel::selectRaw('YEAR(day_contact_date) as year, QUARTER(day_contact_date) as quarter')
            ->selectRaw('SUM(acw_duration_seconds_handled) as total_acw_duration_seconds')
            ->selectRaw('SUM(hold_duration_minutes_handled) as total_hold_duration_minutes')
            ->selectRaw('SUM(talk_duration_seconds_handled) as total_talk_duration_seconds')
            ->selectRaw('SUM(answered_handled) as total_answered_calls')
            ->groupByRaw('YEAR(day_contact_date), QUARTER(day_contact_date)')
            ->orderByRaw('YEAR(day_contact_date), QUARTER(day_contact_date)')
            ->get();
        } catch (\Exception $e) {
            return $this->serverError($e);
        }
    }
}
