<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OAuthController extends Controller
{

    public function __construct()
    {
//        $this->middleware('oauth', ['except' => ['getOAuthToken', 'newAccessToken']]);
    }

    /**
     * @param Request $request
     * @return \OAuth2\HttpFoundationBridge\Response
     */
    public function getOAuthToken(Request $request) {
        // adding the client secret into the request object
        $requestArray = $request->all();
        $requestArray['client_secret'] = env('APP_KEY');
        $request->replace($requestArray);

        $bridgedRequest  = \OAuth2\HttpFoundationBridge\Request::createFromRequest($request->instance());
        $bridgedResponse = new \OAuth2\HttpFoundationBridge\Response();

        $bridgedResponse = \App::make('oauth2')->handleTokenRequest($bridgedRequest, $bridgedResponse);

        return $bridgedResponse;
    }

    /**
     * This function will take the client_id, client_secret, refresh_token, grant_type=refresh_token
     * and then return the token.
     * @param Request $request
     * @return new access token for user
     */
    public function newAccessToken(Request $request)
    {
        //added it to post as not injecting direclty in Request object
        $_POST['client_secret'] = env('APP_KEY');

        $bridgedRequest  = \OAuth2\HttpFoundationBridge\Request::createFromGlobals($request->instance());
        $bridgedRequest->headers->add([ 'client_secret' => env('APP_KEY')]);

        $bridgedResponse = new \OAuth2\HttpFoundationBridge\Response();
        $bridgedResponse =  \App::make('oauth2')->grantAccessToken($bridgedRequest, $bridgedResponse);

        if($bridgedResponse) {
            return response([
                'data' => [
                    'access_token' => $bridgedResponse['access_token'],
                    'refresh_token' => $bridgedResponse['refresh_token'],
                    'expires_in' => $bridgedResponse['expires_in']
                ],
                'message' => 'New access token',
                'flag' => true
            ], 201);
        }

        return response([
            'message' => 'Invalid refresh token',
            'flag' => false
        ], 500);
    }

    /**
     * function to delete access tokens from db
     * @param $userId
     * @return mixed
     */
    public function removeAccessToken($userId) {
        return DB::table('oauth_access_tokens')->where('user_id', $userId)->delete();
    }
}
