<?php
/**
 * Model基类
 * 所有MODEL均要继承此类
 * 使用Eloquent ORM模型
 * todo 严禁乱写方法到本类
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class BaseModel
 * @method static find(int $id, array $columns = ['*'])
 * @method static bool insert(array $values)
 * @method static int insertGetId(array $values, $sequence = null)
 * @package App\Models
 */
class BaseModel extends Model
{
    // 数据库连接
    protected $connection = 'mysql_master';

    // 表名
    protected $table = '';

    // 是否自动维护时间戳
    public $timestamps = false;
    /**
     * 操作主库标志
     * @var bool
     */
    public $writeConnectFlag = false;
    /**
     * 强制索引时,保留最初的表明
     * @var string
     */
    public $bakTable = '';

    //整型过滤字段名称
    public $intFilterColumns = [];

    /**
     * 这是主库操作
     * @return $this
     */
    public function setWriteConnect()
    {
        $this->writeConnectFlag = true;
        return $this;
    }

    /**
     * 重置连接
     * @return $this
     */
    protected function ResetConnect()
    {
        if ($this->writeConnectFlag) {
            $this->writeConnectFlag = false;
        }
        return $this;
    }

    /**
     * @param array|string $columns 字段必须有值,可以array,如['id','source'=>'new','name','shopSource'=>'shopNewName']; 可以字符串'id,userinfoId as aa,name,shopName as shop',也可以其他
     *                              PS:$columns禁止null和'*'
     * @param null         $where   $where = array('id >'=>34,'userinfoId'=>'55','uu'=>array('12','33'),'tt like'=>'%pp%','iio between'=>array('112','4445'),'expression'=>'原生语句xxxoooxo'));
     * @param null         $order   可以字符如(id desc,userinfoId asc)可以数组,数组例:['id'=>'desc','userinfoId'=>'asc'];
     * @param null         $group   数组 ['id','userinfoId']
     * @return \stdClass 单个对象
     */
    public function getOne($columns = NULL, $where = NULL, $order = NULL, $group = NULL)
    {
        /**
         * @var \Illuminate\Database\Query\Builder $selectBuilder
         */
        $selectBuilder = DB::connection($this->connection)->table($this->table);

        $this->_checkColumns($columns, $selectBuilder);
        $this->_checkWhere($where, $selectBuilder);
        $this->_checkOrder($order, $selectBuilder);

        if (isset($group)) {
            $selectBuilder->groupBy($group);
        }
        //连接主库
        if ($this->writeConnectFlag) {
            $selectBuilder->useWritePdo();
        }

        $result = $selectBuilder->first();

        //使用强制索引之后,查询完重置
        if (!empty($this->bakTable)) {
            $this->setTable($this->bakTable);
        }

        //重置主从连接
        $this->ResetConnect();

        //PDO强类型转换
        if ($this->connection == 'mysql_master' && !empty($this->intFilterColumns)) {
            foreach ($this->intFilterColumns as $intColumn) {
                isset($result->$intColumn) && is_numeric($result->$intColumn) && $result->$intColumn = (int)$result->$intColumn;
            }
        }

        return $result;
    }


