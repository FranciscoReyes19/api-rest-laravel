<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de pruebas UserController";
    }
    
    public function register(Request $request){
        
        //RECOJER LOS DATOS POR POST
        $json = $request->input('json', null);
        
        $params = json_decode($json);
        $params_array = json_decode($json,true);
        //LIMPIAR DATOS
        if(!empty($params) && !empty($params_array))
        {
            $params_array = array_map('trim',$params_array);
            //VALIDAR LOS DATOS
            $validate = \Validator::make($params_array, [
               'name'         => 'required|alpha',
               'surname'      => 'required|alpha',
               'email'        => 'required|email|unique:users',
               'password'     => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha creado',
                'errors' => $validate->errors()
                );
            }
            else{
                //CIFRAR CONTRASEÑA
                $pwd = hash('sha256',$params->password);
                
                 //CREAR EL USUARIO
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->role = 'ROLE_USER';
                $user->password = $pwd;

                $user->save();
                
                $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El usuario se ha creado correctamente',
                'user' => $user
                );
            }
        }
        else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
                );
        }
            
            //COMPROBAR SI EXISTE EL USUARIO
            //EL ORM LO HACE
            
            //DEVOLVER EN JSON
            return response()->json($data,$data['code']);
        
    }
    
    public function login(Request $request){
        
        $jwtAuth = new \JwtAuth();
        
        //RECIBIR DATOS POR POST
        $json = $request->input('json', null);
        
        $params = json_decode($json);
        $params_array = json_decode($json,true);
        
        if(!empty($params) && !empty($params_array))
        {
        //LIMPIEZA DEL ARRAY
        $params_array = array_map('trim',$params_array);
        
        //VALIDAR ESOS DATOS
            $validate = \Validator::make($params_array, [
                   'email'        => 'required|email',
                   'password'     => 'required'
                ]);

                if($validate->fails()){
                    $signup = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha podido identificar',
                    'errors' => $validate->errors()
                    );
                }
                else{
                        //CIFRAR LA CONTRASEÑA
                        $pwd = hash('sha256', $params->password);
                        //DEVOLVER TOKEN O DATOS
                        $signup = $jwtAuth->signup($params->email,$pwd);

                        if(!empty($params->gettoken)){
                            $signup = $jwtAuth->signup($params->email,$pwd,true);
                        }
                    }
        }
        else{
            $signup = array(
               'status' => 'error',
               'code' => 404,
               'message' => 'Los datos enviados no son correctos'
            );
        }
                
        return response()->json($signup,200);
    }
    
    public function update(Request $request){
        
        //COMPROBAR SI EL USUARIO ESTA IDENTIFICADO
        
        $token = $request->header('Authorization');
        $JwtAuth = new \JwtAuth();
        $checkToken = $JwtAuth->checkToken($token);
        
            
            //RECOJER LOS DATOS POR POST
            $json = $request->input('json',null);
            $params_array = json_decode($json,true);
        
            if($checkToken && !empty($params_array)){
            
            //OBTENER USUARIO IDENTIFICADO
            $user = $JwtAuth->checkToken($token,true);

            //VALIDAR LOS DATOS
            $validate = \Validator::make($params_array,[
               'name'         => 'required|alpha',
               'surname'      => 'required|alpha',
               'email'        => 'required|email|unique:users,id,'.$user->sub
            ]);
            
            if($validate->fails()){
                    $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'La validacion ha fallado',
                    'errors' => $validate->errors()
                    );
                }
                else{
                    //QUITAR LOS CAMPOS QUE NO QUIERO ACTUALIZAR

                    unset($params_array['id']);
                    unset($params_array['role']);
                    unset($params_array['password']);
                    unset($params_array['created_at']);
                    unset($params_array['remember_token']);


                    //ACTUALIZAR LOS DATOS EN LA DB

                    $user_update = User::where('id',$user->sub)->update($params_array);

                    //DEVOLVER ARRAY COMO RESULTADO
                    $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'user' => $user,
                    'changes' => $params_array
                    );
                }
                
        }
        else{
            $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'El usuario no esta identificado.'
            );
        }
        
        return response()->json($data,$data['code']);
    }
    
    public function upload(Request $request){
        
        //RECOJER LOS DATOS DEL LA PETICION
        $image = $request->file('file0');
        
        //Validar la imagen
        $validate = \Validator::make($request->all(),[
           'file0' => 'required|image|mimes:jpg,jpeg,png,gif' 
        ]);
        
        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.'
                );    
        }
        else{
            
        //GUARDAR LA IMAGEN
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name,\File::get($image));
            
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
            
        }
        
        return response()->json($data,$data['code']);
    }
    public function getImage($filename){
        
        $isset = \Storage::disk('users')->exists($filename);
        
        if($isset){
            $file = \Storage::disk('users')->get($filename);
            
            return new Response($file,200);
        }
        else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe.'
                );    
        }
        
        return response()->json($data,$data['code']);
    }
    
    public function detail($id){
        
        $user = User::find($id);
        
        if(is_object($user)){
            $data = array(
            'code' => 200,
            'status' => 'success',
            'user' => $user
            );
        }
        else{
            $data = array(
            'code' => 404,
            'status' => 'error',
            'message' => 'El usuario no existe'
            );
        }
        
        return response()->json($data,$data['code']);
    }
    
}