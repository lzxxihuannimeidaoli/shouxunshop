<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class Cate extends Controller
{
	public function lst(){
        $cate=new Catetree();
        $cateObj=db('cate');
        if(request()->isAjax()){
            $data=input('post.');
            $cate->cateSort($data['sort'],$cateObj);
            return json(['error'=>1,'msg'=>'排序成功']);
        }
        $cateRes=$cateObj->order('sort DESC')->select();

        
        $cateRes=$cate->catetree($cateRes);
        $this->assign([
            'cateRes'=>$cateRes
        ]);
        return view();
    }
    public function add(){
        $cateObj=db('cate');
        $cate=new Catetree();
        if(request()->isAjax()){
            $data=input('post.');
            if(in_array($data['pid'],['1','3'])){
                return json(['error'=>3,'msg'=>'系统分类不能作为上级分类']);
            }
            if($data['pid']==2){
                $data['cate_type']=3;
            }
            $cataid=$cateObj->field('pid')->find($data['pid']);
            if($cataid['pid']==2){
                return json(['error'=>4,'msg'=>'此分类不能作为上级分类']);
            }
            //数据验证
            $validate = validate('Cate');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }
            if($cateObj->insert($data)){
                return json(['error'=>1,'msg'=>'添加成功']);
            }else{
                return json(['error'=>2,'msg'=>'添加失败']);
            }
        }
        $cateRes=$cateObj->order('sort DESC')->select();
        $cateRes=$cate->catetree($cateRes);
        $this->assign([
            'cateRes'=>$cateRes
        ]);
        return view();
    }

    public function edit($id){
        $cateObj=db('cate');
        $cate=new Catetree();
        if(request()->isAjax()){
            $data=input('post.');
            //数据验证
            $validate = validate('Cate');
            if(!$validate->check($data)){
                return json(['error'=>5,'msg'=>$validate->getError()]);
            }
            $save=$cateObj->update($data);
            if($save==0){
                return json(['error'=>0,'msg'=>'没有修改任何信息']);
            }else if($save==1){
                session('thumb',null);
                return json(['error'=>1,'msg'=>'修改成功']);
            }else{
                return json(['error'=>2,'msg'=>'修改失败']); 
            }
        }
        $cateRes=$cateObj->order('sort DESC')->select();
        $cateRes=$cate->catetree($cateRes);
        $cates=$cateObj->find($id);
        $this->assign([
            'cates'=>$cates,
            'cateRes'=>$cateRes
        ]);
        return view();
    }

    public function del(){
        $cate=db('cate');
        if(request()->isAjax()){
            $id=input('id');
            $cateTree=new Catetree();
            $sonids=$cateTree->childrenids($id,$cate);
            $sonids[]=intval($id);//获取子栏目id
            $arrSys=[1,2,3];
            $arrRes=array_intersect($arrSys,$sonids);
            if($arrRes){
                return json(['error'=>0,'msg'=>'系统内置分类不允许删除']);
            }
            //删除分类前判断该分类下的文章和文章相关缩略图
            $article=db('article');
            foreach($sonids as $kk=>$vv ){
                $artRes=$article->field('id,thumb')->where(array('cate_id'=>$vv))->select();
                foreach ($artRes as $k => $v) {
                    $thumbSrc=IMG_UPLOADS.$v['thumb'];
                    if(file_exists($thumbSrc)){
                        @unlink($thumbSrc);
                    }
                    $article->delete($v['id']);
                }
            }
            if($cate->delete($sonids)){
                return json(['error'=>1,'msg'=>'删除成功']);
            }else{
                return json(['error'=>2,'msg'=>'删除失败']);
            }
        }
    }

    
}
