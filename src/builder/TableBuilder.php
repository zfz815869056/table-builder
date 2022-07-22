<?php
declare (strict_types=1);

namespace zhuzhihao\src\builder;

use think\facade\View;

class TableBuilder
{

    /**
     * @var string 模板路径
     */
    private $_template = '';

    /**
     * @var array 模板变量
     */
    private $_vars = [
        'page_title'       => '',        // 页面标题
        'page_tips'        => '',        // 页面提示
        'page_tips_top'    => '',        // 页面提示[top]
        'page_tips_search' => '',        // 页面提示[search]
        'page_tips_bottom' => '',        // 页面提示[bottom]
        'page_size'        => '',        // 每页显示的行数
        'tips_type'        => '',        // 页面提示类型
        'extra_js'         => '',        // 额外JS代码
        'extra_css'        => '',        // 额外CSS代码
        'extra_html'       => '',        // 额外HTML代码
        'columns'          => [],        // 表格列集合
        'right_buttons'    => [],        // 表格右侧按钮
        'top_buttons'      => [],        // 顶部栏按钮组[toolbar]
        'data_url'         => '',        // 表格数据源
        'add_url'          => '',        // 默认的新增地址
        'edit_url'         => '',        // 默认的修改地址
        'del_url'          => '',        // 默认的删除地址
        'export_url'       => '',        // 默认的导出地址
        'sort_url'         => '',        // 默认的排序地址
        'search'           => [],        // 搜索参数
        'js_url'           => [],        // 额外JS代码(文件名)
        'totalRow'         => 'false',        // 是否开启合计行
        'switch_tool'      => [],    //开关绑定栏,
        'extra_map'        => []
    ];


    private static $instance;


    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 私有化构造函数
     */
    private function __construct()
    {
        // 初始化
        $this->initialize();
    }

    /**
     * 初始化
     */
    protected function initialize()
    {

        // 设置默认模版
        $this->_template = 'form/index';

        // 设置默认URL
        $this->_vars['data_url'] = url('index');
        $this->_vars['add_url'] = url('add');
        $this->_vars['edit_url'] = url('edit');
        $this->_vars['del_url'] = url('del');
        $this->_vars['export_url'] = url('export');

    }


    /**
     * 渲染模版
     * @param string $template 模板文件名或者内容
     * @param bool $renderContent 是否渲染内容
     * @return string
     * @throws \Exception
     */
    public function fetch(string $template = '')
    {
        // 单独设置模板
        if ($template != '') {
            $this->_template = $template;
        }
        //格式化右侧按钮
        foreach ($this->_vars['right_buttons'] as &$vo) {
            $vo['json'] = json_encode($vo);
        }
        $name = request()->controller() . '_' . request()->action();

        if ($this->_vars['extra_map']) {
            ExtraMapFront::getInstance()->setExtraMap($this->_vars['extra_map'], $name);
        }

        $this->_vars['extra_map'] = [
            'name' => $name
        ];
        View::assign($this->_vars);
        return View::fetch($this->_template);
    }


    public function addColumns(TableFront $tableFront)
    {
        $this->_vars['columns'] = $tableFront->getTable();
        return $this;
    }

    /**
     * 添加一个右侧按钮
     * @param string $type 按钮类型：edit/delete/default
     * @param array $attribute 按钮属性
     * @param array $extra 扩展参数(待用)
     * @return $this
     */
    public function addRightButton($type = '', $attribute = [])
    {
        switch ($type) {
            // 预览按钮
            case 'preview':
                // 默认属性
                $btn_attribute = [
                    'type'   => 'preview',
                    'title'  => '查看',
                    'icon'   => '',
                    'class'  => 'layui-btn layui-btn-sm layui-btn-normal',
                    'url'    => url('preview')->build(),
                    'width'  => 600,
                    'height' => 800
                ];
                break;
            // 编辑按钮
            case 'edit':
                // 默认属性
                $btn_attribute = [
                    'type'   => 'edit',
                    'title'  => '编辑',
                    'icon'   => '',
                    'class'  => 'layui-btn layui-btn-sm',
                    'url'    => url('edit', ['name' => request()->controller() . '_' . request()->action()])->build(),
                    'width'  => isset($attribute['width']) ? $attribute['width'] : 600,
                    'height' => isset($attribute['height']) ? $attribute['height'] : 800,
                ];
                break;

            // 删除按钮(不可恢复)
            case 'delete':
                // 默认属性
                $btn_attribute = [
                    'type'   => 'delete',
                    'title'  => '删除',
                    'icon'   => 'far fa-trash-alt',
                    'class'  => 'layui-btn-danger layui-btn layui-btn-sm',
                    'url'    => url('delete')->build(),
                    'width'  => 850,
                    'height' => 500
                ];
                break;

            // 自定义按钮
            default:
                // 默认属性
                $btn_attribute = [
                    'title' => '自定义登录按钮',
                    'icon'  => 'far fa-trash-alt',
                    'class' => 'layui-btn layui-btn-normal layui-btn-sm',
                ];
                break;
        }
        // 合并自定义属性
        if ($attribute && is_array($attribute)) {
            $btn_attribute = array_merge($btn_attribute, $attribute);
        }

        $this->_vars['right_buttons'][] = $btn_attribute;
        return $this;
    }

    /**
     * 添加多个右侧按钮
     * @param array|string $buttons 按钮类型
     *                              例如：
     *                              $builder->addRightButtons('edit');
     *                              $builder->addRightButtons('edit,delete');
     *                              $builder->addRightButtons(['edit', 'delete']);
     *                              $builder->addRightButtons(['edit' => ['title' => '查看'], 'delete']);
     * @return $this
     */
    public function addRightButtons($buttons = [])
    {
        if (!empty($buttons)) {
            $buttons = is_array($buttons) ? $buttons : explode(',', $buttons);
            foreach ($buttons as $key => $value) {
                if (is_numeric($key)) {
                    $this->addRightButton($value);
                } else {
                    $this->addRightButton(trim($key), $value);
                }
            }
        }
        return $this;
    }


