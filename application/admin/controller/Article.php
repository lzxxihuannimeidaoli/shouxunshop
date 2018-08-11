<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class Article extends Controller
{
	/**
	*文章列表
	*/
    public function lst()
    {
        $article=db('article');
        $articleRes=$article->field("a.*,c.cate_name")->alias("a")->join("cate c","a.cate_id=c.id")->order('a.id desc')->paginate(15);
        $this->assign('articleRes',$articleRes);
        /*$this->assign([
            'articleRes'=>$articleRes
        ]);*/
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
            if(session('thumb')){
               $data['thumb'] =session('thumb');
            }
            if(stripos($data['link_url'], 'http://')===false){
                $data['link_url']='http://'.$data['link_url'];
            }
            $data['addtime']=date('Y-m-d H:i:s');
            if(db('article')->insert($data)){
                //数据添加成功后销毁session
                session('thumb',null);
                return json(['error'=>1,'msg'=>'文章添加成功']);
            }else{
                return json(['error'=>0,'msg'=>'文章添加失败']);
            }
        }
        $cateRes=db('cate')->order('sort DESC')->select();
        $cateRes=$cate->catetree($cateRes);
        $this->assign([
            'cateRes'=>$cateRes
        ]);
    	return view();
    }
    /**
	*文章修改
	*/
    public function edit()
    {

        if(request()->isGet()){
            $id=input('id');
            $arts=db('article')->find($id);
            $cate=new Catetree();
            $cateRes=db('cate')->order('sort DESC')->select();
            $cateRes=$cate->catetree($cateRes);
            $this->assign([
                'arts'=>$arts,
                'cateRes'=>$cateRes,
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            if(session('thumb')){
                $oldArticles=db('article')->field('thumb')->find($data['id']);
                $oldArticleThumb=IMG_UPLOADS.$oldArticles['thumb'];
                if(file_exists($oldArticleThumb)){
                    @unlink($oldArticleThumb);
                }
                $data['thumb'] =session('thumb');
            }
            if(stripos($data['link_url'], 'http://')===false){
                $data['link_url']='http://'.$data['link_url'];
            }
            $save=db('article')->update($data);
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
            if($id){
                $oldArticles=db('article')->field('thumb')->find($id);
                $oldArticleThumb=IMG_UPLOADS.$oldArticles['thumb'];
                if(file_exists($oldArticleThumb)){
                    @unlink($oldArticleThumb);
                }
                if(db('article')->delete($id)){
                    return json(['error'=>0,'msg'=>'删除成功']);
                }else{
                    return json(['error'=>1,'msg'=>'删除失败']);
                }
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
