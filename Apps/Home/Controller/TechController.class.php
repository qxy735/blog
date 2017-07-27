<?php namespace Home\Controller;

use Home\Model\CategoryModel as Category;
use Home\Model\ArticleModel as Article;
use Home\Model\LinkModel as Link;
use Home\Model\MenuModel as Menu;
use Think\Exception;
use Think\Log;

class TechController extends BaseController
{
    /**
     * 显示技术分享页面
     */
    public function article()
    {
        $directions = $categorys = $articles = $works = $links = $menu = [];

        // 获取菜单 ID
        $menu_id = (int)I('get.m');

        // 获取父分类 ID
        $parent_id = (int)I('get.pid');

        // 获取子分类 ID
        $son_id = (int)I('get.cid');

        try {
            // 获取顶级分类信息
            $directions = D('category')->where([
                'level' => Category::CATEGORY_LEVEL_TOP,
                'enabled' => Category::CATEGORY_IS_ENABLED,
                'type' => Category::CATEGORY_TYPE_NORMAL
            ])->order('displayorder desc,id desc')->getField('id,name,level');

            $directions = $directions ? array_values($directions) : [];

            // 组装一级文章分类查询条件
            $condition = [
                'level' => Category::CATEGORY_LEVEL_ONE,
                'enabled' => Category::CATEGORY_IS_ENABLED,
                'type' => Category::CATEGORY_TYPE_NORMAL
            ];

            if ($parent_id) {
                $condition['parentid'] = $parent_id;
            }

            // 获取一级文章分类信息
            $categorys = D('category')->where($condition)->order('displayorder desc,id desc')->limit(8)->getField('id,name,level');

            $categorys = $categorys ? array_values($categorys) : [];

            // 组装文章查询条件
            $condition = [
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
            ];

            // 根据选择的分类获取对应的文章信息
            if ($son_id) {
                $condition['categoryid'] = $son_id;
            } elseif ($parent_id) {
                $son_category_ids = [$parent_id];

                foreach ($categorys as $category) {
                    $son_category_ids[] = $category['id'];
                }

                $son_category_ids = implode(',', $son_category_ids);

                $condition['categoryid'] = ['in', $son_category_ids];
            } else {
                $son_category_ids = D('category')->where([
                    'type' => Category::CATEGORY_TYPE_NORMAL,
                    'enabled' => Category::CATEGORY_IS_ENABLED
                ])->getField('id,level');

                if ($son_category_ids) {
                    $son_category_ids = implode(',', array_keys($son_category_ids));

                    $condition['categoryid'] = ['in', $son_category_ids];
                } else {
                    $condition['id'] = 0;
                }
            }

            $condition['menuid'] = $menu_id;

            // 获取最新发布的文章
            $articles = D('article')->where($condition)
                ->order('id desc')
                ->limit(6)
                ->getField('id,title,cover,categoryid,author,content,visitcount,commentcount,createtime');

            $articles = $articles ?: [];

            $category_ids = $article_categorys = [];

            // 处理最新发布的文章内容和标题
            $articles = array_map(function ($article) use (&$category_ids) {
                // 获取文章分类 ID
                $category_ids[] = $article['categoryid'];

                // 处理文章标题
                $article['title'] = $this->substr($article['title']);

                // 处理文章内容
                $article['content'] = $this->substr(strip_tags($article['content']), 50);

                // 处理发布时间
                $article['createtime'] = $article['createtime'] ? date('Y-m-d H:i:s', $article['createtime']) : date('Y-m-d H:i:s');

                // 处理文章作者
                $article['author'] = $article['author'] ?: '公子禹';

                return $article;
            }, $articles);

            // 去除重复分类 ID
            $category_ids = $category_ids ? array_unique($category_ids) : [];

            // 获取分类名
            if ($category_ids) {
                $article_categorys = D('category')->where('id in(' . implode(',', $category_ids) . ') and enabled=' . Category::CATEGORY_IS_ENABLED)->getField('id,name');
            }

            // 获取文章所属分类名
            $articles = array_map(function ($article) use ($article_categorys) {
                $article['category'] = isset($article_categorys[$article['categoryid']]) ? $article_categorys[$article['categoryid']] : '禹译';

                return $article;
            }, $articles);

            // 卸载空闲变量
            unset($article_categorys);

            // 获取我的作品信息
            $works = D()->query('SELECT art.id,art.title FROM `blog_articles` as art INNER JOIN blog_categorys as cat ON art.categoryid = cat.id WHERE art.ispublic = ' . Article::ARTICLE_IS_PUBLIC . ' AND art.`status` = ' . Article::ARTICLE_STATUS_NORMAL . ' AND cat.type = ' . Category::CATEGORY_TYPE_WORK . ' order by art.visitcount desc,art.id desc limit 9');
            $works = $works ? array_values($works) : [];

            // 处理文章标题
            $works = array_map(function ($work) {
                $work['title'] = $this->substr($work['title']);

                return $work;
            }, $works);

            // 获取友情链接
            $links = D('link')->where([
                'enabled' => Link::LINK_IS_ENABLED,
            ])->order('displayorder desc,id desc')->limit(6)->getField('id,name,url');

            $links = $links ?: [];

            // 获取菜单信息
            if ($menu_id) {
                $menu = D('menu')->where([
                    'id' => $menu_id,
                    'enabled' => Menu::MENU_IS_ENABLED
                ])->getField('id,name,url');

                $menu = $menu ? array_values($menu) : [];
                $menu = $menu ? $menu[0] : [];
            }
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递顶级文章分类信息
        $this->assign('directions', $directions);

        // 传递一级文章分类信息
        $this->assign('categorys', $categorys);

        // 传递文章信息
        $this->assign('articles', $articles);

        // 传递菜单信息
        $this->assign('current_menu', $menu);

        // 传递菜单 ID
        $this->assign('menu_id', $menu_id);

        // 传递父分类 ID
        $this->assign('parent_id', $parent_id);

        // 传递子分类 ID
        $this->assign('son_id', $son_id);

        // 传递作品信息
        $this->assign('works', $works);

        // 增加我的作品显示样式名
        $hot_styles = ['red', 'orange', 'pink', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray'];

        // 传递我的作品显示样式名
        $this->assign('hot_styles', $hot_styles);

        // 增加友情链接显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递友情链接显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 传递友情链接信息
        $this->assign('links', $links);

        // 显示技术分享页面
        $this->display('article/tech');
    }
}