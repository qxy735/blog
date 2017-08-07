<?php namespace Home\Controller;

use Think\Exception;
use Think\Log;

class PhotoController extends BaseController
{
    /**
     * 加载更多照片信息
     */
    public function load_more()
    {
        try {
            // 获取相册 ID
            $album_id = (int)I('post.album_id');

            if (!$album_id) {
                echo '';
                exit;
            }

            // 获取当前页
            $page = I('post.page', 1);

            // 每页获取 6 条数据
            $pre_page = 6;

            // 计算获取开始位置
            $start = ($page - 1) * $pre_page;

            // 获取相册里的相片信息
            $photos = D('photo')->where([
                'albumid' => $album_id
            ])->order('id desc')->limit($start, $pre_page)->getField('id,name,url');

            $photos = $photos ? array_values($photos) : [];

            $result = '';

            // 默认图片地址
            $base_path = 'http://' . $_SERVER['SERVER_NAME'] . APP_PATH;

            // 获取资源目录地址
            $asset_path = "{$base_path}Home/Asset";

            // 处理资源目录路径地址
            $asset_path = str_replace('\\', '/', $asset_path);

            $__IMG__ = "{$asset_path}/image";

            $src = "{$__IMG__}/article1.jpg";

            foreach ($photos as $photo) {
                if ($photo['url']) {
                    $src = $photo['url'];
                }

                $result .= "<dl><dt><a href='javascript:void(0);'><img src='{$src}' /></a></dt>";
                $result .= "<dd><a href='javascript:void(0);'>{$photo['name']}</a></dd></dl>";
            }

            echo $result;
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());

            echo '';
        }
    }
}