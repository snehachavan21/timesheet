<?php
/**
 * Created by PhpStorm.
 * User: pruthvi
 * Date: 14/8/15
 * Time: 3:21 PM
 */

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;

class DesktopAppGrantType implements GrantTypeInterface
{

    private $userInfo;

    public function getQuerystringIdentifier()
    {
        return "desktop_app";
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        $query = DB::table('users')
            ->select('id', 'email', 'employee_id', 'joining_date', 'name')
            ->where('email', $request->request("email"));

        $data = $query->first();

        if ($data) {
            $this->userInfo = $data;
            $this->userInfo->user_id = $data->id;
            return true;
        }

        return false;
    }

    public function getClientId()
    {
        return null;
    }

    public function getUserId()
    {
        return $this->userInfo->id;
    }

    public function getScope()
    {
        return null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        //optimise array to return user object as oauth will return only access_token related data
        $userResponse = [
            'access_token' => $accessToken->createAccessToken($client_id, $user_id, $scope),
            'user' => $this->userInfo
        ];
        return $userResponse;
    }
}