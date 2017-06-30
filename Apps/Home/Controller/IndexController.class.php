<?php namespace Home\Controller;

use Home\Model\ArticleModel as Article;
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

            // 获取友情链接
            $links = D('link')->where([
                'enabled' => Link::LINK_IS_ENABLED,
            ])->order('displayorder desc,id desc')->limit(6)->getField('id,name,url');

            // 获取热门推荐
            $hots = D('article')->where([
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
            ])->order('visitcount desc,id desc')->limit(9)->getField('id,title,status');

            // 处理文章标题
            $hots = array_map(function ($hot) {
                $hot['title'] = $this->substr($hot['title']);

                return $hot;
            }, $hots);
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

        // 显示首页页面
        $this->display('index/index');
    }
}