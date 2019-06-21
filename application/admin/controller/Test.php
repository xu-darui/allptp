<?php
namespace app\admin\controller;

class Test extends Controller
{
    public function test($code)
    { 
        echo json_encode(["state"=>1,"message"=>"成功22222233333啦"]);
    }
}