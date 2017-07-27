<?php namespace Home\Controller;

use Home\Model\ArticleModel as Article;
use Home\Model\TagModel as Tag;
use Home\Model\MenuModel as Menu;
use Think\Exception;
use Think\Log;

class NoteController extends BaseController
{
    /**
     * 显示我的日记页面
     */
    public function article()
    {
        $articles = $article_ids = $tags = $menu = [];

        // 获取菜单 ID
        $menu_id = (int)I('get.m');

        try {
            // 组装文章查询条件
            $condition = [
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
                'menuid' => $menu_id,
            ];

            // 获取最新发布的文章
            $articles = D('article')->where($condition)
                ->order('id desc')
                ->limit(6)
                ->getField('id,content,createtime');

            $articles = $articles ?: [];

            // 处理最新发布的文章内容和标题
            $articles = array_map(function ($article) use (&$article_ids) {
                // 获取文章 ID
                $article_ids[] = $article['id'];

                // 处理发布时间
                $article['createtime'] = $article['createtime'] ? date('Y-m-d', $article['createtime']) : date('Y-m-d');

                return $article;
            }, $articles);

            // 根据文章 ID 获取对应的文章标签信息
            if ($article_ids) {
                $article_ids = implode(',', $article_ids);

                $tags = D()->query("select art_tag.articleid,tag.name from `blog_article_tags` as art_tag LEFT JOIN `blog_tags` as tag ON art_tag.tagid = tag.id WHERE art_tag.articleid in({$article_ids}) AND tag.enabled=" . Tag::TAG_IS_ENABLED);
            }

            // 获取文章标签信息
            $articles = array_map(function ($article) use ($tags) {
                $tag_names = [];

                foreach ($tags as $tag) {
                    if ($tag['articleid'] == $article['id']) {
                        $tag_names[] = $tag['name'];
                    }
                }

                $article['tags'] = $tag_names;

                return $article;
            }, $articles);

            unset($article_ids);
            unset($tags);

            // 获取菜单信息
            if ($menu_id) {
                $menu = D('menu')->where([
                    'id' => $menu_id,
                    'enabled' => Menu::MENU_IS_ENABLED
                ])->getField('id,name,url');

                $menu = $menu ? array_values($menu) : [];
                $menu = $menu ? $menu[0] : [];
            }
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 传递文章信息
        $this->assign('articles', $articles);

        // 传递当前日期
        $this->assign('current_date', date('Y-m-d'));

        // 增加标签显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递标签显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 传递菜单信息
        $this->assign('current_menu', $menu);

        // 传递菜单 ID
        $this->assign('menu_id', $menu_id);

        $this->display('article/note');
    }
}