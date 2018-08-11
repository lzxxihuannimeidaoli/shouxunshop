<?php
namespace app\admin\model;
use think\File;
use think\Model;

class Goods extends Model
{
    protected $field=true;
	protected static function init(){
	    Goods::beforeInsert(function($goods){
	        if($_FILES['og_thumb']['tmp_name']){
                $Thumbname=$goods->upload('og_thumb');
                $ogThumb=date('Ymd').DS.$Thumbname;
                $bigThumb=date('Ymd').DS.'big_'.$Thumbname;
                $midThumb=date('Ymd').DS.'mid_'.$Thumbname;
                $smThumb=date('Ymd').DS.'sm_'.$Thumbname;
                $image=\think\Image::open(IMG_UPLOADS.$ogThumb);
                $image->thumb(500,500)->save(IMG_UPLOADS.$bigThumb);
                $image->thumb(200,200)->save(IMG_UPLOADS.$midThumb);
                $image->thumb(80,80)->save(IMG_UPLOADS.$smThumb);
                $goods->og_thumb=$ogThumb;
                $goods->big_thumb=$bigThumb;
                $goods->mid_thumb=$midThumb;
                $goods->sm_thumb=$smThumb;
            }
            $goods->goods_code=time().rand(111111,999999);
            //dump($goods);die;
        });

        Goods::afterInsert(function($goods){
            //接收表单数据
            $GoodsData=input('post.');
          //  dump($GoodsData);
            $mpriceArr=$goods->mp;
            $goods_id=$goods->id;
            if($mpriceArr){
                foreach($mpriceArr as $k=>$v){
                    if(!trim($v))
                        continue;
                    db('member_price')->insert(['mlevel_id'=>$k,'mprice'=>$v,'goods_id'=>$goods_id]);
                }
            }
            //商品相册处理
            if($goods->hasImgs($_FILES['goods_photo']['tmp_name'])){
                $files = request()->file('goods_photo');
                foreach($files as $file){
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'uploads');
                    if($info){
                        $photoName=$info->getFilename();
                        $ogphoto=date('Ymd').DS.$photoName;
                        $bigphoto=date('Ymd').DS.'big_'.$photoName;
                        $midphoto=date('Ymd').DS.'mid_'.$photoName;
                        $smphoto=date('Ymd').DS.'sm_'.$photoName;
                        $image=\think\Image::open(IMG_UPLOADS.$ogphoto);
                        $image->thumb(500,500)->save(IMG_UPLOADS.$bigphoto);
                        $image->thumb(200,200)->save(IMG_UPLOADS.$midphoto);
                        $image->thumb(80,80)->save(IMG_UPLOADS.$smphoto);
                        db('goods_photo')->insert(['goods_id'=>$goods_id,'og_photo'=>$ogphoto,'big_photo'=>$bigphoto,'mid_photo'=>$midphoto,'sm_photo'=>$smphoto]);
                    }else{
                        echo $files->getError();
                    }
                }
            }

            //商品属性处理
            $i=0;
            if(isset($GoodsData['goods_attr'])){
                foreach($GoodsData['goods_attr'] as $k=>$v){
                    if(is_array($v)){
                        if(!empty($v)){
                            foreach($v as $k1=>$v1){
                                if(!$v1){
                                    $i++;
                                    continue;
                                }
                                db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v1,'attr_price'=>$GoodsData['attr_price'][$i],'goods_id'=>$goods_id]);
                                $i++;
                            }
                        }
                    }else{
                        //处理唯一商品属性
                        db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v,'goods_id'=>$goods_id]);
                    }
                }
            }
          //  die;
        });
        Goods::beforeDelete(function($goods){

            $goodsId=$goods->id;
            //删除主图及缩略图
            if($goods->og_thumb){
                $thumb=[];
                $thumb[]=IMG_UPLOADS.$goods->og_thumb;
                $thumb[]=IMG_UPLOADS.$goods->big_thumb;
                $thumb[]=IMG_UPLOADS.$goods->mid_thumb;
                $thumb[]=IMG_UPLOADS.$goods->sm_thumb;
            }
            if(!empty($goods->og_thumb)){
                foreach($thumb as $k =>$v){
                    if(file_exists($v)){
                        @unlink($v);
                    }
                }

            }

            //删除关联的会员价格
            db('member_price')->where('goods_id',$goodsId)->delete();
            //删除关联的商品属性
            db('goods_attr')->where('goods_id',$goodsId)->delete();
            //删除关联的商品相册
            $goodsPhotoRes=model('GoodsPhoto')->where('goods_id',$goodsId)->select();
            if(!empty($goodsPhotoRes)){
                foreach ($goodsPhotoRes as $k =>$v){
                    if($v->og_photo){
                        $photo=[];
                        $photo[]=IMG_UPLOADS.$v->og_photo;
                        $photo[]=IMG_UPLOADS.$v->big_photo;
                        $photo[]=IMG_UPLOADS.$v->mid_photo;
                        $photo[]=IMG_UPLOADS.$v->sm_photo;
                    }
                    foreach($photo as $k1 =>$v1){
                        if(file_exists($v1)){
                            @unlink($v1);
                        }
                    }
                }
            }
            model('GoodsPhoto')->where('goods_id',$goodsId)->delete();
          /*  dump($goodsPhotoRes);die;
            dump($goods);die;*/
        });
    }


    //判断商品相册是否有图片上传
    private function hasImgs($tmpArr){
	    if($tmpArr){
	        foreach ($tmpArr as $k=>$v){
                if($v){
                    return true;
                }
            }
            return false;
        }
    }

    public function upload($imgName){
        $file = request()->file($imgName);
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'uploads');
            if($info){
                return $info->getFilename();
            }else{
                echo $file->getError();
            }
        }
    }
}