    /**
     * @param array|string $columns 字段必须有值,可以array,如['id','source'=>'new','name','shopSource'=>'shopNewName']; 可以字符串'id,userinfoId as aa,name,shopName as shop',也可以其他
     *                              PS:$columns禁止null和'*'
     * @param null         $where   $where = array('id >'=>34,'userinfoId'=>'55','uu'=>array('12','33'),'tt like'=>'%pp%','iio between'=>array('112','4445'),'expression'=>'原生语句xxxoooxo'));
     * @param null         $order   可以字符如(id desc,userinfoId asc)可以数组,数组例:['id'=>'desc','userinfoId'=>'asc'];
     * @param null         $group   数组 ['id','userinfoId']
     * @param int          $limit   条数
     * @param int          $offset  从第几条取
     * @return array [\stdClass] 多条对象
     */
    public function getList($columns = NULL, $where = NULL, $order = NULL, $group = NULL, $limit = NULL, $offset = NULL)
    {
        /**
         * @var \Illuminate\Database\Query\Builder $selectBuilder
         */
        $selectBuilder = DB::connection($this->connection)->table($this->table);

        $this->_checkColumns($columns, $selectBuilder);
        $this->_checkWhere($where, $selectBuilder);
        $this->_checkOrder($order, $selectBuilder);

        if (isset($group)) {
            $selectBuilder->groupBy($group);
        }

        if (isset($limit)) {
            $selectBuilder->take($limit);
        }

        if (isset($offset)) {
            $selectBuilder->skip($offset);
        }

        //连接主库
        if ($this->writeConnectFlag) {
            $selectBuilder->useWritePdo();
        }
        $list = $selectBuilder->get();
        //使用强制索引之后,查询完重置
        if (!empty($this->bakTable)) {
            $this->setTable($this->bakTable);
        }
        //重置主从连接
        $this->ResetConnect();

        if ($list) {
            $data = $list->toArray();

            //PDO强类型转换
            if ($this->connection == 'mysql_master' && !empty($this->intFilterColumns)) {
                foreach ($data as &$v) {
                    foreach ($this->intFilterColumns as $intColumn) {
                        isset($v->$intColumn) && is_numeric($v->$intColumn) && $v->$intColumn = (int)$v->$intColumn;
                    }
                }
            }

            return $data;
        }
        return [];
    }

    /**
     * @param array        $join       关联 如['sale_extend','sale_extend.saleId','sale.id','left']或者
     *                                 多条
     *                                 [
     *                                 ['sale_extend','sale_extend.saleId','sale.id','left'],
     *                                 ['cc','cc.saleId','sale.sale.id','left'],
     *                                 ];
     * @param array|string $columns    字段必须有值,可以array,如['id','source'=>'new','name','shopSource'=>'shopNewName']; 可以字符串'id,userinfoId as aa,name,shopName as shop',也可以其他
     *                                 PS:$columns禁止null和'*'
     * @param array|null   $where      $where = array('id >'=>34,'userinfoId'=>'55','uu'=>array('12','33'),'tt like'=>'%pp%','iio between'=>array('112','4445'),'expression'=>'原生语句xxxoooxo'));
     * @param array|string $order      可以字符如(id desc,userinfoId asc)可以数组,数组例:['id'=>'desc','userinfoId'=>'asc'];
     * @param array        $group      数组 ['id','userinfoId']
     * @param int          $limit      条数
     * @param int          $offset     从第几条取
     * @param string       $forceIndex 强制索引
     * @return array [\stdClass] 多条对象
     */
    public function getJoinList($join = NULL, $columns = NULL, $where = NULL, $order = NULL, $group = NULL, $limit = NULL, $offset = NULL, $forceIndex = NULL)
    {
        if (!empty($forceIndex)) {
            $this->tableForceIndex($forceIndex);
        }
        /**
         * @var \Illuminate\Database\Query\Builder $selectBuilder
         */
        $selectBuilder = DB::connection($this->connection)->table($this->table);

        $this->_checkJoin($join, $selectBuilder);
        $this->_checkColumns($columns, $selectBuilder);
        $this->_checkWhere($where, $selectBuilder);
        $this->_checkOrder($order, $selectBuilder);

        if (isset($group)) {
            $selectBuilder->groupBy($group);
        }

        if (isset($limit)) {
            $selectBuilder->take($limit);
        }

        if (isset($offset)) {
            $selectBuilder->skip($offset);
        }

        //连接主库
        if ($this->writeConnectFlag) {
            $selectBuilder->useWritePdo();
        }

        $list = $selectBuilder->get();
        //使用强制索引之后,查询完重置
        if (!empty($this->bakTable)) {
            $this->setTable($this->bakTable);
        }
        //重置主从连接
        $this->ResetConnect();

        if ($list) {
            return $list->toArray();
        }
        return [];
    }


