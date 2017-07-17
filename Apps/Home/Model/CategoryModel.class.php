<?php namespace Home\Model;

class CategoryModel extends BaseModel
{
    /**
     * 启用分类
     */
    const CATEGORY_IS_ENABLED = 1;
    /**
     * 禁用分类
     */
    const CATEGORY_IS_DISABLED = 0;
    /**
     * 下载类型
     */
    const CATEGORY_TYPE_DOWNLOAD = 1;
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'parentid',
        'name',
        'level',
        'enabled',
        'type',
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
    protected $tableName = 'categorys';
}