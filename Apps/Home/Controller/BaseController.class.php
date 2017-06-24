<?php namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller
{
    /**
     * 初始化处理
     *
     * BaseController constructor.
     */
    public function __construct()
    {
        // 初始化父控制
        parent::__construct();

        // 设置模板所需的资源地址
        $this->set_view_url();
    }

    /**
     * 设置模板资源地址
     */
    protected function set_view_url()
    {
        // 获取资源目录地址
        $asset_path = APP_PATH .'Home/Asset';

        // 处理资源目录路径地址
        $asset_path = str_replace('\\', '/', $asset_path);

        // 传递图片资源目录地址
        $this->assign('__IMG__', "{$asset_path}/image");

        // 传递样式文件资源目录地址
        $this->assign('__CSS__', "{$asset_path}/css");

        // 传递脚本资源目录地址
        $this->assign('__JS__', "{$asset_path}/js");
    }
}