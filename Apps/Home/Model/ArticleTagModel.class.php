<?php namespace Home\Model;

class ArticleTagModel extends BaseModel
{
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'articleid',
        'tagid',
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
    protected $tableName = 'article_tags';
}