    /**
     * 设置搜索参数
     * @param array $items - 设置的搜索
     * @param bool $time -- 是否开启时间搜索，默认true
     * @return $this
     */
    public function setSearch(SearchFront $searchFront, $time = true)
    {

        $search = [];
        if (!empty($searchFront->getSearch())) {

            foreach ($searchFront->getSearch() as $key => $vo) {
                $data['key'] = $vo[0] ?? '';  // 字段key
                $data['name'] = $vo[1] ?? '';  // 字段名称
                $data['type'] = $vo[2] ?? '';  // 类型：input/select
                $data['value'] = $vo[3] ?? '';  //默认值
                $search[] = $data;
            }
            $this->_vars['search'] = $search;
        }
        $this->_vars['search_time'] = $time;
        return $this;
    }

    /**
     * 添加一个顶部按钮[目前只能新窗口打开，暂时不考虑弹出层]
     * @param string $type 按钮类型：add/edit/del/export/build/default
     * @param array $attribute 按钮属性
     * @return $this
     */
    public function addTopButton($type = '', $attribute = [])
    {
        switch ($type) {

            // 新增按钮
            case 'add':
                // 默认属性
                $btn_attribute = [
                    'title'   => '新增',
                    'icon'    => 'layui-icon-add-circle-fine',
                    'class'   => 'layui-btn layui-btn-normal layui-btn-sm',
                    'onclick' => 'buttons.add($(this))',
                    'href'    => url('add', ['name' => request()->controller() . '_' . request()->action()])->build(),
                    'width'   => isset($attribute['width']) ? $attribute['width'] : 600,
                    'height'  => isset($attribute['height']) ? $attribute['height'] : 800,
                ];
                break;

            // 修改按钮
            case 'edit':
                // 默认属性
                $btn_attribute = [
                    'title'   => '修改',
                    'icon'    => 'fa fa-edit',
                    'class'   => 'btn btn-primary single disabled',
                    'onclick' => 'buttons.edit($(this))',
                    'href'    => url('edit', ['name' => request()->controller() . '_' . request()->action()])->build(),
                    'width'   => 600,
                    'height'  => 800
                ];
                break;

            // 批量删除按钮
            case 'deleteAll':
                // 默认属性
                $btn_attribute = [
                    'title'   => '批量删除',
                    'icon'    => 'layui-icon layui-icon-delete',
                    'class'   => 'layui-btn layui-btn-danger layui-btn-sm',
                    'href'    => url('delete')->build(),
                    'onclick' => 'buttons.removeAll($(this))'
                ];
                break;

            // 导出按钮
            case 'export':
                // 默认属性
                $btn_attribute = [
                    'title'   => '导出',
                    'icon'    => 'layui-icon-export',
                    'class'   => 'layui-btn layui-bg-cyan layui-btn-sm',
                    'href'    => url('export', request()->param()),
                    'onclick' => 'buttons.exportExcel($(this))'
                ];
                break;

            // 自定义按钮 - 需自己编写和绑定点击事件
            default:
                // 默认属性
                $btn_attribute = [
                    'title'   => $attribute['title'],
                    'icon'    => $attribute['icon'],
                    'class'   => 'layui-btn layui-btn-danger layui-btn-sm',
                    'onclick' => $attribute['onclick'],
                    'url'     => $attribute['url'],
                ];
                break;
        }

        // 合并自定义属性
        if ($attribute && is_array($attribute)) {
            $btn_attribute = array_merge($btn_attribute, $attribute);
        }
        $this->_vars['top_buttons'][] = $btn_attribute;
        return $this;
    }

    /**
     * 一次性添加多个顶部按钮
     * @param array|string $buttons 按钮组
     *                              例如：
     *                              addTopButtons('add')
     *                              addTopButtons('add, edit, del')
     *                              addTopButtons(['add', 'del'])
     *                              addTopButtons(['add' => ['title' => '增加'], 'del'])
     * @return $this
     */
    public function addTopButtons($buttons = [])
    {
        if (!empty($buttons)) {
            $buttons = is_array($buttons) ? $buttons : explode(',', $buttons);
            foreach ($buttons as $key => $value) {
                if (is_numeric($key)) {
                    // key为数字则直接添加一个按钮
                    $this->addTopButton($value);
                } else {
                    // key不为数字则需设置属性，去除前后空格
                    $this->addTopButton(trim($key), $value);
                }
            }
        }
        return $this;
    }

    /**
     * 添加开关绑定栏
     * @param array $tool 开关组
     *                    例如：['name'=>'status','pk'=>'id','text'=>'锁定|正常']
     * @return $this
     */
    public function addSwitchTool($tool = [])
    {
        if (!empty($tool)) {
            $this->_vars['switch_tool'] = array_merge($this->_vars['switch_tool'], $tool);
        }
        return $this;
    }


    /**
     * 额外JS代码，
     * @param array $url
     */
    public function addJsUrl($url = [])
    {
        $this->_vars['js_url'] = $url;
        return $this;
    }

    /**
     * 设置是否开启合计行
     * @param string $bol => 'true' or 'false'
     * @return $this
     */
    public function setTotalRaw($bol = 'false')
    {
        $this->_vars['totalRow'] = $bol;
        return $this;
    }


    /**
     * 添加初始查询条件
     * @param array $map
     * @return $this
     */
    public function addExtraMap($map = [])
    {
        $this->_vars['extra_map'] = $map;
        return $this;
    }


}
