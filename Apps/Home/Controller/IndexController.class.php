<?php namespace Home\Controller;

use Home\Model\NoticeModel as Notice;
use Home\Model\TagModel as Tag;
use Think\Exception;
use Think\Log;

class IndexController extends BaseController
{
    public function index()
    {
        $notices = $tags = [];

        try {
            // 获取公告信息，默认取三条最新公告信息
            $notices = D('notice')->where([
                'status' => Notice::NOTICE_STATUS_SHOW,
            ])->order('id desc')->limit(3)->getField('id,title,status');

            // 处理公告信息
            $notices = $notices ? array_values($notices) : [];

            // 处理公告标题
            $notices = array_map(function ($notice) {
                $title = mb_substr($notice['title'], 0, 12, 'utf-8');

                if (mb_strlen($notice['title'], 'utf-8') > 12) {
                    $title .= '...';
                }

                $notice['title'] = $title;

                return $notice;
            }, $notices);

            // 获取热门标签
            $tags = D('tag')->where([
                'enabled' => Tag::TAG_IS_ENABLED,
                'ishot' => Tag::TAG_IS_HOT,
            ])->order('id desc')->limit(8)->getField('id,name,ishot');
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 增加标签显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递公告信息
        $this->assign('notices', $notices);

        // 传递标签信息
        $this->assign('tags', $tags);

        // 传递标签显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 显示首页页面
        $this->display('index/index');
    }
}