<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show','getImage','getPostsByCategory','getPostsByUser']]);
    }

    public function index() {
        $posts = Post::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'posts' => $posts
                        ], 200);
    }

    public function show($id) {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'posts' => $post
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => "La entrada no existe"
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //RECOJER LOS DATOS POR POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);        
        if (!empty($params_array)) {
            //CONSEGUIR USUARIO IDENTIFICADO
            $user = $this->getIdentity($request);

            //VALIDAR LOS DATOS

            $validate = \Validator::make($params_array, [
                        'category_id' => 'required',
                        'title' => 'required',
                        'content' => 'required',
                        'image' => 'required'
            ]);

            //GUARDAR LA CATEGORIA
            if ($validate->fails()) {
                $data = array(
                    'code' => 404,
                    'status' => 'errorStorage',
                    'message' => 'No se ha guardado el post'
                );
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params_array['category_id'];
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                $post->image = $params_array['image'];
                $post->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Se ha guardado el post correctamente',
                    'post' => $post
                );
            }
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No has enviado ningun post'
            );
        }
        //DEVOLVER EL RESULTADO
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //RECOJER LOS DATOS POR POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        //ARREGLO POR DEFECTO
        $data = array(
            'code' => 404,
            'status' => 'error',
            'message' => 'ERROR AL INICIAR LA APLICACION'
        );

        if (!empty($params_array)) {
            //VALIDAR LOS DATOS

            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required'
            ]);

            //ACTUALIZAR LA CATEGORIA
            if ($validate->fails()) {
                $data = array(
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'No se ha actualizado el post'
                );
            } else {

                unset($params_array['id']);
                unset($params_array['user_id']);
                //unset($params_array['category_id']);
                unset($params_array['created_at']);

                $user = $this->getIdentity($request);
                $where = [
                    'id' => $id,
                    'user_id' => $user->sub
                ];
                $post = Post::where($where)->update($params_array);

                if (empty($post)) {

                    $data = array(
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'No se actualizo el post'
                    );
                } else {
                   $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Se ha actualizado la categoria correctamente',
                        'post' => $post,
                        'category' => $params_array
                    );
                }
            }
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No has enviado ningun post para actualizar'
            );
        }
        //DEVOLVER EL RESULTADO
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {

        //CONSEGUIR EL USUARIO IDENTIFICADO
        $user = $this->getIdentity($request);

        //CONSEGUIR EL POST
        $post = Post::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if (empty($post)) {

            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'el post no existe'
            );
        } else {
            $post->delete();
            $data = array(
                'code' => 200,
                'status' => 'succes',
                'post' => $post,
                'message' => 'el post ha sido eliminado exitosamente'
            );
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity($request) {

        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request) {
        //RECOJER LA IMAGEN DE LA PETICION
        $image = $request->file('file0');
        
        //VALIDAR LA IMAGEN
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if( !$image ){
            if( $validate->fails()){
                $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al subir la imagen+ValidateFails'
                );
            }
            else{
                $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al subir la imagenOnlyImageError'
                );
            }
        }
        else{
            $image_name = time().$image->getClientOriginalName();
            
            \Storage::disk('images')->put($image_name,\File::get($image));
            
            $data = array(
            'code' => 200,
            'status' => 'successs',
            'image' => $image_name
            );
        }
        //GUARDAR LA IMAGEN
        return response()->json($data, $data['code']);
        
    }
    public function getImage($filename){
       //COMPROBAR SI EXISTE
       $isset = \Storage::disk('images')->exists($filename);
       if($isset){
           //CONSEGUIR IMAGEN
           $file = \Storage::disk('images')->get($filename);
           //DEVOLVER IMAGEN
           return new Response($file,200);
       }else{
           $data = array(
           'code' => 404,
           'status' => 'error',
           'message' => 'file no exist'
           );
       }
       return response()->json($data,$data['code']);
    }
    public function getPostsByCategory($id){
        $posts = Post::where('category_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }
    
    public function getPostsByUser($id){
        $posts = Post::where('user_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }
}
