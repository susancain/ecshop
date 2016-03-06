ecshop的数据库getRow、getAll、getOne区别
ECShop没有使用一些开源的数据库操作类，比如adodb或者PEAR，而是封装了自己的实现。这样做的好处是实现非常轻量，大大减小了分发包的文件大小。另外，当网站需要做memcached缓存时，也可以很方便的实现。当然，这样做的后果就是数据库的选择非常狭窄，无法实现其它的非MySQL数据库。

ECShop的数据操作类文件是includes/cls_mysql.php，类名是cls_mysql。

该类主要提供了下面 一些比较有用的方法：

getAll($sql)和getAllCached($sql, $cached = 'FILEFIRST')：获取所有记录。
getRow($sql, $limited = false)和getRowCached($sql, $cached = 'FILEFIRST')：获取单行记录。
getCol($sqlse)和getColCached($sql, $cached = 'FILEFIRST')：获取某栏位的所有值。
getOne($sql, $limited = false)和getOneCached($sql, $cached = 'FILEFIRST')：获取单个数值。
query($sql)：执行数据库查询。
autoExecute($table, $field_values, $mode = 'INSERT', $where = '')：数据库表操作。


现在我们以实例的方式来说明这些方法如何使用。首先，在ecshop/admin目录下新增文件test_mysql.php，文件内容如下：

复制代码
define('IN_ECS', true); 
define('EC_CHARSET', 'utf-8');
define('ROOT_PATH', 'D:/Program Files/Zend/Apache2/htdocs/ecshop/');
define('DATA_DIR', 'data');

$db_host = "localhost:3306"; 
$db_name = "ecshop"; 
$db_user = "root"; 
$db_pass = ""; 

require('../includes/cls_mysql.php'); 
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
复制代码
获取所有记录
getAll方法用来从数据库中获取满足条件的所有记录。getAllCached是它的缓存版本，cache key是该方法的第二个参数，如果缓存有效，直接返回缓存结果，否则重新执行数据库查询。 

将下面的代码加到test_mysql.php的最后：

复制代码
test_getAll();

function test_getAll()
{
    global $db;
    
    $sql = "SELECT user_id, user_name, email FROM ecs_admin_user";
    $result = $db->getAll($sql);
    print_r($result);
}
复制代码
获取单行记录

getRow方法用来从数据库中获取满足条件的单行记录，或者说是第一条记录。getRowCached是它的缓存版本，cache key是该方法的第二个参数，如果缓存有效，直接返回缓存结果，否则重新执行数据库查询。 

将下面的代码加到test_mysql.php的最后：

复制代码
test_getRow();

function test_getRow()
{
    global $db;
    
    $sql = "SELECT user_id, user_name, email FROM ecs_admin_user LIMIT 1";
    $result = $db->getRow($sql);
    print_r($result);
}
复制代码
获取某栏位的所有值

getCol方法用来从数据库中获取满足条件的某个栏位的所有值。getColCached是它的缓存版本，cache key是该方法的第二个参数，如果缓存有效，直接返回缓存结果，否则重新执行数据库查询。 

将下面的代码加到test_mysql.php的最后：

复制代码
test_getCol();

function test_getCol()
{
    global $db;
    
    $sql = "SELECT email FROM ecs_admin_user";
    $result = $db->getCol($sql);
    print_r($result);
}
复制代码
获取单个值
getOne方法用来从数据库中获取满足条件的单个值。getOneCached是它的缓存版本，cache key是该方法的第二个参数，如果缓存有效，直接返回缓存结果，否则重新执行数据库查询。

 将下面的代码加到test_mysql.php的最后：

复制代码
test_getOne();

function test_getOne()
{
    global $db;
    
    $sql = "SELECT email FROM ecs_admin_user WHERE user_id = 4";
    $result = $db->getOne($sql);
    print_r($result);
}
复制代码
执行数据库查询

query方法用来执行数据库查询，例如INSERT，UPDATE，DELETE等。

将下面的代码加到test_mysql.php的最后：

 

复制代码
test_query();

function test_query()
{
    global $db;
    
    $sql = "UPDATE ecs_admin_user SET todolist = '你有一封新邮件!' WHERE user_id = 4";
    $db->query($sql);
    $sql = "SELECT todolist FROM ecs_admin_user WHERE user_id = 4";
    $result = $db->getOne($sql);
    print_r($result);
}
复制代码
数据库表操作

autoExecute方法用来简化对数据表的INSERT和UPDATE。

将下面的代码加到test_mysql.php的最后：

复制代码
test_autoExecute();

function test_autoExecute()
{
    global $db;
    
    $table = "ecs_role";
    $field_values = array("role_name" => "总经理办", "role_describe" => "总经理办", "action_list" => "all");
    $db->autoExecute($table, $field_values, "INSERT");
    // 执行的SQL：INSERT INTO ecs_role (role_name, action_list, role_describe) VALUES ('总经理办', 'all', '总经理办')

    $role_id = $db->insert_id(); // 新记录的ID：5
    
    $field_values = array("action_list" => "goods_manage");
    $db->autoExecute($table, $field_values, "UPDATE", "role_id = $role_id");
    // 执行的SQL：UPDATE ecs_role SET action_list = 'goods_manage' WHERE role_id = 5

    $sql = "SELECT action_list FROM ecs_role WHERE role_id = $role_id";
    $result = $db->getOne($sql);
    print_r($result);
}
复制代码
$db->getAll($sql) 返回查询数据表中所有结果，形式是一个二维关联数组。如果把结果赋值给smarty非常方便通过循环在模板里面引用。

$db->getOne($sql) 返回查询的第一个字段

比如：
$sql ="select count(*) from ecs_goods ";

$count = $db->getOne($sql);
$count 为 商品数据总个数

$db->getRow($sql) 则返回数据库中一行数据 比如

$sql = "select * from ecs_goods ";

$row = $db->getRow($sql);
则$row 为一个一维的关联数组 可以通过$row['goods_name'] 取得商品名称 等等。。。

其实这里的结果

$row_all = $db->getAll($sql) ；
$row = $db->getRow($sql);

$row 其实等于 $row_all[0] 当然你可以通过循环 取得 其他的值 比如 $row_all[1] ...
 $db->getOne 一行一个字段
 $db->getRow 一行记录
 $db->getAll 全部记录