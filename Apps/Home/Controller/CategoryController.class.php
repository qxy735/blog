<?php namespace Home\Controller;

use Think\Exception;
use Think\Log;
use Home\Model\CategoryModel as Category;

class CategoryController extends BaseController
{
    /**
     * 获取子分类信息
     */
    public function get_son_category()
    {
        // 获取父分类 ID
        $parent_id = (int)I('post.parent_id');

        // 获取菜单 ID
        $menu_id = (int)I('post.menu_id');

        $result = "<li><span>分类 :</span></li><li><a href='/tech/article/m/{$menu_id}/pid/{$parent_id}/cid/0'>全部</a></li>";

        try {
            // 组装查询条件
            $condition = [
                'level' => Category::CATEGORY_LEVEL_ONE,
                'enabled' => Category::CATEGORY_IS_ENABLED,
                'type' => Category::CATEGORY_TYPE_NORMAL,
            ];

            // 根据父 ID 查询对应子分类信息
            if ($parent_id) {
                $condition['parentid'] = $parent_id;
            }

            // 获取对应子分类信息
            $son_categorys = D('category')->where($condition)->order('displayorder desc,id desc');

            if (!$parent_id) {
                $son_categorys->limit(8);
            }

            $son_categorys = $son_categorys->getField('id,name,level');

            // 子分类信息不存在，则返回空
            if (!$son_categorys) {
                echo $result;
                exit;
            }

            foreach ($son_categorys as $son_category) {
                $result .= "<li><a href='/tech/article/m/{$menu_id}/pid/{$parent_id}/cid/{$son_category['id']}'>{$son_category['name']}</a></li>";
            }

            unset($son_categorys);

            echo $result;
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());

            echo $result;
        }
    }
}

