<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use App\Billetera;

class UserController extends Controller
{
    /*public function testOrm(){
        //$user = User::all()->load('billetera');
        $billetera = Billetera::where('id', '1')->first();
        //$userBilletera = User::where('documento', '71113528')->first();
        dd($billetera);
        return response()->json($userBilletera);
    }*/

    public function registro(Request $request){

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            //limpiar datos
            $params_array = array_map('trim', $params_array);

            $validator = \Validator::make($params_array, [
                'nombre' => 'required|alpha',
                'email'=> 'required|email|unique:users,email',
                'celular' => 'required|numeric|digits:10|unique:users,celular',
                'documento' => 'required|numeric|unique:users,documento'
            ]);

            if($validator->fails()){
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => $validator->errors()
                ];
            }else{
                // Guardar usuario en la base de datos y crear billetera del usuario
                $user = new User();
                $user->nombre = $params_array['nombre'];
                $user->celular = $params_array['celular'];
                $user->documento = $params_array['documento'];
                $user->email = $params_array['email'];
                $user->save();
                // crear billetera del usuario
                $userBilletera = User::where('documento', $params_array['documento'])->first();

                $billetera = new Billetera();
                $billetera->user_id = $userBilletera['id'];
                $billetera->saldo = 0;
                $billetera->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se guardaron los datos con exito',
                    'user' => $user,
                    'billetera' => $billetera
                ];
            }
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'no se enviaron datos o son incorrectos'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
