<?php
namespace app\admin\controller;
use think\Controller;
class Login extends Controller
{
	public function index(){
	    if(request()->isAjax()){
	        return model('admin')->login(input('post.'));
        }
        return view();
    }
}
