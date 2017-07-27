<?php namespace Home\Controller;

use Home\Model\CategoryModel as Category;
use Home\Model\ArticleModel as Article;
use Home\Model\MenuModel as Menu;
use Home\Model\LinkModel as Link;
use Think\Exception;
use Think\Log;

class WorkController extends BaseController
{
    /**
     * 显示我的作品页面
     */
    public function article()
    {
        $softwares = $links = $articles = $menu = [];

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

            // 获取软件推荐信息
            $softwares = D()->query('SELECT art.id,art.title FROM `blog_articles` as art INNER JOIN blog_categorys as cat ON art.categoryid = cat.id WHERE art.ispublic = ' . Article::ARTICLE_IS_PUBLIC . ' AND art.`status` = ' . Article::ARTICLE_STATUS_NORMAL . ' AND cat.type = ' . Category::CATEGORY_TYPE_DOWNLOAD . ' order by art.visitcount desc,art.id desc limit 9');
            $softwares = $softwares ? array_values($softwares) : [];

            // 处理文章标题
            $softwares = array_map(function ($software) {
                $software['title'] = $this->substr($software['title']);

                return $software;
            }, $softwares);

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
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递菜单信息
        $this->assign('current_menu', $menu);

        // 传递菜单 ID
        $this->assign('menu_id', $menu_id);

        // 增加热门推荐显示样式名
        $hot_styles = ['red', 'orange', 'pink', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray'];

        // 传递热门推荐显示样式名
        $this->assign('hot_styles', $hot_styles);

        // 传递软件推荐文章信息
        $this->assign('softwares', $softwares);

        // 增加友情链接显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递友情链接显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 传递友情链接信息
        $this->assign('links', $links);

        // 传递文章信息
        $this->assign('articles', $articles);

        // 显示我的作品页面
        $this->display('article/work');
    }
}