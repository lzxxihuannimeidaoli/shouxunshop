<?php
namespace app\admin\controller;
use think\Controller;
class Admin extends Controller
{
    /**
     * 管理员列表
     * @Author wpw < 1340747350@qq.com >
     */
    public function lst()
    {
        $adminRes=db('admin')->alias('a')->field('a.*,g.title')->join('auth_group g','a.groupid=g.id')->paginate(15);
        //获取所有用户组
        $this->assign([
            'adminRes'=>$adminRes,
        ]);
        return view();
    }
    /**
     * 添加管理员
     * @Author wpw < 1340747350@qq.com >
     */
    public function add()
    {
        if(request()->isAjax()){
            $data=input('post.');
            $data['create_time']=date('Y-m-d H:i:s');
            $data['password']=md5($data['password'].md5('wpw'));
            $add=db('admin')->insertGetId($data);
            $_data=[];
            $_data['uid']=$add;
            $_data['group_id']=$data['groupid'];
            $addGroupAccess=db('auth_group_access')->insert($_data);
            if($add && $addGroupAccess){
                return json(['code'=>1,'msg'=>'管理员添加成功']);
            }else{
                return json(['code'=>0,'msg'=>'管理员添加失败']);
            }
        }
        $group=db('auth_group')->select();
        $this->assign([
            'group'=>$group,
        ]);
        return view();
    }

    /**
     * 修改管理员
     * @Author wpw < 1340747350@qq.com >
     */
    public function edit(){
        if(request()->isGet()){
            $this->assign([
                'admins'=>db('admin')->find(input('id')),
                'group'=>db('auth_group')->select(),
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            $save=db('admin')->update($data);
            db('authGroupAccess')->where(array('uid'=>$data['id']))->update(['group_id'=>$data['groupid']]);
            if($save!==false){
                return json(['code'=>1,'msg'=>'修改成功']);
            }else{
                return json(['code'=>2,'msg'=>'修改失败']);
            }
        }
    }

    /**
     * 删除用户
     * @Author wpw < 1340747350@qq.com >
     */
    public function del()
    {
        if(request()->isAjax()){
            if(db('admin')->delete(input('id'))!==false)
               return json(['code'=>1,'msg'=>'删除成功']);
            return json(['code'=>2,'msg'=>'删除失败']);
        }else{
            return json(['code'=>3,'msg'=>'非法数据']);
        }
    }

    /**
     * 退出登录
     * @Author wpw < 1340747350@qq.com >
     */
    public function logout(){
        session(null);
        $this->success('退出成功！','Login/index');
    }

    
}
