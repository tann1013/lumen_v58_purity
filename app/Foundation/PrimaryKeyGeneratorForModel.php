<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-04-23
 * @version 1.0
 */
namespace App\Foundation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class PrimaryKeyGeneratorForModel
{
    const PRIMARY_KEY_PREFIX = 'DB:PrimaryKey';

    /**
     * 为模型生成主键，参数尽量传模型实例
     *
     * @param Model|string $model
     * @return integer
     * @author Chuoke
     */
    public static function generate($model)
    {
        [$val_prefix, $expire] = array_values(self::valPrefixAndExpire());
        $key = self::keyOfModel($model, $val_prefix);

        $val = PrimaryKeyGenerator::generate($key);
        if ($val < 2 && $expire > 0) {
            PrimaryKeyGenerator::expire($key, $expire);
        }

        return intval($val_prefix . sprintf('%05d', $val));
    }

    /**
     * 为模型构建 key
     *
     * @param Model|string $model
     * @param null|int $val_prefix
     * @return string
     * @author Chuoke
     */
    public static function keyOfModel($model, $val_prefix = null)
    {
        if (is_string($model)) {
            $modelName = ucfirst($model);

            $modelClass = stripos($modelName, '\\') === false
                ? '\\App\\Models\\' . $modelName
                : '\\' . ltrim($modelName, '\\');

            if (class_exists($modelClass)) {
                $model = app($modelClass);
            } else {
                throw new \Exception('模型' . $modelClass . ' 不存在');
            }
        } elseif (!$model instanceof Model) {
            throw new \Exception('参数 $model 必须是模型类名或实例');
        }

        return implode(
            ':',
            array_diff([self::PRIMARY_KEY_PREFIX, $model->getTable(), $val_prefix], ['', null])
        );
    }

    /**
     * 值前缀和键有效期(秒)
     *
     * @return array [2020033112, 3600]
     * @author Chuoke
     */
    public static function valPrefixAndExpire()
    {
        return [
            'prefix' => date('YmdH'),
            'expire' => 3600
        ];
    }

    /**
     * 从 cache 中生成主键
     *
     * @param string $key
     * @param int|null $prefix_value 主键前缀值，默认 date('YmdH', time())
     * @return int
     * @author Chuoke
     */
    public static function generateFromCache($key, $prefix_value = null)
    {
        if ($prefix_value) {
            $new_prefix_val = abs(intval($prefix_value));
        } else {
            $new_prefix_val = date('YmdH', time());
        }

        $prefix_value_key = 'BaseLogic:generatePkIdV2:baseNumKey';
        $increase_num_key = 'BaseLogic:generatePkIdV2:increaseNumKey:' . $key;

        $old_prefix_value = Cache::get($prefix_value_key);
        if (!$old_prefix_value || $old_prefix_value != $new_prefix_val) {
            Cache::forget($prefix_value_key);
            Cache::forget($increase_num_key);

            Cache::put($prefix_value_key, $new_prefix_val);
        }

        $prefix = Cache::get($prefix_value_key);
        $increase_num = Cache::increment($increase_num_key);

        return intval($prefix . sprintf("%05d", $increase_num));
    }
}