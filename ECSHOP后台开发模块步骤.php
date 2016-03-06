ECSHOP后台开发模块步骤
一、建数据库
二、添加到后台导航栏并配置相关语言包
三、权限配置
四、添加增删查改
五、增加其他功能（复制，搜索（暂时调不出来页面），排序，转移，AJAX）


以添加支付信息模块为例

第一步首先我们用phpmyadmin建一个支付表，表名：ecs_pay表字段：pay_id,pay_name,pay_info,pay_bank,pay_credit,pay_state第二步添加到后台左侧导航栏并配置权限和相关语言包
共修改四个文件inc_priv.php、  inc_menu.php 、priv_action.php、common.php

1.打开languages\zh_cn\admin\common.php
找到/* 菜单分类部分 */ 添加       
$_LANG['18_pay'] = '支付管理';

文件末尾添加
/* 支付管理 */
$_LANG['02_pay_list'] = '支付人信息';
$_LANG['03_pay_charge'] = '账户充值';
$_LANG['04_pay_record'] = '流水记账';

配其他相关的语言包
$_LANG['pay_name'] = '支付人';
$_LANG['pay_info'] = '支付信息';
$_LANG['pay_bank'] = '开户银行';
$_LANG['pay_credit'] = '开户帐号';
$_LANG['pay_state'] = '状态';

2.打开admin\includes\inc_menu.php
末尾添加
$modules['18_pay']['02_pay_info']     = 'pay.php?act=list';
$modules['18_pay']['03_pay_charge']   = 'pay.php?act=charge';
$modules['18_pay']['04_pay_record']         = 'pay.php?act=record';

OK，菜单栏显示

第三步
配置权限体系(priv_action.php ，inc_priv.php)
1.在表ecs_admin_action 里面添加模块字段pay 、pay_manage、 pay_drop   
parent_id = 0的为顶级栏目，其他子栏目的操作，都继承了parent_id 和顶级栏目关联起来．(注意action_id  和parent_id 的关系 )
添加一个顶级栏目  pay   action_id为136  parent_id 为0；
      其下子栏目  pay_manage                parent_id 为136；
                      pay_drop                   parent_id 为136；


2.打开languages\zh_cn\admin\priv_action.php 
/* 权限管理的一级分组 */下添加
$_LANG['pay']         = '支付管理';
末尾添加
//支付管理
$_LANG['pay_manage'] = '支付添加/编辑';
$_LANG['pay_drop'] = '支付删除';


3.打开admin\includes\inc_priv.php
末尾添加
        //支付管理
        $purview['02_pay_info']          = array('pay_manage', 'pay_drop');
        $purview['03_pay_charge']        = 'pay_manage';
        $purview['04_pay_record']                  = 'pay_manage';


第四步，添加基本的增删改查功能
建四个文件 pay.php、pay_list、pay_info、pay_search
.添加“增加”功能
/*------------------------------------------------------ */
//-- 添加支付人信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
   // admin_priv('pay_manage');

    /*初始化*/
    $smarty->assign('ur_here',    $_LANG['pay_name_add']);
    //$smarty->assign('action_link', array('text' => $_LANG['pay_name_add'], 'href' => 'pay.php?act=list'));
         $smarty->assign('form', 'insert');
    assign_query_info();
    $smarty->display('pay_info.htm');
}

/*------------------------------------------------------ */
//-- 添加支付人信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    //admin_priv('pay_manage');

    /*检查是否重复*/
    $is_only = $exc->is_only('pay_id', $_POST['pay_id'],0, " pay_id ='$_POST[pay_id]'");

    if (!$is_only)
    {
        sys_msg($_LANG['goods_exist'], 1);
    }

    /*插入数据*/
    if (empty($_POST['pay_id']))
    {
        $_POST['pay_id'] = 0;
    }
    $sql = "INSERT INTO ".$ecs->table('pay')."(pay_id, pay_name,pay_info, pay_bank, pay_credit) ".
            "VALUES ('$_POST[pay_id]','$_POST[pay_name]', '$_POST[pay_info]', '$_POST[pay_bank]', '$_POST[pay_credit]')";
   
    $db->query($sql);

        $link[0]['text'] = $_LANG['back_list'];
    $link[0]['href'] = 'pay.php?act=list';

    $link[1]['text'] = $_LANG['pay_continue_add'];
    $link[1]['href'] = 'pay.php?act=add';

   

    admin_log($_POST['pay_id'],'add','exchange_goods');

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['articleadd_succeed'],0, $link);
}

