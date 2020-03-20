<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;


class PruebasController extends Controller
{
    public function index(){
        $titulo = "Animales";
        $animales = ['Perro', 'Gato', 'Tigre', 'Leon'];
    
        return view('index',array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }
    
    public function testORM1(){
        $posts = Post::all();
        foreach($posts as $post){
            echo "<h1>Estos son los titulos</h1>";
            echo "<h1>".$post->title."</h1>";
            echo "<span style='color:gray;'>".$post->user->password."</span>";
            echo "<p>".$post->content."</p>";
        }
        die();
    }
    
    public function testORM(){
        $categories = Category::all();
        
        foreach($categories as $category){
            echo "<h1>Estos son los titulos</h1>";
            echo "<h1>".$category->name."</h1>";
            //echo "<span style='color:gray;'>".$post->user->password."</span>";
            foreach($category->posts as $post){
                echo "<h2>".$post->title."</h2>";
                echo "<p>".$post->content."</p>";
            }
        }
        die();
    }
    
}