    /**
     * @param array        $join    关联 如['sale_extend','sale_extend.saleId','sale.id','left']或者
     *                              多条
     *                              [
     *                              ['sale_extend','sale_extend.saleId','sale.id','left'],
     *                              ['cc','cc.saleId','sale.sale.id','left'],
     *                              ];
     * @param array|string $columns 字段必须有值,可以array,如['id','source'=>'new','name','shopSource'=>'shopNewName']; 可以字符串'id,userinfoId as aa,name,shopName as shop',也可以其他
     *                              PS:$columns禁止null和'*'
     * @param array        $where   $where = array('id >'=>34,'userinfoId'=>'55','uu'=>array('12','33'),'tt like'=>'%pp%','iio between'=>array('112','4445'),'expression'=>'原生语句xxxoooxo'));
     * @param array|string $order   可以字符如(id desc,userinfoId asc)可以数组,数组例:['id'=>'desc','userinfoId'=>'asc'];
     * @param array        $group   数组 ['id','userinfoId']
     * @param int          $limit   条数
     * @param int          $offset  从第几条取
     * @return \stdClass|array|null 多条对象
     */
    public function getJoinOne($join = NULL, $columns = NULL, $where = NULL, $order = NULL, $group = NULL, $limit = NULL, $offset = NULL)
    {
        /**
         * @var \Illuminate\Database\Query\Builder $selectBuilder
         */
        $selectBuilder = DB::connection($this->connection)->table($this->table);

        $this->_checkJoin($join, $selectBuilder);
        $this->_checkColumns($columns, $selectBuilder);
        $this->_checkWhere($where, $selectBuilder);
        $this->_checkOrder($order, $selectBuilder);

        if (isset($group)) {
            $selectBuilder->groupBy($group);
        }

        if (isset($limit)) {
            $selectBuilder->take($limit);
        }

        if (isset($offset)) {
            $selectBuilder->skip($offset);
        }

        //连接主库
        if ($this->writeConnectFlag) {
            $selectBuilder->useWritePdo();
        }

        $result = $selectBuilder->first();
        //使用强制索引之后,查询完重置
        if (!empty($this->bakTable)) {
            $this->setTable($this->bakTable);
        }
        //重置主从连接
        $this->ResetConnect();

        return $result;
    }

    /**
     * 插入数据,返回主键ID
     * @param $data
     * @return mixed
     */
    public function insertData($data)
    {
        if (!is_array($data)) {
            if (is_object($data)) {
                $data = json_decode(json_encode($data, JSON_UNESCAPED_UNICODE), true);
            } else {
                abort(404, __METHOD__ . '数据必须为数组或者对象');
            }
        }

        return DB::connection($this->connection)->table($this->table)->insertGetId($data);
    }

    /**
     * 插入多条数据
     * @param  $data array
     * @return bool or int
     */
    public function insertMore($data)
    {
        if (!is_array($data)) {
            if (is_object($data)) {
                $data = json_decode(json_encode($data, JSON_UNESCAPED_UNICODE), true);
            } else {
                abort(404, __METHOD__ . '数据必须为数组或者对象');
            }
        }

        return DB::connection($this->connection)->table($this->table)->insert($data);
    }

    /**
     * 修改数据,返回修改条数
     * @param  array $data array
     * @param  array $where
     * @return int modify num
     */
    public function updateData($data, $where)
    {
        if (!is_array($data) || empty($where)) {
            return false;
        }
        /**
         * @var \Illuminate\Database\Query\Builder $updateBuilder
         */
        $updateBuilder = DB::connection($this->connection)->table($this->table);
        $this->_checkWhere($where, $updateBuilder);
        return $updateBuilder->update($data);
    }

    /**
     * 删除数据
     * @param array|string $where array
     * @return int modify num
     */
    public function deleteData($where)
    {
        if (empty($where)) {
            return false;
        }
        /**
         * @var \Illuminate\Database\Query\Builder $updateBuilder
         */
        $updateBuilder = DB::connection($this->connection)->table($this->table);
        $this->_checkWhere($where, $updateBuilder);
        return $updateBuilder->delete();
    }

    /**
     * 返回数据条数
     * @param  array|string $where
     * @return int num
     */
    public function count($where)
    {
        /**
         * @var \Illuminate\Database\Query\Builder $countBuilder
         */
        $countBuilder = DB::connection($this->connection)->table($this->table);
        $this->_checkWhere($where, $countBuilder);

        //连接主库
        if ($this->writeConnectFlag) {
            $countBuilder->useWritePdo();
        }

        $result = $countBuilder->count();
        //使用强制索引之后,查询完重置
        if (!empty($this->bakTable)) {
            $this->setTable($this->bakTable);
        }
        //重置主从连接
        $this->ResetConnect();

        return $result;
    }

