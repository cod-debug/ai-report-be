<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('raw_data', function (Blueprint $table) {
            $table->id();
            $table->string('manager');
            $table->string('supervisor');
            $table->string('agent');
            $table->string('day_contact_date')->nullable();
            $table->string('week_contact_date')->nullable();
            $table->string('month_contact_date')->nullable();
            $table->string('days_to_recontact')->nullable();
            $table->string('driver_level_1')->nullable();
            $table->string('driver_level_2')->nullable();
            $table->string('rcr_driver_category')->nullable();
            $table->string('rcr_driver_level_1')->nullable();
            $table->string('rcr_driver_level_2')->nullable();
            $table->string('rcr_driver_l2_match')->nullable();
            $table->integer('recontacts_with_same_driver')->nullable();
            $table->integer('recontacts')->nullable();
            $table->integer('recontacts_eligible')->nullable();
            $table->double('acw_duration_seconds_handled')->nullable();
            $table->double('hold_duration_minutes_handled')->nullable();
            $table->double('talk_duration_seconds_handled')->nullable();
            $table->double('answered_handled')->nullable();
            $table->double('answered')->nullable();
            $table->double('csat')->nullable();
            $table->double('csat_answered')->nullable();
            $table->double('csat_score')->nullable();
            $table->double('cres')->nullable();
            $table->double('cres_answered')->nullable();
            $table->double('cres_score')->nullable();
            $table->double('kb_contact_searches')->nullable();
            $table->double('kb_artilces_used')->nullable();
            $table->double('agent_hangups')->nullable();
            $table->double('make_good_amt')->nullable();
            $table->string('next_steps_reason_l2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_data');
    }
};
