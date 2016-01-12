<?php

use Illuminate\Database\Migrations\Migration;

class CreateTicketTimeCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function ($table) {
            $table->float('time_spend')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function ($table) {
            $table->dropColumn('time_spend');
        });
    }
}
