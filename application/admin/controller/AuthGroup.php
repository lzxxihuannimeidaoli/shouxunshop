<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class AuthGroup extends Controller
{
    /**
     * 数据列表
     * @Author：wpw < 1340747350@qq.com >
     */
	public function lst(){
        $AuthGroupRes=db('auth_group')->order('id DESC')->select();
        $this->assign([
            'AuthGroupRes'=>$AuthGroupRes
        ]);
        return view();
    }

    /**
     * 数据添加
     * @Author：wpw < 1340747350@qq.com >
     */
    public function add(){
        if(request()->isAjax()){
            $data=input('post.');
            //数据验证
            /*$validate = validate('Cate');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }*/
            if(db('auth_group')->insert($data)){
                return json(['code'=>1,'msg'=>'添加成功']);
            }else{
                return json(['code'=>2,'msg'=>'添加失败']);
            }
        }
        return view();
    }

    /**
     * 数据修改
     * @Author：wpw < 1340747350@qq.com >
     */
    public function edit(){
        if(request()->isGET()){
            $id=input('id');
            $groups=db('auth_group')->find($id);
            $this->assign([
                'groups'=>$groups,
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            $save=db('auth_group')->update($data);
            if($save==0){
                return json(['code'=>0,'msg'=>'没有修改任何信息']);
            }else if($save==1){
                return json(['code'=>1,'msg'=>'修改成功']);
            }else{
                return json(['code'=>2,'msg'=>'修改失败']);
            }
        }
    }

    /**
     * 数据删除
     * @Author：wpw < 1340747350@qq.com >
     */
    public function del(){
        if(request()->isAjax()){
            $id=input('id');
            $auth_group=db('auth_group');
            $cateTree=new Catetree();
            $sonids=$cateTree->childrenids($id,$auth_group);
            $sonids[]=intval($id);
            if($auth_group->delete($sonids)){
                return json(['code'=>1,'msg'=>'删除成功']);
            }else{
                return json(['code'=>0,'msg'=>'删除失败']);
            }

        }
    }

    /**
     * 分配权限
     * @Author：wpw < 1340747350@qq.com >
     */
    public function power(){
	    if(request()->isGet()){
	        //获取顶级规则
            $data=db('auth_rule')->where(['pid'=>0])->select();
            foreach($data as $k=>$v){
                $data[$k]['children']=db('auth_rule')->where(['pid'=>$v['id']])->select();
                foreach($data[$k]['children'] as $k1=>$v1){
                    $data[$k]['children'][$k1]['children']=db('auth_rule')->where(['pid'=>$v1['id']])->select();
                }
            }
	        $id=input('id');
            $auth_group=db('auth_group');
            $groups=$auth_group->find($id);
            $rules=explode(',',$groups['rules']);
            $this->assign([
                'data'=>$data,
                'rules'=>$rules,
                'groups'=>$groups,
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            if(empty($data['rules'])){
                $rules='';
            }else{
                $rules=implode(',',$data['rules']);
            }
            $save=db('auth_group')->where(['id'=>$data['id']])->update(['rules'=>$rules]);
            if($save!==false){
                return json(['code'=>1,'msg'=>"分配权限成功！"]);
            }else{
                return json(['code'=>0,'msg'=>'分配权限失败！']);
            }
        }
    }
}
