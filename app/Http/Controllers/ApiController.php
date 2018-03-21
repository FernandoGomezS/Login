<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use JWTAuth;
use JWTFactory;
use JWTAuthException;
use App\User;
use GuzzleHttp\Client;
use Validator;

class ApiController extends Controller
{

	public function __construct()
	{
		$this->user = new User;
	}

	public function login(Request $request){
		$credentials = $request->only('email', 'password');		

		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);
		$response = $client->request('GET', 'users');

		
		$users=json_decode($response->getBody()->getContents());
		foreach ($users as $key => $user) {
			if($user->email==$credentials['email']){
				$password=$user->password;
				$name=$user->name;
				break; 
			}
		}

		if($credentials['password']==$password){


			$factory = JWTFactory::customClaims([
    'sub'   => env('API_ID'),
]);

$payload = $factory->make();
$token = JWTAuth::encode($payload);
return ['HTTP_Authorization' => "Bearer {$token}"];

		}
		else{

			return response()->json(['error' => 'invalid_credentials'], 401);
		}
		



		$token = null;
		try {
			if (!$token = JWTAuth::attempt($credentials)) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
		} catch (JWTAuthException $e) {
			 return response()->json(['error' => 'could_not_create_token'], 500);			
		}
		return response()->json([
			'response' => 'success',
			'result' => [
				'token' => $token,
			],
		]);
	}

	public function getAuthUser(Request $request){

		dd('hola');
		$user = JWTAuth::toUser($request->token);        
		return response()->json(['result' => $user]);
	}

	public function register(Request $request)
	{
		$credentials = $request->only('name', 'email', 'password');

		$rules = [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|'
		];
		$validator = Validator::make($credentials, $rules);
		if($validator->fails()) {
			return response()->json(['success'=> false, 'error'=> $validator->messages()]);
		}

		$name = $request->name;
		$email = $request->email;
		$password = $request->password;



		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);


		$response = $client->request('POST', 'users', ['json' => [			
			'name' => $name,
			'password' => $password,
			'email' => $email,
		]]);

		dd(json_decode($response->getBody()->getContents()));

		dd($response);
		return response()->json(['success'=> true, 'message'=> 'Thanks for signing up! Please check your email to complete your registration.']);
	}

	public function logout()
{
    JWTAuth::invalidate();
    return response([
            'status' => 'success',
            'msg' => 'Logged out Successfully.'
        ], 200);
}

}