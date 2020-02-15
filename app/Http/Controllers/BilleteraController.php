<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Billetera;
use App\User;

class BilleteraController extends Controller
{
    public function consultar(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            $validator = \Validator::make($params_array,[
               'documento' => 'required|numeric|min:99999',
                'celular' => 'required|numeric|digits:10'
            ]);

            if($validator->fails()){
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => $validator->errors()
                ];
            }else{
                //consultar saldo
                $documento = $params_array['documento'];
                $celular = $params_array['celular'];

                $user = User::where(function ($query) use ($documento, $celular) {
                    $query->where('documento', $documento)
                           ->where('celular', $celular);
                })->first();
                if(is_null($user)){
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'la cedula o celular no se encuentran registrados'
                    ];
                }else{
                    $billetera_user = $user['id'];
                    $billetera_user = Billetera::where('user_id', $billetera_user)->first();
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'la consulta fue exitosa',
                        'user' => $user,
                        'billetera' => $billetera_user
                    ];
                }
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
