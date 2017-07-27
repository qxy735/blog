<?php namespace Home\Controller;

use Home\Model\AlbumModel as Album;
use Home\Model\MenuModel as Menu;
use Think\Exception;
use Think\Log;

class AboutController extends BaseController
{
    /**
     * 显示关于禹译页面
     */
    public function yuyi()
    {
        $menu = $albums = [];

        // 获取菜单 ID
        $menu_id = (int)I('get.m');

        try {
            // 获取菜单信息
            if ($menu_id) {
                $menu = D('menu')->where([
                    'id' => $menu_id,
                    'enabled' => Menu::MENU_IS_ENABLED
                ])->getField('id,name,url');

                $menu = $menu ? array_values($menu) : [];
                $menu = $menu ? $menu[0] : [];
            }

            // 获取相册信息
            $albums = D('album')->where([
                'enabled' => Album::ALBUM_IS_ENABLED
            ])->order('displayorder desc, id desc')->getField('id,name,cover');

            $albums = $albums ? array_values($albums) : [];
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递菜单信息
        $this->assign('current_menu', $menu);

        // 传递菜单 ID
        $this->assign('menu_id', $menu_id);

        // 传递相册信息
        $this->assign('albums', $albums);

        // 显示关于禹译页面
        $this->display('about/yuyi');
    }
}