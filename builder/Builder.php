<?php
declare (strict_types=1);

use Exceptions\InvalidInstanceException;

class Builder
{


    /**
     * 静态魔术加载方法
     * @param string $name 静态类名
     * @param array $arguments 参数集合
     * @return mixed
     * @throws InvalidInstanceException
     */
    public static function __callStatic($name, $arguments)
    {
        $class = 'Resolver\\' . $name;

        if (!empty($class) && class_exists($class)) {
            return new $class();
        }
        throw new InvalidInstanceException("class {$name} not found");
    }

}