<?php namespace Home\Controller;

use Home\Model\ArticleModel as Article;
use Home\Model\CategoryModel as Category;
use Home\Model\LinkModel as Link;
use Home\Model\NoticeModel as Notice;
use Home\Model\TagModel as Tag;
use Think\Exception;
use Think\Log;

class IndexController extends BaseController
{
    /**
     * 显示网站首页
     */
    public function index()
    {
        $notices = $tags = $links = $articles = $hots = [];

        try {
            // 获取公告信息，默认取三条最新公告信息
            $notices = D('notice')->where([
                'status' => Notice::NOTICE_STATUS_SHOW,
            ])->order('id desc')->limit(3)->getField('id,title,status');

            // 处理公告信息
            $notices = $notices ? array_values($notices) : [];

            // 处理公告标题
            $notices = array_map(function ($notice) {
                $notice['title'] = $this->substr($notice['title']);

                return $notice;
            }, $notices);

            // 获取热门标签
            $tags = D('tag')->where([
                'enabled' => Tag::TAG_IS_ENABLED,
                'ishot' => Tag::TAG_IS_HOT,
            ])->order('id desc')->limit(8)->getField('id,name,ishot');

            $tags = $tags ?: [];

            // 获取友情链接
            $links = D('link')->where([
                'enabled' => Link::LINK_IS_ENABLED,
            ])->order('displayorder desc,id desc')->limit(6)->getField('id,name,url');

            $links = $links ?: [];

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

            // 获取最新发布的文章
            $articles = D('article')->where([
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
            ])->order('id desc')->limit(6)->getField('id,title,cover,categoryid,author,content,visitcount,commentcount,createtime');

            $articles = $articles ?: [];

            $category_ids = $categorys = [];

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
                $categorys = D('category')->where('id in(' . implode(',', $category_ids) . ') and enabled=' . Category::CATEGORY_IS_ENABLED)->getField('id,name');
            }

            // 获取文章所属分类名
            $articles = array_map(function ($article) use ($categorys) {
                $article['category'] = isset($categorys[$article['categoryid']]) ? $categorys[$article['categoryid']] : '禹译';

                return $article;
            }, $articles);

            // 卸载空闲变量
            unset($categorys);
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 增加标签显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递标签显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 传递公告信息
        $this->assign('notices', $notices);

        // 传递标签信息
        $this->assign('tags', $tags);

        // 传递友情链接信息
        $this->assign('links', $links);

        // 增加热门推荐显示样式名
        $hot_styles = ['red', 'orange', 'pink', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray'];

        // 传递热门推荐显示样式名
        $this->assign('hot_styles', $hot_styles);

        // 传递热门推荐信息
        $this->assign('hots', $hots);

        // 传递文章信息
        $this->assign('articles', $articles);

        // 卸载空闲变量
        unset($articles);

        // 传递当前日期
        $this->assign('date', date('Y-m-d'));

        // 显示首页页面
        $this->display('index/index');
    }
}