<?php

namespace App\Http\Controllers;

use DemeterChain\B;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Billetera;
use App\User;
use App\Token;
use Firebase\JWT\JWT;

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

    public function recargar(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        if(!empty($params_array)){
            $validator = \Validator::make($params_array,[
                'documento' => 'required|numeric|min:99999',
                'celular' => 'required|numeric|digits:10',
                'saldo' => 'required|numeric|min:10000|max:500000'
            ]);

            if($validator->fails()){
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => $validator->errors()
                ];
            }else{
                //Recargar saldo
                $documento = $params_array['documento'];
                $celular = $params_array['celular'];
                $saldo = $params_array['saldo'];

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
                    $billetera_user_id = $user['id'];
                    $billetera_user = Billetera::where('user_id', $billetera_user_id)->first();
                    $old_saldo = $billetera_user['saldo'];
                    $old_saldo = (int) $old_saldo;
                    $params_array['saldo'] += $old_saldo;
                    unset($params_array['documento']);
                    unset($params_array['celular']);
                    $new_billetera = Billetera::where('user_id', $billetera_user_id)->update($params_array);
                    $new_billetera = Billetera::where('user_id', $billetera_user_id)->first();
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'la consulta fue exitosa',
                        'user' => $user,
                        'billetera' => $new_billetera
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

    public function pagar(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        if(!empty($params_array)){
            $validator = \Validator::make($params_array,[
                'documento' => 'required|numeric|min:99999',
                'celular' => 'required|numeric|digits:10',
                'pagar' => 'required|numeric|min:10000|max:500000'
            ]);

            if($validator->fails()){
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => $validator->errors()
                ];
            }else{
                //Pagar
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
                    $billetera_user_id = $user['id'];
                    $billetera_user = Billetera::where('user_id', $billetera_user_id)->first();
                    $saldo = (int) $billetera_user['saldo'];
                    $pagar = $params_array['pagar'];

                    if($saldo >= $pagar){
                        //enviar comprobantes a email
                        $token = bin2hex(random_bytes(3));
                        $new_token = new Token();
                        $new_token->user_id = $user['id'];
                        $new_token->token = $token;
                        $new_token->save();
                        $jwt = [
                            'sub' => $user['id'],
                            'documento' => $params_array['documento'],
                            'celular' => $params_array['celular'],
                            'iar' => time(),
                            'exp' => time() + (60 * 15)
                        ];
                        $id_session = JWT::encode($jwt, 'llave', 'HS256');

                        // Envio de correo
                        $destino = $user['email'];
                        $asunto = "confirmacion para realizar pago";

                        //respuesta con id de sesion(jwt) y con token
                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'Pago: falta por confirmar',
                            'token' => $token,
                            'id_session' => $id_session
                        ];

                    }else{
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'no tiene saldo suficiente para reaizar el pago'
                        ];
                    }
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

    public function confirmar(Request $request){
        $jwt = $request->header('Auth');
        $id_session = JWT::decode($jwt, 'llave', 'HS256');
    }
}
