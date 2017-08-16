<?php namespace Home\Controller;

use Home\Model\ArticleModel as Article;
use Home\Model\CategoryModel as Category;
use Home\Model\LinkModel as Link;
use Home\Model\MenuModel as Menu;
use Home\Model\TagModel as Tag;
use Think\Exception;
use Think\Log;

class ArticleController extends BaseController
{
    /**
     * 显示文章详情页面
     */
    public function detail()
    {
        // 获取菜单 ID
        $menu_id = (int)I('get.m');

        $article = $hots = $links = $softwares = $menu = [];

        try {
            // 获取文章 ID
            $id = intval(I('get.id'));

            if ($id) {
                // 根据文章 ID 获取对应的文章信息
                $article = D('article')->where([
                    'id' => $id,
                    'ispublic' => Article::ARTICLE_IS_PUBLIC,
                    'status' => Article::ARTICLE_STATUS_NORMAL,
                ])->getField('id,title,author,content,visitcount,goodcount,badcount,commentcount,createtime');

                $article = $article ? array_values($article) : [];
                $article = $article ? $article[0] : [];

                // 更新文章访问量
                if ($article) {
                    $new_visitcount = $article['visitcount'] + 1;

                    $result = D('article')->where(['id' => $id])->save(['visitcount' => $new_visitcount]);

                    if ($result) {
                        $article['visitcount'] = $new_visitcount;
                    }
                }
            }

            // 获取菜单信息
            if ($menu_id) {
                $menu = D('menu')->where([
                    'id' => $menu_id,
                    'enabled' => Menu::MENU_IS_ENABLED
                ])->getField('id,name,url');

                $menu = $menu ? array_values($menu) : [];
                $menu = $menu ? $menu[0] : [];
            }

            // 获取友情链接
            $links = D('link')->where([
                'enabled' => Link::LINK_IS_ENABLED,
            ])->order('displayorder desc,id desc')->limit(6)->getField('id,name,url');

            $links = $links ?: [];

            // 获取热门推荐
            $hots = D('article')->where([
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
            ])->order('visitcount desc,id desc')->limit(9)->getField('id,title,status');

            $hots = $hots ? array_values($hots) : [];

            // 处理文章标题
            $hots = array_map(function ($hot) {
                $hot['title'] = $this->substr($hot['title']);

                return $hot;
            }, $hots);

            // 获取软件推荐信息
            $softwares = D()->query('SELECT art.id,art.title FROM `blog_articles` as art INNER JOIN blog_categorys as cat ON art.categoryid = cat.id WHERE art.ispublic = ' . Article::ARTICLE_IS_PUBLIC . ' AND art.`status` = ' . Article::ARTICLE_STATUS_NORMAL . ' AND cat.type = ' . Category::CATEGORY_TYPE_DOWNLOAD . ' order by art.visitcount desc,art.id desc limit 9');
            $softwares = $softwares ? array_values($softwares) : [];

            // 处理文章标题
            $softwares = array_map(function ($software) {
                $software['title'] = $this->substr($software['title']);

                return $software;
            }, $softwares);
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());
        }

        // 处理文章发布时间
        if ($article) {
            $article['createtime'] = $article['createtime'] ? date('Y-m-d', $article['createtime']) : '';
        }

        // 传递文章信息
        $this->assign('article', $article);

        // 卸载空闲变量
        unset($article);

        // 传递友情链接信息
        $this->assign('links', $links);

        // 增加热门推荐显示样式名
        $hot_styles = ['red', 'orange', 'pink', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray'];

        // 传递热门推荐显示样式名
        $this->assign('hot_styles', $hot_styles);

        // 传递热门推荐信息
        $this->assign('hots', $hots);

        // 增加友情链接显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递友情链接显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 传递软件推荐文章信息
        $this->assign('softwares', $softwares);

        // 传递菜单信息
        $this->assign('current_menu', $menu);

        // 传递菜单 ID
        $this->assign('menu_id', $menu_id);

        // 显示文章详情页面
        $this->display('article/detail');
    }

