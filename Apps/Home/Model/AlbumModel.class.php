<?php namespace Home\Model;

class AlbumModel extends BaseModel
{
    /**
     * 启用菜单
     */
    const ALBUM_IS_ENABLED = 1;
    /**
     * 禁用菜单
     */
    const ALBUM_IS_DISABLED = 0;
    /**
     * 表字段
     *
     * @var array
     */
    public $columns = [
        'id',
        'name',
        'cover',
        'photos',
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
    protected $tableName = 'albums';
}