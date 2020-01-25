<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function pruebas(Request $request) {
        return "Accion de pruebas CategoryController";
    }

    public function index() {
        $categories = Category::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'categories' => $categories
        ]);
    }

    public function show($id) {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'categories' => $category
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No se encontro la categoria'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //RECOJER LOS DATOS POR POST
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);
        
        if(!empty($params_array))
        {
            //VALIDAR LOS DATOS

            $validate = \Validator::make($params_array,[
                'name' => 'required'
            ]);

            //GUARDAR LA CATEGORIA
            if($validate->fails()){
                $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha guardado la categoria'
                );
            }
            else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Se ha guardado la categoria correctamente',
                'category' => $category
                );
            }
        }
        else
        {
            $data = array(
            'code' => 404,
            'status' => 'error',
            'message' => 'No has enviado ninguna categoria'
            );
        }
        //DEVOLVER EL RESULTADO
        return response()->json($data, $data['code']);
        
    }
    public function update($id, Request $request) {
        //RECOJER LOS DATOS POR POST
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);
        
        if(!empty($params_array))
        {
            //VALIDAR LOS DATOS

            $validate = \Validator::make($params_array,[
                'name' => 'required'
            ]);

            //ACTUALIZAR LA CATEGORIA
            if($validate->fails()){
                $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha actualizado la categoria'
                );
            }
            else{
                
                unset($params_array['id']);
                unset($params_array['created_at']);
                
                $category_update = Category::where('id',$id)->update($params_array);
                
                $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Se ha actualizado la categoria correctamente',
                'category' => $params_array
                );
            }
        }
        else
        {
            $data = array(
            'code' => 404,
            'status' => 'error',
            'message' => 'No has enviado ninguna categoria para actualizar'
            );
        }
        //DEVOLVER EL RESULTADO
        return response()->json($data, $data['code']);
        
    }

}
