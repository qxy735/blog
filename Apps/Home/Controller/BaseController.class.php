<?php namespace Home\Controller;

use Home\Model\MenuModel as Menu;
use Think\Controller;
use Think\Exception;
use Think\Log;

class BaseController extends Controller
{
    /**
     * 当前登录用户 ID
     *
     * @var int
     */
    protected $user_id = 0;
    /**
     * 当前登录用户权限
     *
     * @var array
     */
    protected $auths = [];
    /**
     * 是否需要导航菜单
     *
     * @var int
     */
    protected $is_need_menu = 1;

    /**
     * 初始化处理
     *
     * BaseController constructor.
     */
    public function __construct()
    {
        // 初始化父控制
        parent::__construct();

        // 获取当前登录用户权限
        $this->auths = $this->get_user_auth();

        // 设置模板所需的资源地址
        $this->set_view_url();
    }

    /**
     * 设置模板资源地址
     */
    protected function set_view_url()
    {
        // 获取基础目录
        $base_path = 'http://' . $_SERVER['SERVER_NAME'] . APP_PATH;

        // 获取资源目录地址
        $asset_path = "{$base_path}Home/Asset";

        // 处理资源目录路径地址
        $asset_path = str_replace('\\', '/', $asset_path);

        // 传递资源目录地址
        $this->assign('__ASSET__', $asset_path);

        // 传递图片资源目录地址
        $this->assign('__IMG__', "{$asset_path}/image");

        // 传递样式文件资源目录地址
        $this->assign('__CSS__', "{$asset_path}/css");

        // 传递脚本资源目录地址
        $this->assign('__JS__', "{$asset_path}/js");

        // 传递视图文件目录地址
        $this->assign('__VIEW__', "{$base_path}View");

        // 传递网站导航菜单
        if ($this->is_need_menu) {
            $this->assign('__MENU__', $this->get_navigate_menu());
        }

        // 传递当前请求地址
        $this->assign('__URI__', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
    }

    /**
     * 获取导航菜单
     *
     * @return array
     */
    protected function get_navigate_menu()
    {
        try {
            // 获取 menu 表操作 model
            $menu = D('menu');

            // 查询条件
            $where = [
                'parentid' => Menu::MENU_PARENT_VALUE,
                'type' => Menu::MENU_TYPE_FRONT,
                'enabled' => Menu::MENU_IS_ENABLED,
            ];

            // 获取首页导航菜单
            $menus = $menu->where($where)->order('displayorder desc')->getField(implode(',', $menu->columns));
            $menus = $menus ?: [];

            // 根据用户权限返回菜单
            $menus = array_filter($menus, function ($navMenu) {
                // 菜单设置了权限，则需要做验证
                if ($auth = $navMenu['auth']) {
                    // 菜单权限数组化
                    $auth = json_decode($auth, true);

                    // 组装菜单权限
                    $auth = $auth ? key($auth) . '.' . current($auth) : '';

                    // 验证菜单权限
                    if (!in_array($auth, $this->auths)) {
                        return false;
                    }
                }

                return true;
            });

            // 返回导航菜单
            return array_values($menus);
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());

            return [];
        }
    }

    /**
     * 获取当前登录用户权限
     *
     * @return array
     */
    protected function get_user_auth()
    {
        // 根据用户 ID 获取对应的用户权限
        if ($user_id = $this->user_id) {

        }

        return [];
    }

    /**
     * 截取字符串(获取指定长度的字符)
     *
     * @param $string
     * @param int $length
     * @return string
     */
    protected function substr($string, $length = 12)
    {
        $substr_string = mb_substr($string, 0, $length, 'utf-8');

        if (mb_strlen($string, 'utf-8') > $length) {
            $substr_string .= '...';
        }

        return $substr_string;
    }
}