    /**
     * Ajax 获取更多文章信息
     */
    public function load_more()
    {
        try {
            // 获取当前页
            $page = I('post.page', 1);

            // 每页获取 6 条数据
            $pre_page = 6;

            // 计算获取开始位置
            $start = ($page - 1) * $pre_page;

            // 获取菜单 ID
            $menu_id = (int)I('post.menu_id');

            // 获取父分类 ID
            $parent_id = (int)I('post.parent_id');

            // 获取子分类 ID
            $son_id = (int)I('post.son_id');

            // 获取分类类别
            $category_type = (int)I('post.category_type');

            // 获取是否首页加载
            $is_index = (int)I('post.is_index');

            // 获取标签 ID
            $tag_id = (int)I('post.tag');

            // 组装查询条件
            $condition = [
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
            ];

            // 获取查询关键字
            $keyword = I('post.keyword');

            // 获取是否查询关键字
            $is_search = (int)I('post.is_search');

            if ($is_search) {
                if (!$keyword && !$tag_id) {
                    return '';
                }

                if ($keyword) {
                    $condition['_string'] = "title like '%{$keyword}%' or content like '%{$keyword}%'";
                }

                // 根据标签 ID 查询
                if ($tag_id) {
                    $article_tags = D('articleTag')->where(['tagid' => $tag_id])->getField('id,articleid,tagid');
                    $article_tags = $article_tags ? array_values($article_tags) : [];

                    $article_ids = [];

                    foreach ($article_tags as $article_tag) {
                        $article_ids[] = $article_tag['articleid'];
                    }

                    $article_ids = $article_ids ? array_unique($article_ids) : [0];
                    $article_ids = implode(',', $article_ids);

                    $condition['id'] = ['in', $article_ids];

                    unset($article_tags);
                    unset($article_ids);
                }
            }

            // 根据菜单  ID 查询
            if ($menu_id) {
                if ($is_index) {
                    $condition['menuid'] = ['in', "0,{$menu_id}"];
                } else {
                    $condition['menuid'] = $menu_id;
                }
            }

            // 根据分类查询
            if ($son_id) {
                $condition['categoryid'] = $son_id;
            } elseif ($parent_id) {
                $son_category_ids = [$parent_id];

                // 组装一级文章分类查询条件
                $condition = [
                    'level' => Category::CATEGORY_LEVEL_ONE,
                    'enabled' => Category::CATEGORY_IS_ENABLED,
                    'parentid' => $parent_id
                ];

                // 获取一级文章分类信息
                $categorys = D('category')->where($condition)->getField('id,level');

                $categorys = $categorys ? array_keys($categorys) : [];

                $son_category_ids = array_merge($son_category_ids, $categorys);

                unset($categorys);

                $son_category_ids = implode(',', $son_category_ids);

                $condition['categoryid'] = ['in', $son_category_ids];
            } else {
                if (-1 != $category_type) {
                    $category_condition = ['enabled' => Category::CATEGORY_IS_ENABLED];

                    if (Category::CATEGORY_TYPE_NORMAL == $category_type) {
                        $category_condition['type'] = Category::CATEGORY_TYPE_NORMAL;
                    } elseif (Category::CATEGORY_TYPE_DOWNLOAD == $category_type) {
                        $category_condition['type'] = Category::CATEGORY_TYPE_DOWNLOAD;
                    } elseif (Category::CATEGORY_TYPE_WORK == $category_type) {
                        $category_condition['type'] = Category::CATEGORY_TYPE_WORK;
                    }

                    $son_category_ids = D('category')->where($category_condition)->getField('id,level');

                    if ($son_category_ids) {
                        $son_category_ids = implode(',', array_keys($son_category_ids));

                        $condition['categoryid'] = ['in', $son_category_ids];
                    } else {
                        $condition['id'] = 0;
                    }
                }
            }

            // 获取文章
            $articles = D('article')->where($condition)->order('id desc')->limit($start, $pre_page)->getField('id,title,cover,categoryid,author,content,visitcount,commentcount,createtime');

            // 判断文章是否存在
            if (!$articles) {
                echo '';
                exit;
            }

            $category_ids = $categorys = [];

            // 处理最新发布的文章内容和标题
            $articles = array_map(function ($article) use (&$category_ids) {
                // 获取文章分类 ID
                $category_ids[] = $article['categoryid'];

                // 处理文章标题
                $article['title'] = $this->substr($article['title']);

                // 处理文章内容
                $article['content'] = $this->substr(strip_tags($article['content']), 50);

                // 处理发布时间
                $article['createtime'] = $article['createtime'] ? date('Y-m-d H:i:s', $article['createtime']) : date('Y-m-d H:i:s');

                // 处理文章作者
                $article['author'] = $article['author'] ?: '公子禹';

                return $article;
            }, $articles);

            // 去除重复分类 ID
            $category_ids = $category_ids ? array_unique($category_ids) : [];

            // 获取分类名
            if ($category_ids) {
                $categorys = D('category')->where('id in(' . implode(',', $category_ids) . ') and enabled=' . Category::CATEGORY_IS_ENABLED)->getField('id,name');
            }

            // 获取文章所属分类名
            $articles = array_map(function ($article) use ($categorys) {
                $article['category'] = isset($categorys[$article['categoryid']]) ? $categorys[$article['categoryid']] : '禹译';

                return $article;
            }, $articles);

            // 卸载空闲变量
            unset($categorys);

            $results = '';

            // 默认图片地址
            $base_path = 'http://' . $_SERVER['SERVER_NAME'] . APP_PATH;

            // 获取资源目录地址
            $asset_path = "{$base_path}Home/Asset";

            // 处理资源目录路径地址
            $asset_path = str_replace('\\', '/', $asset_path);

            $__IMG__ = "{$asset_path}/image";

            $target = '';

            // 定义链接打开方式
            if ($is_search) {
                $target = 'target="_blank"';
            }

            // 处理返回形式
            foreach ($articles as $article) {
                $results .= "<div class='article'><div class='article-img'>";

                if ($article['cover']) {
                    $results .= "<img src='{$article['cover']}' />";
                } else {
                    $results .= "<img src='{$__IMG__}/article.jpg' />";
                }

                $results .= "</div><div class='article-content'><h3><a href='/article/detail/id/{$article['id']}' {$target}>{$article['title']}</a></h3>";
                $results .= "<p><img src='{$__IMG__}/my-min.jpg' />";
                $results .= "<i>{$article['author']}</i><span>发布时间: {$article['createtime']}</span>
			<span>归属: {$article['category']}</span></p><p><a href='/article/detail/id/{$article['id']}' {$target}>{$article['content']}</a></p>";
                $results .= "<p class='use'><img src='{$__IMG__}/reviewbg.png' />";
                $results .= "<span>评论(<b>{$article['visitcount']}</b>)</span>";
                $results .= "<img src='{$__IMG__}/browsebg.png' />";
                $results .= "<span>浏览(<b>{$article['commentcount']}</b>)</span>
			<a href='/article/detail/id/{$article['id']}' {$target} class='readall'>阅读全文</a></p></div></div>";
            }

            unset($articles);

            echo $results;
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());

            echo '';
        }
    }

    /**
     * 加载更多我的日记信息
     */
    public function load_more_note()
    {
        try {
            // 获取当前页
            $page = I('post.page', 1);

            // 每页获取 6 条数据
            $pre_page = 6;

            // 计算获取开始位置
            $start = ($page - 1) * $pre_page;

            // 获取菜单 ID
            $menu_id = (int)I('post.menu_id');

            // 组装查询条件
            $condition = [
                'ispublic' => Article::ARTICLE_IS_PUBLIC,
                'status' => Article::ARTICLE_STATUS_NORMAL,
                'menuid' => $menu_id,
            ];

            // 获取我的日记
            $articles = D('article')->where($condition)->order('id desc')->limit($start, $pre_page)->getField('id,content,createtime');

            // 判断文章是否存在
            if (!$articles) {
                echo '';
                exit;
            }

            $article_ids = [];

            // 处理我的日记发布时间
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

            // 标签显示样式名
            $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

            $results = '';

            // 处理返回形式
            foreach ($articles as $article) {
                $results .= "<div class='note'><span class='line-date'>{$article['createtime']}</span>";
                $results .= "<p>{$article['content']}</p><ul>";
                foreach ($article['tags'] as $tag) {
                    $results .= "<li class='{$tag_styles[rand(0, 7)]}'>{$tag}</li>";
                }
                $results .= "</ul></div>";
            }

            unset($articles);

            echo $results;
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());

            echo '';
        }
    }

    /**
     * 检索文章
     */
    public function search()
    {
        $articles = $links = $softwares = [];

        // 获取搜索关键字
        $keyword = I('post.keyword');

        // 获取标签 ID
        $tag_id = (int)I('get.tag');

        try {
            if ($keyword || $tag_id) {
                // 组装查询条件
                $condition = [
                    'ispublic' => Article::ARTICLE_IS_PUBLIC,
                    'status' => Article::ARTICLE_STATUS_NORMAL,
                ];

                if ($keyword) {
                    $condition['_string'] = "title like '%{$keyword}%' or content like '%{$keyword}%'";
                }

                if ($tag_id) {
                    // 根据标签 ID 获取文章 ID
                    $article_tags = D('articleTag')->where(['tagid' => $tag_id])->getField('id,articleid,tagid');
                    $article_tags = $article_tags ? array_values($article_tags) : [];

                    $article_ids = [];

                    foreach ($article_tags as $article_tag) {
                        $article_ids[] = $article_tag['articleid'];
                    }

                    $article_ids = $article_ids ? array_unique($article_ids) : [0];
                    $article_ids = implode(',', $article_ids);

                    $condition['id'] = ['in', $article_ids];

                    unset($article_tags);
                    unset($article_ids);
                }

                // 获取文章
                $articles = D('article')->where($condition)->order('id desc')->limit(6)->getField('id,title,cover,categoryid,author,content,visitcount,commentcount,createtime');

                $articles = $articles ?: [];

                $category_ids = $categorys = [];

                // 处理最新发布的文章内容和标题
                $articles = array_map(function ($article) use (&$category_ids) {
                    // 获取文章分类 ID
                    $category_ids[] = $article['categoryid'];

                    // 处理文章标题
                    $article['title'] = $this->substr($article['title']);

                    // 处理文章内容
                    $article['content'] = $this->substr(strip_tags($article['content']), 50);

                    // 处理发布时间
                    $article['createtime'] = $article['createtime'] ? date('Y-m-d H:i:s', $article['createtime']) : date('Y-m-d H:i:s');

                    // 处理文章作者
                    $article['author'] = $article['author'] ?: '公子禹';

                    return $article;
                }, $articles);

                // 去除重复分类 ID
                $category_ids = $category_ids ? array_unique($category_ids) : [];

                // 获取分类名
                if ($category_ids) {
                    $categorys = D('category')->where('id in(' . implode(',', $category_ids) . ') and enabled=' . Category::CATEGORY_IS_ENABLED)->getField('id,name');
                }

                // 获取文章所属分类名
                $articles = array_map(function ($article) use ($categorys) {
                    $article['category'] = isset($categorys[$article['categoryid']]) ? $categorys[$article['categoryid']] : '禹译';

                    return $article;
                }, $articles);

                // 卸载空闲变量
                unset($categorys);
            }

            // 获取友情链接
            $links = D('link')->where([
                'enabled' => Link::LINK_IS_ENABLED,
            ])->order('displayorder desc,id desc')->limit(6)->getField('id,name,url');

            $links = $links ?: [];

            // 获取软件推荐信息
            $softwares = D()->query('SELECT art.id,art.title FROM `blog_articles` as art INNER JOIN blog_categorys as cat ON art.categoryid = cat.id WHERE art.ispublic = ' . Article::ARTICLE_IS_PUBLIC . ' AND art.`status` = ' . Article::ARTICLE_STATUS_NORMAL . ' AND cat.type = ' . Category::CATEGORY_TYPE_DOWNLOAD . ' order by art.visitcount desc,art.id desc limit 9');
            $softwares = $softwares ? array_values($softwares) : [];

            // 处理文章标题
            $softwares = array_map(function ($software) {
                $software['title'] = $this->substr($software['title']);

                return $software;
            }, $softwares);
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e);
        }

        // 传递搜索到的文章内容
        $this->assign('articles', $articles);

        // 传递查询关键字
        $this->assign('keyword', $keyword);

        // 增加友情链接显示样式名
        $tag_styles = ['pink', 'blue1', 'orange', 'green', 'blue2', 'yellow', 'blue3', 'red'];

        // 传递友情链接显示样式名
        $this->assign('tag_styles', $tag_styles);

        // 传递友情链接信息
        $this->assign('links', $links);

        // 增加热门推荐显示样式名
        $hot_styles = ['red', 'orange', 'pink', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray'];

        // 传递热门推荐显示样式名
        $this->assign('hot_styles', $hot_styles);

        // 传递软件推荐文章信息
        $this->assign('softwares', $softwares);

        // 传递标签 ID
        $this->assign('tag_id', $tag_id);

        // 加载文章搜索页面
        $this->display('article/search');
    }

    /**
     * 更新点赞数
     */
    public function good()
    {
        try {
            // 获取文章 ID
            $id = (int)I('post.id');

            // 判断文章 ID 是否有效
            if (!$id) {
                echo json_encode(['success' => false, 'msg' => '文章ID无效']);
                exit;
            }

            // 获取文章信息
            $article = D('article')->where(['id' => $id])->getField('id,goodcount,badcount');
            $article = $article ? array_values($article) : [];
            $article = $article ? $article[0] : [];

            // 判断文章信息是否存在
            if (!$article) {
                echo json_encode(['success' => false, 'msg' => '文章不存在']);
                exit;
            }

            // 获取点赞数
            $good = $article['goodcount'];

            // 更新点赞数
            $result = D('article')->where(['id' => $id])->save(['goodcount' => $good + 1]);

            // 判断更新是否成功
            if ($result) {
                echo json_encode(['success' => true, 'msg' => '操作成功']);
            } else {
                echo json_encode(['success' => false, 'msg' => '操作失败']);
            }
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());

            echo json_encode(['success' => false, 'msg' => '操作失败']);
        }
    }

    /**
     * 更新差评数
     */
    public function bad()
    {
        try {
            // 获取文章 ID
            $id = (int)I('post.id');

            // 判断文章 ID 是否有效
            if (!$id) {
                echo json_encode(['success' => false, 'msg' => '文章ID无效']);
                exit;
            }

            // 获取文章信息
            $article = D('article')->where(['id' => $id])->getField('id,goodcount,badcount');
            $article = $article ? array_values($article) : [];
            $article = $article ? $article[0] : [];

            // 判断文章信息是否存在
            if (!$article) {
                echo json_encode(['success' => false, 'msg' => '文章不存在']);
                exit;
            }

            // 获取差评数
            $bad = $article['badcount'];

            // 更新点赞数
            $result = D('article')->where(['id' => $id])->save(['badcount' => $bad + 1]);

            // 判断更新是否成功
            if ($result) {
                echo json_encode(['success' => true, 'msg' => '操作成功']);
            } else {
                echo json_encode(['success' => false, 'msg' => '操作失败']);
            }
        } catch (Exception $e) {
            // 记录错误日志信息
            Log::write($e->getMessage());

            echo json_encode(['success' => false, 'msg' => '操作失败']);
        }
    }
}