<?php namespace Home\Model;

class TagModel extends BaseModel
{
    /**
     * 启用标签
     */
    const TAG_IS_ENABLED = 1;
    /**
     * 关闭标签
     */
    const TAG_IS_DISABLED = 0;
    /**
     * 热门标签
     */
    const TAG_IS_HOT = 1;
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'name',
        'enabled',
        'ishot',
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
    protected $tableName = 'tags';
}