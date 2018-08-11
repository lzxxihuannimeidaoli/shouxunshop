<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class Category extends Controller
{
	/**
	*商品分类列表
	*/
    public function lst()
    {
        $cate=new Catetree();
        $categoryObj=db('category');
        if(request()->isAjax()){
            $data=input('post.');
            $cate->cateSort($data['sort'],$categoryObj);
            return json(['error'=>1,'msg'=>'排序成功']);
        }
        $categoryRes=$categoryObj->order('sort DESC')->select();

        $categoryRes=$cate->catetree($categoryRes);
        $this->assign([
            'categoryRes'=>$categoryRes
        ]);
        return view();
    }
    /**
	*文章添加
	*/
    public function add()
    {
        $cate=new Catetree();
        if(request()->isAjax()){
            $data=input('post.');
            //dump($data);die;
            if(session('thumb')){
               $data['cate_img'] =session('thumb');
            }
            if(db('category')->insert($data)){
                //数据添加成功后销毁session
                session('thumb',null);
                return json(['error'=>1,'msg'=>'商品分类添加成功']);
            }else{
                return json(['error'=>0,'msg'=>'商品分类添加失败']);
            }
        }
        $categoryRes=db('category')->order('sort DESC')->select();
        $categoryRes=$cate->catetree($categoryRes);
        $this->assign([
            'categoryRes'=>$categoryRes
        ]);
    	return view();
    }
    /**
	*商品分类修改
	*/
    public function edit()
    {
        if(request()->isGet()){
            $id=input('id');
            $categoryObj=db('category');
            $cate=new Catetree();
            $categoryRes=$categoryObj->order('sort DESC')->select();
            $categoryRes=$cate->catetree($categoryRes);
            $categorys=$categoryObj->find($id);
            $this->assign([
                'categorys'=>$categorys,
                'categoryRes'=>$categoryRes
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            if(session('thumb')){
                $oldArticles=db('category')->field('cate_img')->find($data['id']);
                $oldArticleThumb=IMG_UPLOADS.$oldArticles['thumb'];
                if(file_exists($oldArticleThumb)){
                    @unlink($oldArticleThumb);
                }
                $data['cate_img'] =session('thumb');
            }
            $save=db('category')->update($data);
            if($save==0){
                return json(['error'=>0,'msg'=>'没有修改任何信息']);
            }else if($save==1){
                return json(['error'=>1,'msg'=>'修改成功']);
            }else{
                return json(['error'=>2,'msg'=>'修改失败']);
            }
        }

    }
    /**
	*文章删除
	*/
    public function del()
    {
        if(request()->isAjax()){
            $id=input('id');
            $category=db('category');
            if($id){
                $cateTree=new Catetree();
                $sonids=$cateTree->childrenids($id,$category);
                $sonids[]=intval($id);
                foreach($sonids as $v){
                    $oldCategorys=$category->field('cate_img')->find($v);
                    $oldCategoryCateImg=IMG_UPLOADS.$oldCategorys['cate_img'];
                    if(file_exists($oldCategoryCateImg)){
                        @unlink($oldCategoryCateImg);
                    }
                }
                if($category->delete($sonids)){
                    return json(['error'=>0,'msg'=>'删除成功']);
                }else{
                    return json(['error'=>1,'msg'=>'删除失败']);
                }
            }else{
                return json(['error'=>3,'msg'=>'非法数据']);
            }
        }
    }

    //图片上传
    public function upload(){
        if (session('thumb')){
            if (file_exists(IMG_UPLOADS.session('thumb'))) {
                unlink(IMG_UPLOADS.session('thumb'));
            }
        }
        $file = request()->file('thumb');
        $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads');
        if($info){
            session('thumb',$info->getSaveName());
            return DS . 'static' . DS . 'uploads'.DS.$info->getSaveName();
        }else{
            echo $file->getError();
        }
    }
    //实时删除缩略图的方法
    public function canclethumb(){
        if(request()->isAjax()){
            if(session('thumb')){
                if(file_exists(IMG_UPLOADS.session('thumb'))){
                    unlink(IMG_UPLOADS.session('thumb'));
                }
            }
            session('thumb',null);
        }
    }
    //图片管理
    public function imglist(){
        $_files=my_scandir();
        $files=array();
        foreach ($_files as $k => $v) {
            if(is_array($v)){
                foreach($v as $k1=>$v1){
                    $v1=str_replace(UEDITOR, HTTP_UEDITOR, $v1);
                    $files[]=$v1;
                }
            }else{
                $v=str_replace(UEDITOR, HTTP_UEDITOR, $v);
                $files[]=$v;
            }
        }
        $this->assign([
            'imgRes'=>$files,
        ]);
        return view();
    }

    //图片删除
    public function delimg(){
        $imgsrc=input('imgsrc');
        if(file_exists(DEL_UEDITOR.$imgsrc)){
            if(@unlink(DEL_UEDITOR.$imgsrc)){
                return json(['error'=>1,'msg'=>'图片删除成功']);
            }else{
                return json(['error'=>2,'msg'=>'图片删除失败']);
            }
        }else{
            return json(['error'=>0,'msg'=>'图片不存']);
        }
    }


    
}
