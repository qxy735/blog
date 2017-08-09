<?php namespace Home\Controller;

use Home\Model\NoticeModel as Notice;
use Think\Exception;
use Think\Log;

class NoticeController extends BaseController
{
    /**
     * 显示网站公告页面
     */
    public function index()
    {
        $notice = [];

        // 获取公告 ID
        $id = (int)I('get.id');

        try {
            $notice = D('notice')->where(['id' => $id, 'status' => Notice::NOTICE_STATUS_SHOW])->getField('id,title,content,sendname,lastoperate');
            $notice = $notice ? array_values($notice) : [];
            $notice = $notice ? $notice[0] : [];

            if ($notice) {
                $notice['lastoperate'] = $notice['lastoperate'] ? date('Y-m-d H:i:s', $notice['lastoperate']) : '';
            }
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递公告信息
        $this->assign('notice', $notice);

        $this->display('message/notice');
    }
}