    /**
     * 获取mysql的枚举类型
     * @param string $tableName
     * @param string $filedName
     * @param array  $exclude
     * @return array
     */
    public function getEnums($tableName, $filedName, $exclude = [])
    {
        $sql = "SHOW COLUMNS FROM {$tableName} LIKE '{$filedName}'";
        $result = DB::connection($this->connection)->select($sql);
        if ($result) {
            $enum = $result[0]->Type;
            $enum_arr = explode("(", $enum);
            $enum = $enum_arr[1];
            $enum_arr = explode(")", $enum);
            $enum = $enum_arr[0];
            $enum_arr = explode(",", $enum);
            for ($i = 0; $i < count($enum_arr); $i++) {
                $enum_arr[$i] = str_replace("'", "", $enum_arr[$i]);
            }

            if (count($exclude) > 0) {
                $enum_arr = array_diff($enum_arr, $exclude);
            }
            return $enum_arr;
        }
        return [];
    }

    /**
     * 检查字段
     * @param                                    $columns
     * @param \Illuminate\Database\Query\Builder $selectBuilder
     * @return string
     */
    private function _checkColumns($columns, &$selectBuilder)
    {
        $filed = '';
        if (!isset($columns)) {
            abort(404, __METHOD__ . '字段取值禁止用*或者null');
        } else {
            if (is_array($columns)) {
                foreach ($columns as $k => $v) {
                    if ($v == '*') {
                        abort(404, __METHOD__ . '字段取值禁止用*或者null');
                        break;
                    }
                    if (is_numeric($k)) {
                        $filed .= $v . ',';
                    } else {
                        $filed .= $k . ' as ' . $v . ',';
                    }
                }
                return $selectBuilder->selectRaw(substr($filed, 0, strlen($filed) - 1));
            } elseif (is_string($columns)) {
                if ($columns == '*') {
                    abort(404, __METHOD__ . '字段取值禁止用*或者null');
                }
                return $selectBuilder->selectRaw(DB::raw($columns));
            } else {
                return $selectBuilder->select($columns);
            }
        }
    }

    /**
     * @param array|string                       $order
     * @param \Illuminate\Database\Query\Builder $selectBuilder
     * @return mixed
     */
    private function _checkOrder($order, &$selectBuilder)
    {
        if (!isset($order)) {
            return $selectBuilder;
        }

        if (is_array($order)) {
            $orderStr = '';
            foreach ($order as $key => $v) {
                $v = strtolower($v);
                if (!in_array($v, ['asc', 'desc'])) {
                    $v = 'asc';
                }
                $orderStr .= $key . ' ' . $v . ',';
            }
            return $selectBuilder->orderByRaw(substr($orderStr, 0, strlen($orderStr) - 1));
        } else {
            return $selectBuilder->orderByRaw($order);
        }

    }

    /**
     * @param array|string                       $where
     * @param \Illuminate\Database\Query\Builder $selectBuilder
     * @return mixed
     */
    private function _checkWhere($where, &$selectBuilder)
    {
        if (!isset($where)) {
            return $selectBuilder;
        }

        if (is_string($where)) {
            $this->_checkWhereRawSql($where);
            return $selectBuilder->whereRaw($where);
        } elseif (is_array($where)) {
            foreach ($where as $k => $v) {
                if (!is_numeric($k)) {

                    $k = trim($k);
                    $tip = trim(strtolower($k));

                    if (strpos($tip, 'notin')) {
                        $selectBuilder->whereNotIn(trim(str_replace('notin', '', $k)), $v);
                    } elseif ($tip === 'expression') {
                        if (!isset($v['sql'])) {
                            abort(404, __METHOD__ . '表达式必须含有sql字段,如果有参数请用字段bind');
                        }
                        $this->_checkWhereRawSql($v['sql']);
                        $selectBuilder->whereRaw($v['sql'], $v['bind'] ?? []);
                    } else {
                        if (is_array($v)) {
                            $selectBuilder->whereIn($k, $v);
                        } else {
                            //新增为null,一定要恒等于
                            if ($v === null) {
                                $this->_checkWhereRawSql($k);
                                $selectBuilder->whereRaw($k);
                            } else {
                                $tmpArr = explode(' ', trim($k));
                                if (empty($tmpArr[1])) {
                                    $tmpArr[1] = '=';
                                }
                                $selectBuilder->where($tmpArr[0], $tmpArr[1], $v);
                            }
                        }
                    }

                }
            }

            return $selectBuilder;
        } else {
            return $selectBuilder->where($where);
        }
    }

