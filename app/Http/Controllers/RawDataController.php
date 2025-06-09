<?php

namespace App\Http\Controllers;

use App\Models\RawDataModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RawDataController extends Controller
{
    //
    public function sync(Request $request){
        $apiKey = config('services.google.api_key');
        $spreadsheetId = '1swilPRMD93TP5DI0YrxpXqpPzw8nvNZzYIkDeTpRHeQ';
        $range = 'Raw Data';
        
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}?key={$apiKey}";
        $response = Http::get($url);

        $data = $response->json()['values'];
        
        if (!empty($data)) {
            RawDataModel::truncate();
            foreach ($data as $key => $row) {
                // stop when manager is null, meaning no further data
                if(!$row[0]){
                    break;
                }
                // start at index 1
                if($key > 0){
                    $insert_data = [
                        'manager' => $row[0],
                        'supervisor' => $row[1],
                        'agent' => $row[2],
                        'day_contact_date' => substr($row[3], 0, 10),
                        'week_contact_date' => substr($row[4], 0, 10),
                        'month_contact_date' => substr($row[5], 0, 10),
                        'days_to_recontact' => $row[6] ?? null,
                        'driver_level_1' => $row[7] ?? null,
                        'driver_level_2' => $row[8] ?? null,
                        'rcr_driver_category' => $row[9] ?? null,
                        'rcr_driver_level_1' => $row[10] ?? null,
                        'rcr_driver_level_2' => $row[11] ?? null,
                        'rcr_driver_l2_match' => $row[12] ?? null,
                        'recontacts_with_same_driver' => $row[13] ?? null,
                        'recontacts' => $row[14] ?? null,
                        'recontacts_eligible' => $row[15] ?? null,
                        'acw_duration_seconds_handled' => $row[16] ?? null,
                        'hold_duration_minutes_handled' => $row[17] ?? null,
                        'talk_duration_seconds_handled' => $row[18] ?? null,
                        'answered_handled' => $row[19] ?? null,
                        'answered' => $row[20] ?? null,
                        'csat' => $row[21] ?? null,
                        'csat_answered' => $row[22] && $row[22] != '' ? $row[22] : null,
                        'csat_score' => $row[23] ?? null,
                        'cres' => $row[24] ?? null,
                        'cres_answered' => $row[25] && $row[25] != '' ? $row[25] : null,
                        'cres_score' => $row[26] && $row[26] != '' ? $row[26] : null,
                        'kb_contact_searches' => $row[27] ?? null,
                        'kb_artilces_used' => $row[28] ?? null,
                        'agent_hangups' => $row[29] ?? null,
                        'make_good_amt' => $row[30] && $row[30] != '' && is_numeric($row[30]) ? $row[30] : null,
                        'next_steps_reason_l2' => $row[31] ?? null
                    ];
                    
                    RawDataModel::create($insert_data);
                }
            }
        }
        
        // dd($response);
        if ($response->successful()) {
            return $response->json()['values'];
        } else {
            return 'Failed to fetch data';
        }
    }
}
