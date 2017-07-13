<?php namespace Home\Controller;

use Home\Model\ArticleModel as Article;
use Home\Model\CategoryModel as Category;

class ArticleController extends BaseController
{
    /**
     * Ajax 获取更多文章信息
     */
    public function load_more()
    {
        // 获取当前页
        $page = I('post.page', 1);

        // 每页获取 6 条数据
        $pre_page = 6;

        // 计算获取开始位置
        $start = ($page - 1) * $pre_page;

        // 获取文章
        $articles = D('article')->where([
            'ispublic' => Article::ARTICLE_IS_PUBLIC,
            'status' => Article::ARTICLE_STATUS_NORMAL,
        ])->order('id desc')->limit($start, $pre_page)->getField('id,title,cover,categoryid,author,content,visitcount,commentcount,createtime');

        // 判断文章是否存在
        if (!$articles) {
            echo '';
            exit;
        }

        $category_ids = $categorys = [];

        // 处理最新发布的文章内容和标题
        $articles = array_map(function ($article) use (&$category_ids) {
            // 获取文章分类 ID
            $category_ids[] = $article['categoryid'];

            // 处理文章标题
            $article['title'] = $this->substr($article['title']);

            // 处理文章内容
            $article['content'] = $this->substr($article['content'], 50);

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
            $categorys = D('category')->where('id in(' . implode(',', $category_ids) . ') and enabled=' . Category::CATEGORY_IS_ENABLED)->getField('id,name');
        }

        // 获取文章所属分类名
        $articles = array_map(function ($article) use ($categorys) {
            $article['category'] = isset($categorys[$article['categoryid']]) ? $categorys[$article['categoryid']] : '禹译';

            return $article;
        }, $articles);

        // 卸载空闲变量
        unset($categorys);

        $results = '';

        // 默认图片地址
        $base_path = 'http://' . $_SERVER['SERVER_NAME'] . APP_PATH;

        // 获取资源目录地址
        $asset_path = "{$base_path}Home/Asset";

        // 处理资源目录路径地址
        $asset_path = str_replace('\\', '/', $asset_path);

        $__IMG__ = "{$asset_path}/image";

        // 处理返回形式
        foreach ($articles as $article) {
            $results .= "<div class='article'><div class='article-img'>";

            if ($article['cover']) {
                $results .= "<img src='{$article['cover']}' />";
            } else {
                $results .= "<img src='{$__IMG__}/article.jpg' />";
            }

            $results .= "</div><div class='article-content'><h3><a href='article.html'>{$article['title']}</a></h3>";
            $results .= "<p><img src='{$__IMG__}/my-min.jpg' />";
            $results .= "<i>{$article['author']}</i><span>发布时间: {$article['createtime']}</span>
			<span>归属: {$article['category']}</span></p><p><a href='article.html'>{$article['content']}</a></p>";
            $results .= "<p class='use'><img src='{$__IMG__}/reviewbg.png' />";
            $results .= "<span>评论(<b>{$article['visitcount']}</b>)</span>";
            $results .= "<img src='{$__IMG__}/browsebg.png' />";
            $results .= "<span>浏览(<b>{$article['commentcount']}</b>)</span>
			<a href='article.html' class='readall'>阅读全文</a></p></div></div>";
        }

        echo $results;
    }
}