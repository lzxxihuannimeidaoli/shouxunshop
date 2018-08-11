<?php
namespace app\admin\controller;
use think\Controller;
use Catetree\Catetree;
class Goods extends Controller
{
	/**
	*配置列表
	*/
    public function lst()
    {
        $join=[
            ['category c','g.category_id=c.id','LEFT'],
            ['brand b','g.brand_id=b.id','LEFT'],
            ['type t','g.type_id=t.id','LEFT'],
            ['product p','g.id=p.goods_id','LEFT'],
        ];
        $goodsRes=db('goods')->alias('g')->field('g.*,c.cate_name,b.brand_name,t.type_name,SUM(p.goods_number) gn')->join($join)->group('g.id')->order('g.id DESC')->paginate(10);
        $this->assign([
            'goodsRes'=>$goodsRes,
        ]);
        return view();
    }
    /**
	*商品添加
	*/
    public function add()
    {
        if(request()->isPost()){
            $data=input('post.');
            // dump($data); die;
            //验证
            // dump($_FILES);die;
            /*$validate = validate('goods');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }*/
            $add=model('goods')->save($data);
            if($add){
                $this->success('添加商品成功！','lst');
            }else{
                $this->error('添加商品失败！');
            }
            return;
        }
        // 会员级别数据
        $mlRes=db('memberLevel')->field('id,level_name')->select();

        $typeRes=db('type')->select();
        // 品牌分类
        $brandRes=db('brand')->field('id,brand_name')->select();
        //商品推荐位
       // $goodsRecposRes=db('recpos')->where('rec_type','=',1)->select();
        // 商品分类
        $Category=new Catetree();
        $CategoryObj=db('Category');
        $CategoryRes=$CategoryObj->order('sort DESC')->select();
        $CategoryRes=$Category->Catetree($CategoryRes);
        $this->assign([
            'typeRes'=>$typeRes,
            'brandRes'=>$brandRes,
            'CategoryRes'=>$CategoryRes,
            'mlRes'=>$mlRes,
        ]);
    	return view();
    }
    /**
	*配置修改
	*/
    public function edit()
    {
        if(request()->isGet()){
            $id=input('id');
            $confs=db('conf')->find($id);
            $this->assign([
                'confs'=>$confs,
            ]);
            return view();
        }
        if(request()->isPost()){
            $data=input('post.');
            //将中文逗号进行替换
            if($data['form_type']=='radio' || $data['form_type']=='select' || $data['form_type']=='checkbox'){
                $data['values']=str_replace('，',',', $data['values']);
                $data['value']=str_replace('，',',', $data['value']);
            }
            $validate = validate('Conf');
            if(!$validate->check($data)){
                return json(['error'=>3,'msg'=>$validate->getError()]);
            }
            $save=db('conf')->update($data);
            if($save==1){
                return json(['error'=>1,'msg'=>'修改成功']);
                $this->success('品牌修改成功','lst');
            }else if($save==0){
                return json(['error'=>2,'msg'=>'没有修改任何信息']);
            }else{
                return json(['error'=>3,'msg'=>'修改失败']);
            }
        }
        
    }
    /**
	*配置删除
	*/
    public function del()
    {
        if(request()->isAjax()){
            if(model('goods')->destroy(input('id')))
                return json(['error'=>0,'msg'=>'删除成功']);
            return json(['error'=>1,'msg'=>'删除失败']);

        }
    }

    public function upload($imgName){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($imgName);
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
    //库存
    public function product($id){
        if(request()->isPost()){
            db('product')->where('goods_id',$id)->delete();
            $data=input('post.');
            $goodsAttr=$data['goods_attr'];
            $goodsNum=$data['goods_num'];
            $product=db('product');
            foreach ($goodsNum as $k =>$v){
                $strArr=array();
                foreach ($goodsAttr as $k1 => $v1){
                    if(intval($v1[$k])<=0){
                        continue 2;
                    }
                    $strArr[]=$v1[$k];

                }
                sort($strArr);
                $strArr=implode(',',$strArr);
                $product->insert([
                    'goods_id'=>$id,
                    'goods_number'=>$v,
                    'goods_attr'=>$strArr,
                ]);
            }
            $this->success('添加库存成功！');
            return;
        }

        $_radioAttrRes=db('goods_attr')->alias('g')->field('g.id,g.attr_id,g.attr_value,a.attr_name')->join('attr a','g.attr_id=a.id')->where(array('goods_id'=>$id,'a.attr_type'=>1))->select();
        $radioAttrRes=array();
        //数组格式重组
        foreach ($_radioAttrRes as $k =>$v){
            $radioAttrRes[$v['attr_name']][]=$v;
        }
        //获取商品库存信息
        $goodsProRes=db('product')->where('goods_id',$id)->select();
//        dump($goodsProRes);die;
        $this->assign([
            'radioAttrRes'=>$radioAttrRes,
            'goodsProRes'=>$goodsProRes,
        ]);
        //  dump($radioAttrRes);die;
        return view();
    }
    
}
