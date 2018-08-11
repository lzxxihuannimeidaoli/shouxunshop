<?php
namespace app\admin\controller;
use think\Controller;
class Link extends Controller
{
	/**
	*商品品牌列表
	*/
    public function lst()
    {
        $brand=db('link');
        $linkRes=$brand->order('id desc')->paginate(15);
        $this->assign([
            'linkRes'=>$linkRes,
        ]);
        return view();
    }
    /*友情链接添加*/
    public function add()
    {
        if(request()->isAjax()){
            $data=input('post.');
            if(session('logo')){
               $data['logo'] =session('logo');
            }
            if(stripos($data['link_url'], 'http://')===false){
                $data['link_url']='http://'.$data['link_url'];
            }
            if(db('link')->insert($data)){
                //数据添加成功后销毁session
                session('logo',null);
                return json(['error'=>1,'msg'=>'友情链接添加成功']);
            }else{
                return json(['error'=>0,'msg'=>'友情链接添加失败']);
            }
        }
        return view();
    }
    /**
	*商品品牌修改
	*/
    public function edit()
    {
        if(request()->isGet()){
            $id=input('id');
            $links=db('link')->find($id);
            $this->assign([
                'links'=>$links,
            ]);
            return view();
        }
        if(request()->isAjax()){
            $data=input('post.');
            if(session('logo')){
                $oldArticles=db('link')->field('logo')->find($data['id']);
                $oldArticlelogo=IMG_UPLOADS.$oldArticles['logo'];
                if(file_exists($oldArticlelogo)){
                    @unlink($oldArticlelogo);
                }
                $data['logo'] =session('logo'); 
            }
            if(stripos($data['link_url'], 'http://')===false){
                $data['link_url']='http://'.$data['link_url'];
            }
            $save=db('link')->update($data);
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
	*商品品牌删除
	*/
    public function del()
    {
        if(request()->isAjax()){
            $id=input('id');
            if($id){
                $oldLinks=db('link')->field('logo')->find($id);
                $oldLinkLogo=IMG_UPLOADS.$oldLinks['logo'];
                if(file_exists($oldLinkLogo)){
                    @unlink($oldLinkLogo);
                }
                if(db('link')->delete($id)){
                    return json(['error'=>0,'msg'=>'删除成功']);
                }else{
                    return json(['error'=>1,'msg'=>'删除失败']);
                }
            }
        }
    }

    
    //图片上传
    public function upload(){
        if (session('logo')){
            if (file_exists(IMG_UPLOADS.session('logo'))) {
                unlink(IMG_UPLOADS.session('logo'));
            }
        }
        $file = request()->file('logo');
        $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads');
        if($info){
            session('logo',$info->getSaveName());
            return DS . 'static' . DS . 'uploads'.DS.$info->getSaveName();
        }else{
            echo $file->getError();
        }
    }
    //实时删除缩略图的方法
    public function canclelogo(){
        if(request()->isAjax()){
            if(session('logo')){
                if(file_exists(IMG_UPLOADS.session('logo'))){
                    unlink(IMG_UPLOADS.session('logo'));
                }
            }
            session('logo',null);
        }
    }
    
}
