<?php
namespace app\admin\controller;
use think\Controller;
class Brand extends Controller
{
	/**
	*商品品牌列表
	*/
    public function lst()
    {
        $brand=db('brand');
        $brandRes=$brand->order('id desc')->paginate(15);
        $this->assign([
            'brandRes'=>$brandRes,
        ]);
        return view();
    }
    /**
	*商品品牌添加
	*/
    public function add()
    {
        if(request()->isPost()){
            $data=input('post.');
            if($_FILES['brand_img']['tmp_name']){
                $data['brand_img']=$this->upload();
            }
            if(stripos($data['brand_url'], 'http://')===false){
                $data['brand_url']='http://'.$data['brand_url'];
            }
            $add=db('brand')->insert($data);
            if($add){
                $this->success('添加成功','lst');
            }else{
                $this->error('添加失败');
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
            $brands=db('brand')->find($id);
            $this->assign([
                'brands'=>$brands,
            ]);
            return view();
        }
        if(request()->isPost()){
            $data=input('post.');
            if($_FILES['brand_img']['tmp_name']){
                $oldBrands=db('brand')->field('brand_img')->find($data['id']);
                $oldBrandImg=IMG_UPLOADS.$oldBrands['brand_img'];
                if(file_exists($oldBrandImg)){
                    @unlink($oldBrandImg);
                }
                $data['brand_img']=$this->upload();
            }
            if(stripos($data['brand_url'], 'http://')===false){
                $data['brand_url']='http://'.$data['brand_url'];
            }
            $save=db('brand')->update($data);
            if($save){
                $this->success('品牌修改成功','lst');
            }else{
                $this->error('品牌修改失败');
            }
        }
        
    }
    /**
	*商品品牌删除
	*/
    public function del()
    {
        if(request()->isAjax()){
            if(db('brand')->delete(input('id')))
                return json(['error'=>0,'msg'=>'删除成功']);
            return json(['error'=>1,'msg'=>'删除失败']);

        }
    }

    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('brand_img');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads');
            if($info){
                return  $info->getSaveName();
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }
    
}
