<?php namespace Home\Controller;

use Home\Model\NoticeModel as Notice;
use Think\Exception;
use Think\Log;

class IndexController extends BaseController
{
    public function index()
    {
        $notices = [];

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
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递公告信息
        $this->assign('notices', $notices);

        $this->display('index/index');
    }
}