    /**
     * 检查连接查询
     * @param array                              $join
     * @param \Illuminate\Database\Query\Builder $selectBuilder
     * @return mixed
     */
    private function _checkJoin($join, &$selectBuilder)
    {
        if (!isset($join)) {
            return $selectBuilder;
        }

        if (!is_array($join[0])) {
            $join = [$join];
        }

        foreach ($join as $val) {

            if ($val[3] == 'left') {
                $method = 'leftJoin';
            } elseif ($val[3] == 'left') {
                $method = 'rightJoin';
            } elseif ($val[3] == 'cross') {
                $method = 'crossJoin';
            } else {
                $method = 'join';
            }
            $selectBuilder->$method($val[0], $val[1], '=', $val[2]);
        }
        return $selectBuilder;
    }

    /**
     * 检查原始 where 语句
     * @param string $whereRawSql
     * @return void
     */
    private function _checkWhereRawSql($whereRawSql)
    {
        $whereRawSql = trim((string)$whereRawSql);
        if (
            mb_strlen($whereRawSql) < 3 || //太短
            mb_strlen($whereRawSql) > 2000 || //太长
            preg_match('/(^|AND|OR)\s*\d+=/i', $whereRawSql) || //字段名数字开头 1=1
            !preg_match('/[,=!\^\|\~\<\>\'\"\(]|like|between/i', $whereRawSql) //不包含常见的符号
        ) {
            Log::error('sql有问题:' . json_encode($whereRawSql));
            abort(404, '语句检查失败');
        }
    }

    //强制索引
    public function tableForceIndex($index)
    {
        $this->bakTable = $this->table;
        $this->setTable(DB::raw("`" . $this->table . "` force Index ($index)"));
        return $this;
    }

    /**
     * 开始事务
     * @throws \Exception
     */
    public function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
    }

    /**
     * 提交事务
     * @return void
     */
    public function commit()
    {
        $this->getConnection()->commit();
    }

    /**
     * 回滚事务
     * @return void
     */
    public function rollBack()
    {
        $this->getConnection()->rollBack();
    }

    /**
     * 批量更新，默认以id为条件更新，如果没有ID则以第一个字段为条件
     * @param array $multipleData
     * @return bool
     */
    public function updateBatch($multipleData = [])
    {
        try {
            if (empty($multipleData)) {
                return false;
            }
            // 取出字段名
            $firstRow = current($multipleData);
            $updateColumn = array_keys($firstRow);
            // 默认以id为条件更新，如果没有ID则以第一个字段为条件
            $referenceColumn = isset($firstRow['id']) ? 'id' : current($updateColumn);
            unset($updateColumn[0]);
            // 拼接sql语句
            $updateSql = "UPDATE " . $this->table . " SET ";
            $sets = [];
            $bindings = [];
            foreach ($updateColumn as $uColumn) {
                $setSql = "`" . $uColumn . "` = CASE ";
                foreach ($multipleData as $data) {
                    $setSql .= "WHEN `" . $referenceColumn . "` = ? THEN ? ";
                    $bindings[] = $data[$referenceColumn];
                    $bindings[] = $data[$uColumn];
                }
                $setSql .= "ELSE `" . $uColumn . "` END ";
                $sets[] = $setSql;
            }
            $updateSql .= implode(', ', $sets);
            $whereIn = collect($multipleData)->pluck($referenceColumn)->values()->all();
            $bindings = array_merge($bindings, $whereIn);
            $whereIn = rtrim(str_repeat('?,', count($whereIn)), ',');
            $updateSql = rtrim($updateSql, ", ") . " WHERE `" . $referenceColumn . "` IN (" . $whereIn . ")";
            // 传入预处理sql语句和对应绑定数据
            return DB::update($updateSql, $bindings);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 单条插入
     * @param $data
     * @return int
     */
    public function addOne($data)
    {
        return $this->insertGetId($data);
    }

    /**
     * 单条更新
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateOne($data, $where)
    {
        return $this->where($where)->update($data);
    }

}