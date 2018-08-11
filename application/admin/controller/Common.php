<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use auth\Auth;
class Common extends Controller
{

    public $config;

    public function  _initialize()
    {
        if(!session('uname')){
            $this->error('请先登录系统','Login/index');
        }
        $request=Request::instance();
        $module=$request->module();
        $con=$request->controller();
        $action=$request->action();
        $name=$module.'/'.$con.'/'.$action;
        $auth=new Auth();
        if(!$auth->check(strtolower($name),session('id'))){
            $this->error('没有该操作权限');
        }
        $this->assign('con',$con);
    }

}