（首先是根据act传来的参数add,把参数insert赋值到smarty模板，放在在添加页面隐藏域中，更新也是如此）
1.根据传递过来的act的参数insert逻辑添加处理，（参数insert在添加页面的）
2.admin_priv函数判断是否具有权限，
3.$exc->is_only()判断自增号是否唯一（可去掉）
4.$db->query($sql)对数据库进行操作，
5.admin_log()函数记录操作信息
6.clear_cache_files();清除缓存
7.sys_msg()函数提示操作信息


2..添加“修改功能”
/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
   // admin_priv('exchange_goods');

    /* 取数据 */
    $sql = "SELECT * FROM " . $ecs->table('pay') ." WHERE pay_id='$_REQUEST[id]'";
    $pay = $db->GetRow($sql);
    $smarty->assign('pay',   $pay);
    $smarty->assign('ur_here', $_LANG['pay_name_add']);
    $smarty->assign('action_link', array('text' => $_LANG['pay_list'], 'href' => 'pay.php?act=list'));
    $smarty->assign('form', 'update');

    assign_query_info();
    $smarty->display('pay_info.htm');
}

/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */

if ($_REQUEST['act'] =='update')
{
    /* 权限判断 */
   // admin_priv('pay_manage');

    if (empty($_POST['pay_id']))
    {
        $_POST['pay_id'] = 0;
    }

    if ($exc->edit("pay_name='$_POST[pay_name]', pay_info='$_POST[pay_info]', pay_bank='$_POST[pay_bank]', pay_credit='$_POST[pay_credit]'", $_POST['pay_id']))
    {
        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'pay.php?act=list&' ;

        admin_log($_POST['pay_id'], 'edit', 'pay');

        clear_cache_files();
        sys_msg($_LANG['pay_update_success'], 0, $link);
    }
    else
    {
        die($db->error());
    }
}


（首先是根据act传来的参数edit把参数update赋值到smarty模板，放在在添加页面隐藏域中）
1.根据传递过来的act的参数update逻辑添加处理，（参数update在添加页面的）
2.admin_priv函数判断是否具有权限，
3.判断是否提交id值（为空则赋值为0）
4.$$exc->edit()对数据库进行更新操作
5.admin_log()函数记录操作信息
6.clear_cache_files();清除缓存
7.sys_msg()函数提示操作信息


3.添加“删除功能”
/*------------------------------------------------------ */
//-- 删除信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    //check_authz_json('pay_drop');

    $id = intval($_GET['id']);
    if ($exc->drop($id))
    {
        admin_log($id,'remove','article');
        clear_cache_files();
    }

    $url = 'pay.php';

    ecs_header("Location: $url\n");
    exit;
}

act的参数为remove
check_authz_json()函数检查
$exc->drop($id)数据库进行删除操作
记录操作记录
清除缓存文件
ecs_header()跳转


4..添加“批量删除”
/*------------------------------------------------------ */
//-- 批量删除
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch_remove')
{
    admin_priv('pay');

    if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_select_goods'], 1);
    }

    $count = 0;
    foreach ($_POST['checkboxes'] AS $key => $id)
    {
        if ($exc->drop($id))
        {
            admin_log($id,'remove','exchange_goods');
            $count++;
        }
    }

    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'pay.php?act=list');
    sys_msg(sprintf($_LANG['batch_remove_succeed'], $count), 0, $lnk);
}



