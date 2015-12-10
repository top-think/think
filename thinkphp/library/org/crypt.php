<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Haotong Lin <lofanmi@gmail.com>
// +----------------------------------------------------------------------

namespace org;

use org\transform\driver\Base64;

/**
 * ThinkPHP加密模块(AES加密)
 *
 *     基于Laravel 5.1 Illuminate\Encryption\Encrypter实现, 使用时需打开openssl扩展
 *
 *     注: 关于key的生成
 *
 *         当 CIPHER_MODE = AES-128-CBC, IV_SIZE = 16 时,
 *
 *         * key可以由16位随机字符串组成, 如:
 *             $key = 'gm9?lh=ngV.w86!Q';
 *
 *         * 也可以使用32位十六进制字符串pack而成, 如:
 *             $key = pack('H*', 'd40e1a08a07fb2e21abe2abc5910f533');
 *
 *         当使用AES-256-CBC时, 密钥长度和初始化向量大小为32!
 *
 *     测试一下~
 *        // 明文
 *        $data = 'ThinkPHP框架 | 中文最佳实践PHP开源框架,专注WEB应用快速开发8年！';
 *        // 密钥
 *        $key = 'gm9?lh=ngV.w86!Q';
 *        // $key = pack('H*', 'd40e1a08a07fb2e21abe2abc5910f533');
 *        // 1秒后失效
 *        $expire = 1;
 *        // 加密结果
 *        var_dump($test = Crypt::encrypt($data, $key, $expire));
 *        // 解密结果
 *        var_dump($test = Crypt::decrypt($test, $key));
 *        // true
 *        var_dump($data === $test);
 *        // 等一下下~
 *        sleep(0.1 + $expire);
 *        // false
 *        var_dump(Crypt::decrypt($test, $key));
 */
class Crypt
{
    /**
     * 校验码长度
     *     sha256的长度为32个字节, 这里只取前4个字节
     */
    const HMAC_SIZE = 4;

    /**
     * 初始化向量大小
     *     AES-128-CBC 长度为16
     *     AES-256-CBC 长度为32
     */
    const IV_SIZE = 16;

    /**
     * 加密模式
     *     AES-128-CBC
     *     AES-256-CBC
     */
    const CIPHER_MODE = 'AES-128-CBC';

    /**
     * 密码有效期长度(4个字节)
     *
     */
    const EXPIRE_SIZE = 4;

    /**
     * 加密字符串
     *
     * @param mixed  $value  待加密的数据(数字, 字符串, 数组或对象等)
     * @param string $key    加密密钥
     * @param int    $expire 加密有效期(几秒后加密失效)
     * @param string $target 编码目标
     *
     * @return string
     */
    public static function encrypt($value, $key, $expire = 0, $target = 'url')
    {
        // 随机生成初始化向量, 增加密文随机性
        $iv = static::createIV(self::IV_SIZE);
        // 序列化待加密的数据(支持数组或对象的加密)
        $value = static::packing($value);
        // 加密数据
        $value = openssl_encrypt($value, self::CIPHER_MODE, $key, OPENSSL_RAW_DATA, $iv);
        if ($value === false) {
            return false;
        }
        // 加密有效期
        $expire = $expire ? dechex(time() + $expire) : 0;
        $expire = sprintf('%08s', $expire);
        // 生成密文校验码
        $hmac = static::hmac($iv, $value, $key);
        // 组合加密结果并base64编码
        return Base64::encode(pack('H*', $hmac . $expire) . $iv . $value, $target);
    }

    /**
     * 解密字符串
     *
     * @param string $value  待加密的数据(数字, 字符串, 数组或对象等)
     * @param string $key    解密密钥
     * @param string $target 解码目标
     *
     * @return string
     */
    public static function decrypt($value, $key, $target = 'url')
    {
        // Base64解码
        $value = Base64::decode($value, $target);
        // 拆分加密结果(校验码, 有效期, 初始化向量, 加密数据)
        $hmac   = substr($value, 0, self::HMAC_SIZE);
        $expire = substr($value, self::HMAC_SIZE, self::EXPIRE_SIZE);
        $iv     = substr($value, self::HMAC_SIZE + self::EXPIRE_SIZE, self::IV_SIZE);
        $value  = substr($value, self::HMAC_SIZE + self::EXPIRE_SIZE + self::IV_SIZE);
        // 超出有效期
        if (time() > hexdec(bin2hex($expire))) {
            return false;
        }
        // 验证密文是否被篡改
        if (static::compareString(static::hmac($iv, $value, $key), bin2hex($hmac)) === false) {
            return false;
        }
        // 解密数据
        $value = openssl_decrypt($value, self::CIPHER_MODE, $key, OPENSSL_RAW_DATA, $iv);
        if ($value === false) {
            return false;
        }
        // 反序列化
        $value = static::unpacking($value);
        // 返回解密结果
        return $value;
    }

    /**
     * 随机生成指定长度的初始化向量
     *
     * @param  int $size 初始化向量长度
     *
     * @return string
     */
    protected static function createIV($size)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($size, $strong);
        }
        if (is_null($bytes) || $bytes === false || $strong === false) {
            $size *= 2;
            $pool  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $bytes = pack('H*', substr(str_shuffle(str_repeat($pool, $size)), 0, $size));
        }
        return $bytes;
    }

    /**
     * 生成指定长度的加密校验码, 保证密文安全
     *
     * @param string $iv 初始化向量
     * @param string $value 加密后的数据
     * @param string $key 加密密钥
     *
     * @return string
     */
    protected static function hmac($iv, $value, $key)
    {
        return substr(hash_hmac('sha256', $iv . $value, $key), 0, self::HMAC_SIZE * 2);
    }

    /**
     * 数据打包(数据如何序列化)
     *     serialize or json_encode
     *
     * @param mixed $value 待加密的数据
     *
     * @return string 返回序列化后的数据
     */
    protected static function packing($value)
    {
        return serialize($value);
    }

    /**
     * 数据解包(数据如何反序列化)
     *     unserialize or json_decode
     *
     * @param string $value 被序列化的数据
     *
     * @return mixed 返回被加密的数据
     */
    protected static function unpacking($value)
    {
        return unserialize($value);
    }

    /**
     * 比较字符串是否相等
     *
     * @param string $known 参考字符串
     * @param string $input 待测试字符串
     *
     * @return boolean
     */
    protected static function compareString($known, $input)
    {
        // 强制转换为字符串类型
        $known = (string) $known;
        $input = (string) $input;
        if (function_exists('hash_equals')) {
            return hash_equals($known, $input);
        }
        // 字符串长度不相等可直接返回
        $length = strlen($known);
        if ($length !== strlen($input)) {
            return false;
        }
        // 逐位比较字符串
        // 遇到字符不一致, 并不是直接返回, 这样就无法猜解字符串在哪里出错
        $result = 0;
        for ($i = 0; $i < $length; $i++) {
            $result |= (ord($known[$i]) ^ ord($input[$i]));
        }
        return $result === 0;
    }

}
