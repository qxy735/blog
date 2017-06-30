<?php namespace Home\Model;

class LinkModel extends BaseModel
{
    /**
     * 启用友情链接
     */
    const LINK_IS_ENABLED = 1;
    /**
     * 关闭友情链接
     */
    const LINK_IS_DISABLED = 0;
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'name',
        'url',
        'enabled',
        'displayorder',
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
    protected $tableName = 'links';
}