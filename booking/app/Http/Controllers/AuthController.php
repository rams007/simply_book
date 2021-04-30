<?php

namespace App\Http\Controllers;

use App\Helpers\JsonRpcClient;
use App\Models\User;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function doLogin(Request $request)
    {
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));
        $login = trim($request->email);
        $password = trim($request->password);
        try {
            $user = $client->getClientInfoByLoginPassword($login, $password);

            if ($user) {
                $userRecord = User::where('simply_book_id', $user->id)->first();
                if (!$userRecord) {
                    $userRecord = User::create([
                        'simply_book_id' => $user->id,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'client_hash' => $user->client_hash,
                        'openid_img' => $user->openid_img
                    ]);
                    $remeberToken = md5($userRecord->id . $userRecord->simply_book_id);
                    $userRecord->remember_token = $remeberToken;
                    $userRecord->save();
                }
            }
            $t = 1;
            return response()->json(['error' => false, 'msg' => 'Logined', 'userData' => $user]);
        } catch (\Throwable $e) {
            return response()->json(['error' => true, 'msg' => $e->getMessage()]);

        }


    }
}
