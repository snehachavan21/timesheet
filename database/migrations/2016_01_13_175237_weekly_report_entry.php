<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WeeklyReportEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_report_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('week');
            $table->integer('total_days');
            $table->integer('days_worked');
            $table->float('client_time');
            $table->float('internal_time');
            $table->float('rnd_time');
            $table->string('comments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('weekly_report_entries');
    }
}
