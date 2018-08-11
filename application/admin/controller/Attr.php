<?php
namespace app\admin\controller;
use think\Controller;
class Attr extends Controller
{
	public function lst(){
	    if(request()->isGet()){
	        $typeid=input('id');
	        if(isset($typeid)){
                $map['type_id']=['=',$typeid];
            }else{
                $map=1;
            }
            $attrRes=db('attr')->alias('a')->field('a.*,t.type_name')->join('type t',"a.type_id=t.id")->order('id DESC')->where($map)->paginate(15);
            $this->assign([
                'attrRes'=>$attrRes
            ]);
        }
        return view();
    }
    public function add(){
        if(request()->isAjax()){
            $data=input('post.');
            $data['attr_values']=str_replace('，',',', $data['attr_values']);
            //数据验证
            $validate = validate('Attr');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }
            if(db('attr')->insert($data)){
                return json(['error'=>1,'msg'=>'添加成功']);
            }else{
                return json(['error'=>2,'msg'=>'添加失败']);
            }
        }
        $typeRes=db('type')->select();
        $this->assign([
            'typeRes'=>$typeRes
        ]);
        return view();
    }

    public function edit(){
        if(request()->isGET()){
            $id=input('id');
            $attrs=db('attr')->find($id);
            $typeRes=db('type')->select();
            $this->assign([
                'attrs'=>$attrs,
                'typeRes'=>$typeRes
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            //数据验证
            $validate = validate('Attr');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }
            $save=db('attr')->update($data);
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
            if(db('attr')->delete($id)){
                return json(['error'=>1,'msg'=>'删除成功']);
            }else{
                return json(['error'=>2,'msg'=>'删除失败']);
            }
        }
    }

    /**
     *Ajax获取商品属性
     */
    public function isAjaxAttr(){
        $typeId=input('type_id');
        $attrRes=db('attr')->where(array('type_id'=>$typeId))->select();
        echo json_encode($attrRes);
    }
}
