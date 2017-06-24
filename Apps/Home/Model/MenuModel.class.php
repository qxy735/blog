<?php namespace Home\Model;

class MenuModel extends BaseModel
{
    /**
     * 启用菜单
     */
    const MENU_IS_ENABLED = 1;
    /**
     * 禁用菜单
     */
    const MENU_IS_DISABLED = 0;
    /**
     * 父级菜单
     */
    const MENU_PARENT_VALUE = 0;
    /**
     * 前台菜单类型
     */
    const MENU_TYPE_FRONT = 0;
    /**
     * 后台菜单类型
     */
    const MENU_TYPE_ADMIN = 1;
    /**
     * 微信菜单类型
     */
    const MENU_TYPE_WEIXIN = 2;
    /**
     * 定义表名
     *
     * @var string
     */
    protected $tableName = 'menus';
}