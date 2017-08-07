<?php namespace Home\Controller;

use Home\Model\AlbumModel as Album;
use Home\Model\MenuModel as Menu;
use Home\Model\ArticleModel as Article;
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
            ])->order('displayorder desc, id desc')->getField('id,name,cover,photos');

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

    /**
     * 显示相册相片页面
     */
    public function album()
    {
        $menu = $album = $photos = $hots = [];

        // 获取菜单 ID
        $menu_id = (int)I('get.m');

        // 获取相册 ID
        $album_id = (int)I('get.id');

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

            if ($album_id) {
                // 获取相册信息
                $album = D('album')->where([
                    'id' => $album_id
                ])->getField('id,name,photos');

                $album = $album ? array_values($album) : [];
                $album = $album ? $album[0] : [];

                // 获取相册里的相片信息
                $photos = D('photo')->where([
                    'albumid' => $album_id
                ])->order('id desc')->limit(6)->getField('id,name,url');

                $photos = $photos ? array_values($photos) : [];
            }

            // 获取热门推荐
            $hots = D('article')->where([
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
            ])->order('visitcount desc,id desc')->limit(9)->getField('id,title,status');

            $hots = $hots ? array_values($hots) : [];

            // 处理文章标题
            $hots = array_map(function ($hot) {
                $hot['title'] = $this->substr($hot['title']);

                return $hot;
            }, $hots);
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递菜单信息
        $this->assign('current_menu', $menu);

        // 传递菜单 ID
        $this->assign('menu_id', $menu_id);

        // 传递相册信息
        $this->assign('album', $album);

        // 传递相册 ID
        $this->assign('album_id', $album_id);

        // 传递照片信息
        $this->assign('photos', $photos);

        // 增加热门推荐显示样式名
        $hot_styles = ['red', 'orange', 'pink', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray'];

        // 传递热门推荐显示样式名
        $this->assign('hot_styles', $hot_styles);

        // 传递热门推荐信息
        $this->assign('hots', $hots);

        $this->display('about/album');
    }
}