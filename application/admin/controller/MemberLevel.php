<?php
namespace app\admin\controller;
use think\Controller;
class MemberLevel extends Controller
{
	public function lst(){
        $MemberLevelRes=db('member_level')->paginate(15);
        $this->assign([
            'MemberLevelRes'=>$MemberLevelRes
        ]);
        return view();
    }
    public function add(){
        if(request()->isAjax()){
            $data=input('post.');

            //数据验证
            /*$validate = validate('Attr');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }*/
            if(db('member_level')->insert($data)){
                return json(['error'=>1,'msg'=>'添加成功']);
            }else{
                return json(['error'=>2,'msg'=>'添加失败']);
            }
        }
        return view();
    }

    public function edit(){
        if(request()->isGET()){
            $id=input('id');
            $mls=db('member_level')->find($id);
            $this->assign([
                'mls'=>$mls,
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            //数据验证
            /*$validate = validate('Attr');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }*/
            $save=db('member_level')->update($data);
            if($save==0){
                return json(['error'=>0,'msg'=>'没有修改任何信息']);
            }else if($save==1){
                return json(['error'=>1,'msg'=>'修改成功']);
            }else{
                return json(['error'=>2,'msg'=>'修改失败']); 
            }
        }
    }

    public function del(){
        if(request()->isAjax()){
            $id=input('id');
            if(db('member_level')->delete($id)){
                return json(['error'=>1,'msg'=>'删除成功']);
            }else{
                return json(['error'=>2,'msg'=>'删除失败']);
            }
        }
    }
}
