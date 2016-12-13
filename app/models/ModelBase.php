<?php 
namespace Models;
use Phalcon\Mvc\Model;

/**
* 模型基类
*/
class ModelBase extends Model
{
    const CACHETIME_DAY_7     = 604800;
    const CACHETIME_DAY_3     = 259200;
    const CACHETIME_DAY_1     = 86400;
    const CACHETIME_HOUR_1    = 3600;
    const CACHETIME_MINUTE_10 = 600;

    const STATUS_ON  = 1;   //有效
    const STATUS_OFF = 0;   //无效

    const IS_OBJ = 1;       //查询结果是对象
    const IS_ARR = 0;       //查询结果是数组

    /**
     * [cutRowsColumnToString 把返回的字段裁剪成字符串]
     * @Author   Jason
     * @DateTime 2015-11-25T10:40:44+0800
     * @param    array                    $rows   [description]
     * @param    string                   $column [description]
     * @param    string                   $plode  [description]
     * @return   [type]                           [description]
     */
    public static function cutRowsColumnToString( $rows = array(), $column = '', $plode = ',' )
    {
        if ( !empty( $rows ) ) {
            $arr = array();
            foreach ($rows as $key => $value) {
                if ( isset( $value[$column] ) ) {
                    $arr[] = $value[$column];
                }
            }

            return implode($plode, $arr);
        }

        return null;
    }

    /**
     * [get_random_elements 从数组里获取随机的数组]
     * @Author   Jason
     * @DateTime 2015-11-25T11:17:28+0800
     * @param    array                    $array [description]
     * @param    integer                  $limit [description]
     * @return   [type]                          [description]
     */
    public static function getRandomElements( $array = array() ,$limit = 0 ) {

        shuffle($array);

        if ( $limit > 0 ) {
            $array = array_splice($array, 0, $limit);
        }
        return $array;
    }

	/**
     * [findFirst 重写findFirst]
     * @Author   Jason
     * @DateTime 2016-05-27T10:24:10+0800
     * @param    array                    $param [description]
     * @return   [type]                          [description]
     */
    public static function findFirstToArray( $param = array() )
    {
        if ( !isset( $param['cache'] ) ) {
        	$data = parent::findFirst( $param );
            return $data ? $data->toArray() : array();  
        }

        //获取cache
        $cacheKey = $param['cache']['key'];
        $lifetime = isset( $param['cache']['lifetime'] ) ? $param['cache']['lifetime'] : 0;
        unset( $param['cache'] );

        $memcached = self::getModelsCache();
        if ( $memcached->exists( $cacheKey ) ) {
            return $memcached->get( $cacheKey );
        }

        $data = parent::findFirst( $param );
        $data = $data ? $data->toArray() : array();

        if ( $lifetime ) {
            $memcached->save( $cacheKey, $data, $lifetime );    
        } else {
            $memcached->save( $cacheKey, $data);
        }
        
        return $data;
    }

    /**
     * [find 重写find]
     * @Author   Jason
     * @DateTime 2016-05-27T10:24:23+0800
     * @param    array                    $param [description]
     * @return   [type]                          [description]
     */
    public static function findToArray( $param = array() )
    {
        if ( !isset( $param['cache'] ) ) {
            return parent::find( $param )->toArray();  
        }

        //获取cache
        $cacheKey = $param['cache']['key'];
        $lifetime = isset( $param['cache']['lifetime'] ) ? $param['cache']['lifetime'] : 0;
        unset( $param['cache'] );

        $memcached = self::getModelsCache();
        if ( $memcached->exists( $cacheKey ) ) {
            return $memcached->get( $cacheKey );
        }

        $data = parent::find( $param );
        $data = $data ? $data->toArray() : array();

        if ( $lifetime ) {
            $memcached->save( $cacheKey, $data, $lifetime );    
        } else {
            $memcached->save( $cacheKey, $data);
        }
        
        return $data;
    }

    /**
     * [getModelsCache 获取模型cache对象]
     * @Author   Jason
     * @DateTime 2016-05-26T15:44:01+0800
     * @return   [type]                   [description]
     */
    public static function getModelsCache()
    {
        return self::getDefaultDI()->getModelsCache();
    }

    /**
     * [getDefaultDI 获取DI]
     * @Author   Jason
     * @DateTime 2016-07-29T10:06:32+0800
     * @return   [type]                   [description]
     */
    public static function getDefaultDI()
    {
        return \Phalcon\DI::getDefault();
    }

    /**
     * [avg description]
     * @Author   Jason
     * @DateTime 2016-09-26T10:24:24+0800
     * @param    array                    $param  [description]
     * @param    string                   $column [description]
     * @return   [type]                           [description]
     */
    protected static function avg( $param = array(), $column = '' )
    {
        $param['columns'] = ' AVG( ' . $column . ' ) as avg ';
        $data = self::findFirstToArray( $param );
        return $data['avg'];
    }

    /**
     * [genRet 接口错误码返回]
     * @Author   Jason
     * @DateTime 2016-07-29T12:03:56+0800
     * @param    [type]                   $errcode [description]
     * @param    string                   $errmsg  [description]
     * @return   [type]                            [description]
     */
    protected static function genRet($errcode, $errmsg = '', $datas = null)
    {
        return array(
            "errcode" => $errcode,
            "errmsg" => $errmsg,
            "data" => $datas
        );
    }

    /**
     * [delCacheKeys 批量删除缓存]
     * @Author   Jason
     * @DateTime 2016-12-13T11:49:43+0800
     * @param    array                    $keys [description]
     * @return   [type]                         [description]
     */
    protected static function delCacheKeys( $keys = array() )
    {
        $memcached = self::getModelsCache();
        foreach ($keys as $key) {
            if ( is_string( $key ) ) {
                $memcached->delete( $key );      
            }
        }
    }

    public function beforeCreate()
    {
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

    public function getMessages($filter = NULL)
    {
        $messages = '';
        foreach (parent::getMessages() as $message) {
            $messages = $messages . ' ' . $message->getMessage();
        }

        return $messages;
    }

    /**
     * [saveData 保存数据]
     * @Author   Jason
     * @DateTime 2015-11-11T16:40:25+0800
     * @return   [type]                   [description]
     */
    public function saveData()
    {
        $ret = $this->save();
        if ( $ret ) {
            $this->deleteCache();
        } else {
            if ( $this->getDI()->getLogger() ) {
                $this->getDI()->getLogger()->error('model save error ' . $this->getMessages() );    
            }
        }
        return $ret;
    }

}