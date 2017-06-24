<?php namespace Home\Controller;

use Home\Model\MenuModel as Menu;
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

        $this->get_navigate_menu();
    }

    /**
     * 设置模板资源地址
     */
    protected function set_view_url()
    {
        // 获取基础目录
        $base_path = 'http://' . $_SERVER['SERVER_NAME'] . APP_PATH;

        // 获取资源目录地址
        $asset_path = "{$base_path}Home/Asset";

        // 处理资源目录路径地址
        $asset_path = str_replace('\\', '/', $asset_path);

        // 传递图片资源目录地址
        $this->assign('__IMG__', "{$asset_path}/image");

        // 传递样式文件资源目录地址
        $this->assign('__CSS__', "{$asset_path}/css");

        // 传递脚本资源目录地址
        $this->assign('__JS__', "{$asset_path}/js");

        // 传递视图文件目录地址
        $this->assign('__VIEW__', "{$base_path}View");
    }

    protected function get_navigate_menu()
    {

    }
}