<?php

use Illuminate\Database\Seeder;

class OauthClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('oauth_clients')->insert(array(
            'client_id' => "web",
            'client_secret' => env('APP_KEY'),
            'redirect_uri' => "http://fake/",
        ));
    }
}
