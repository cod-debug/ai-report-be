<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawDataModel extends Model
{
    //
    protected $table = 'raw_data';

    protected $fillable = [
        'manager',
        'supervisor',
        'agent',
        'day_contact_date',
        'week_contact_date',
        'month_contact_date',
        'days_to_recontact',
        'driver_level_1',
        'driver_level_2',
        'rcr_driver_category',
        'rcr_driver_level_1',
        'rcr_driver_level_2',
        'rcr_driver_l2_match',
        'recontacts_with_same_driver',
        'recontacts',
        'recontacts_eligible',
        'acw_duration_seconds_handled',
        'hold_duration_minutes_handled',
        'talk_duration_seconds_handled',
        'answered_handled',
        'answered',
        'csat',
        'csat_answered',
        'csat_score',
        'cres',
        'cres_answered',
        'cres_score',
        'kb_contact_searches',
        'kb_artilces_used',
        'agent_hangups',
        'make_good_amt',
        'next_steps_reason_l2'
    ];
}
