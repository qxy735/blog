<?php namespace Home\Controller;

use Home\Model\CategoryModel as Category;
use Home\Model\ArticleModel as Article;
use Home\Model\LinkModel as Link;
use Home\Model\MenuModel as Menu;
use Think\Exception;
use Think\Log;

class OperaController extends BaseController
{
    /**
     * 显示开心剧场页面
     */
    public function article()
    {
        $hots = $links = $articles = $menu = [];

        // 获取菜单 ID
        $menu_id = (int)I('get.m');

        try {
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

            // 获取友情链接
            $links = D('link')->where([
                'enabled' => Link::LINK_IS_ENABLED,
            ])->order('displayorder desc,id desc')->limit(6)->getField('id,name,url');

            $links = $links ?: [];

            // 组装文章查询条件
            $condition = [
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
                'menuid' => $menu_id,
            ];

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

        // 增加热门推荐显示样式名
        $hot_styles = ['red', 'orange', 'pink', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray'];

        // 传递热门推荐显示样式名
        $this->assign('hot_styles', $hot_styles);

        // 传递热门推荐信息
        $this->assign('hots', $hots);

        // 传递友情链接信息
        $this->assign('links', $links);

        // 增加标签显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递标签显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 传递文章信息
        $this->assign('articles', []);

        // 传递菜单信息
        $this->assign('current_menu', $menu);

        // 传递菜单 ID
        $this->assign('menu_id', $menu_id);

        // 显示开心剧场页面
        $this->display('article/opera');
    }
}