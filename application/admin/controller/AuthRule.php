<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class AuthRule extends Controller
{
	public function lst(){
        $tree=new Catetree();
        $AuthRuleRes=db('auth_rule')->order('id DESC')->select();
        $AuthRuleRes=$tree->catetree($AuthRuleRes);
        $this->assign([
            'AuthRuleRes'=>$AuthRuleRes
        ]);
        return view();
    }
    public function add(){
        if(request()->isAjax()){
            $data=input('post.');
            //数据验证
            /*$validate = validate('Cate');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }*/
            if(db('auth_rule')->insert($data)){
                return json(['code'=>1,'msg'=>'添加成功']);
            }else{
                return json(['code'=>2,'msg'=>'添加失败']);
            }
        }
        $tree=new Catetree();
        $AuthRuleRes=db('auth_rule')->order('id DESC')->select();
        $AuthRuleRes=$tree->catetree($AuthRuleRes);
        $this->assign([
            'AuthRuleRes'=>$AuthRuleRes
        ]);
        return view();
    }

    public function edit(){
        if(request()->isGET()){
            $id=input('id');
            $rules=db('auth_rule')->find($id);
            $tree=new Catetree();
            $AuthRuleRes=db('auth_rule')->order('id DESC')->select();
            $AuthRuleRes=$tree->catetree($AuthRuleRes);
            $this->assign([
                'rules'=>$rules,
                'AuthRuleRes'=>$AuthRuleRes
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            $save=db('auth_rule')->update($data);
            if($save==0){
                return json(['code'=>0,'msg'=>'没有修改任何信息']);
            }else if($save==1){
                return json(['code'=>1,'msg'=>'修改成功']);
            }else{
                return json(['code'=>2,'msg'=>'修改失败']);
            }
        }
    }

    public function del(){
        if(request()->isAjax()){
            $id=input('id');
            $auth_rule=db('auth_rule');
            $cateTree=new Catetree();
            $sonids=$cateTree->childrenids($id,$auth_rule);
            $sonids[]=intval($id);
            if($auth_rule->delete($sonids)){
                return json(['code'=>1,'msg'=>'删除成功']);
            }else{
                return json(['code'=>0,'msg'=>'删除失败']);
            }
        }
    }

    public function ajaxlst(){
        if(request()->isAjax()){
            $ruleid=input('ruleid');
            $cateTree=new Catetree();
            $auth_rule=db('auth_rule');
            $sonids=$cateTree->childrenids($ruleid,$auth_rule);
            echo json_encode($sonids);
        }else{
            $this->error('非法操作！');
        }
    }

    
}
