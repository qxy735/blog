<?php namespace Home\Model;

class PhotoModel extends BaseModel
{
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'albumid',
        'name',
        'url',
        'description',
        'liked',
        'click',
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
    protected $tableName = 'photos';
}