5.添加“搜索”(暂时调不出页面)


6.添加"AJAX"功能
两种情况（一种点击修改状态，一种是点击修改文本框）
/*------------------------------------------------------ */
//-- 修改上架状态(对错状态)
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'pay_state')
{
    //check_authz_json('goods_manage');
    $pay_id       = intval($_POST['id']);
    $pay_state      = intval($_POST['val']);

    if ($exc->edit("pay_state = '$pay_state'", $pay_id))
    {
        clear_cache_files();
        make_json_result($pay_state);
    }
}


Html页面：
<td align="center"><img src="images/{if $pay.pay_state eq 1}yes{else}no{/if}.gif" onclick="listTable.toggle(this, 'pay_state', {$pay.pay_id})" /></td>




/*------------------------------------------------------ */
//-- 修改支付人姓名信息（点击修改文本框）
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_pay_name')
{
    //check_authz_json('goods_manage');

    $pay_id       = intval($_POST['id']);
    $pay_name = json_str_iconv(trim($_POST['val']));

    if ($exc->edit("pay_name = '$pay_name'", $pay_id))
    {
        clear_cache_files();
        make_json_result($pay_name);
    }
}

Html 页面
<td align="center"><span onclick="listTable.edit(this, 'edit_pay_info', {$pay.pay_id})">{$pay.pay_info}</span></td>

7.排序问题
Php:
/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
   // check_authz_json('exchange_goods');

    $pay_list = get_exchange_goodslist();

    $smarty->assign('pay',    $pay_list['arr']);
    $smarty->assign('filter',        $pay_list['filter']);
    $smarty->assign('record_count',  $pay_list['record_count']);
    $smarty->assign('page_count',    $pay_list['page_count']);

    $sort_flag  = sort_flag($pay_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('pay_list.htm'), '',
   array('filter' => $pay_list['filter'], 'page_count' => $pay_list['page_count']));
}

/* 获得列表 */
function get_exchange_goodslist()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'eg.pay_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'asc' : trim($_REQUEST['sort_order']);


                 /* 记录总数以及页数 */
        if (isset($_POST['brand_name']))
        {
            $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('pay') .' WHERE pay_name = \''.$_POST['brand_name'].'\'';
        }
        else
        {
            $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('pay');
        }

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

                 /* 查询记录 */
        if (isset($_POST['brand_name']))
        {
            if(strtoupper(EC_CHARSET) == 'GBK')
            {
                $keyword = iconv("UTF-8", "gb2312", $_POST['brand_name']);
            }
            else
            {
                $keyword = $_POST['brand_name'];
            }
            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('pay')." WHERE pay_name like '%{$keyword}%' ORDER BY sort_order ASC";
        }
        else
        {
           $sql = 'SELECT eg.* '.
               'FROM ' .$GLOBALS['ecs']->table('pay'). ' AS eg '.
               'WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];
        }
        $filter = page_and_size($filter);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


Html:
   <th><a href="javascript:listTable.sort('pay_name'); ">{$lang.pay_name}</a>{$sort_pay_name}</th>

8.复制功能
Php:
/*------------------------------------------------------ */
//-- 复制
/*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'copy')
        {
            // 商品信息
                         $goods['pay_id'] = 0;
          
            $sql = "SELECT  '0' AS pay_id,pay_name,pay_credit,pay_info,pay_bank" .
                    " FROM " . $ecs->table('pay') .
                    " WHERE pay_id = '$_REQUEST[pay_id]' ";
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $db->autoExecute($ecs->table('pay'), $row, 'INSERT');
            }
                }

Html页面：

<a href="pay.php?act=copy&pay_id={$pay.pay_id}" title="{$lang.copy}"><img src="images/icon_copy.gif" width="16" height="16" border="0" /></a>

因为自己研究得不是很透彻，肯定还有很多有待改善的地方，但是基本开发思路已经出来了，大家可以按照步骤一步一步研究