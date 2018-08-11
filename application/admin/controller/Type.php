<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class Type extends Controller
{
	public function lst(){
        $typeRes=db('type')->order('id DESC')->paginate();
        $this->assign([
            'typeRes'=>$typeRes
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
            if(db('type')->insert($data)){
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
            $types=db('type')->find($id);
            $this->assign([
                'types'=>$types,
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            //数据验证
            $save=db('type')->update($data);
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
            if(db('type')->delete($id)){
                db('attr')->where(array('type_id'=>$id))->delete();
                return json(['error'=>1,'msg'=>'删除成功']);
            }else{
                return json(['error'=>2,'msg'=>'删除失败']);
            }
        }
    }

    
}
