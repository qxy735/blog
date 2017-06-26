<?php namespace Home\Model;

class NoticeModel extends BaseModel
{
    /**
     * 待显示
     */
    const NOTICE_STATUS_WAIT = 0;
    /**
     * 显示中
     */
    const NOTICE_STATUS_SHOW = 1;
    /**
     * 预显示
     */
    const NOTICE_STATUS_READY = 2;
    /**
     * 已关闭
     */
    const NOTICE_STATUS_CLOSE = 3;
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'title',
        'content',
        'sendid',
        'sendname',
        'status',
        'showtime',
        'createtime',
        'creator',
        'lastoperate',
        'lastoperator',
    ];
    /**
     * 定义表名
     *
     * @var string
     */
    protected $tableName = 'notices';
}