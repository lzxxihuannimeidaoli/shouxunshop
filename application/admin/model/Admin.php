<?php
namespace app\admin\model;
use think\Model;

class Admin extends Model
{
   public function login($data)
   {
       $uname=$data['uname'];
       $admins=Admin::get(['uname'=>$uname]);
       $password=md5($data['password'].md5('wpw'));
       if($admins){
            if($admins['status']==0){
                return json(['code'=>0,'msg'=>'该账号以被禁用']);
            }
            if($admins['password']!==$password){
                return json(['code'=>3,'msg'=>'密码不正确']);
            }
            session('uname',$uname);
            session('id',$admins['id']);
            db('admin')->where('uname', $uname)->update(['last_time' => date('Y-m-d H:i:s')]);
            return json(['code'=>1,'msg'=>'认证成功，正在跳转...']);
       }else{
           return json(['code'=>2,'msg'=>'用户名不存在']);
       }
   }
}
