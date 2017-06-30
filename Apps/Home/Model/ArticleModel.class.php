<?php namespace Home\Model;

class ArticleModel extends BaseModel
{
    /**
     * 公开显示
     */
    const ARTICLE_IS_PUBLIC = 1;
    /**
     * 非公开显示
     */
    const ARTICLE_IS_PRIVATE = 0;
    /**
     * 正常文章
     */
    const ARTICLE_STATUS_NORMAL = 0;
    /**
     * 草稿文章
     */
    const ARTICLE_STATUS_DRAFT = 1;
    /**
     * 已删除文章
     */
    const ARTICLE_STATUS_DELETE = 2;
    /**
     * 拒绝文章评论
     */
    const IS_COMMENT_DENY = 0;
    /**
     * 允许文章评论
     */
    const IS_COMMENT_ALLOW = 1;
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'menuid',
        'categoryid',
        'cover',
        'title',
        'content',
        'come',
        'ispublic',
        'status',
        'author',
        'visitcount',
        'commentcount',
        'downloadcount',
        'iscomment',
        'attachment',
        'coin',
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
    protected $tableName = 'articles';
}