<?php namespace Home\Controller;

use Home\Model\CategoryModel as Category;
use Think\Exception;
use Think\Log;

class TechController extends BaseController
{
    /**
     * 显示技术分享页面
     */
    public function index()
    {
        $directions = $categorys = [];

        try {
            // 获取顶级分类信息
            $directions = D('category')->where([
                'level' => Category::CATEGORY_LEVEL_TOP,
                'enabled' => Category::CATEGORY_IS_ENABLED,
                'type' => Category::CATEGORY_TYPE_NORMAL
            ])->order('displayorder desc,id desc')->getField('id,name,level');

            $directions = $directions ? array_values($directions) : [];

            // 获取一级文章分类信息
            $categorys = D('category')->where([
                'level' => Category::CATEGORY_LEVEL_ONE,
                'enabled' => Category::CATEGORY_IS_ENABLED,
                'type' => Category::CATEGORY_TYPE_NORMAL
            ])->order('displayorder desc,id desc')->limit(8)->getField('id,name,level');

            $categorys = $categorys ? array_values($categorys) : [];
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递顶级文章分类信息
        $this->assign('directions', $directions);

        // 传递一级文章分类信息
        $this->assign('categorys', $categorys);

        // 显示技术分享页面
        $this->display('article/tech');
    }
}