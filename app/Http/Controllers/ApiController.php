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
use Hash;

class ApiController extends Controller
{

	public function __construct()
	{
		$this->user = new User;
	}

	public function login(Request $request){
		//Email y password
		$credentials = $request->only('email', 'password');		

		//busqueda  de Usuarios en Api mockapi
		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);
		$response = $client->request('GET', 'users');

		//buscamos al usuario por el email
		$users=json_decode($response->getBody()->getContents());
		foreach ($users as $key => $user) {
			//encontramos al usuario 
			if($user->email==$credentials['email']){
				$password=$user->password;
				$name=$user->name;
				$id=$user->ID;	
       			//comprobamos contraseña encriptada
				if( Hash::check($credentials['password'], $password)){
					$search=User::where('email',$credentials['email'])->get();
					if($search->count()==0){
						$user= new User;
						$user->name=$name;
						$user->password=$password;
						$user->email=$credentials['email'];
						$user->id=$id;
						$user->save();
					}
					$token = null;
					try {
						if (!$token = JWTAuth::attempt($credentials)) {
					//no son validas las credenciales
							return response()->json(['error' => 'invalid_credentials'], 401);
						}
					} catch (JWTAuthException $e) {
					//no se pudo crear el token
						return response()->json(['error' => 'could_not_create_token'], 500);			
					}
					//todo ok envio de token
					return response()->json([
						'response' => 'success',
						'result' => ['Authorization' => "Bearer {$token}"],
					]);
				}
				else{
				//no son validas las credenciales
					return response()->json(['error' => 'invalid_credentials'], 401);
				}
			}
		}
		//no son validas las credenciales
		return response()->json(['error' => 'invalid_credentials'], 401);


	}

	public function getUser(Request $request){

		$user = JWTAuth::toUser($request->token); 
		return response()->json(['response' => 'success','result' => ['name' => $user->name ,'email'=> $user->email , 'password'=>$user->password] ]);
	}

	public function register(Request $request)
	{
		$credentials = $request->only('name', 'email', 'password');
		//validaciones
		$rules = [
			'name' => 'required|max:255',
			'password' => 'required|max:255',
			'email' => 'required|email|max:255|'
		];
		$validator = Validator::make($credentials, $rules);
		if($validator->fails()) {
			return response()->json(['success'=> false, 'error'=> $validator->messages()]);
		}
		//traemos todos los users
		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);
		$response = $client->request('GET', 'users');

		//validamos al usuario si ya existe por el email
		$users=json_decode($response->getBody()->getContents());
		foreach ($users as $key => $user) {
			//encontramos al usuario 
			if($user->email==$credentials['email']){
				return response()->json(['success'=> false, 'error'=> 'There is already a user with this email.']);
			}
		}

		//obtenemos los datos
		$name = $request->name;
		$email = $request->email;
		$password = $request->password;
		//accedemos al cliente api
		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);

		//realizamos peticion de post a la api de users
		$response = $client->request('POST', 'users', ['json' => [			
			'name' => $name,
			'password' => bcrypt($password),
			'email' => $email,
		]]);
		return response()->json(['success'=> true, 'message'=> 'Thanks for signing up! ']);
	}

	public function logout(Request $request)
	{
		$user = JWTAuth::toUser($request->token); 
		JWTAuth::invalidate();
		$user->delete();
		return response([
			'status' => 'success',
			'msg' => 'Logged out Successfully.'
		], 200);
	}

	public function updateUser(Request $request)
	{
		// datos 		
		$user = JWTAuth::toUser($request->token);
		$request= request()->all();
		//traemos todos los users
		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);
		$response = $client->request('GET', 'users');

		//validamos al usuario si ya existe por el email
		$users=json_decode($response->getBody()->getContents());
		foreach ($users as $key => $user) {
			//encontramos al usuario 
			if($user->email==$request['email']){
				return response()->json(['success'=> false, 'error'=> 'There is already a user with this email.']);
			}
		}
		
		//cambiamos a los nuevos datos
		if( isset($request['name'])){
			$user->name=$request['name'];
		}
		if( isset($request['email'])){
			$user->email=$request['email'];
		}
		if( isset($request['password'])){
			$user->password=bcrypt($request['password']);
		}		
		//actualizamos al usuario
		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);

		//realizamos peticion de put a la api de user
		$response = $client->request('PUT', 'users/'.$user->id, ['json' => [			
			'name' => $user->name,
			'password' => $user->password,
			'email' => $user->email,
		]]);	

		return response([
			'status' => 'success',
			'msg' => 'Updated User!.'
		], 200);

	}

	public function deleteUser(Request $request)
	{
		$user = JWTAuth::toUser($request->token); 
		//Borrar al usuario
		$client = new Client([ 
			'base_uri' => 'http://5ab1f27462a6ae001408c1aa.mockapi.io/',
			'timeout'  => 2.0,
		]);

		//realizamos peticion de delete a la api de user
		$response = $client->request('DELETE', 'users/'.$user->id);	
		
		JWTAuth::invalidate();
		$user->delete();
		return response([
			'status' => 'success',
			'msg' => 'User Deleted.'
		], 200);
	}

}