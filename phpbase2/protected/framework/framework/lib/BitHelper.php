<?php

/**
 * BitHelper.
 */
class BitHelper {
    protected static $outputArgs = [];

    public static function writeBool($value) {
        $data = pack('c', $value ? 1 : 0);
        self::$outputArgs[] = $data;

        return 1;
    }

    public static function writeByte($value) {
        $data = pack('c', $value);
        self::$outputArgs[] = $data;

        return 1;
    }

    public static function writeI16($value) {
        $data = pack('n', $value);
        self::$outputArgs[] = $data;

        return 2;
    }

    public static function writeI32($value) {
        $data = pack('N', $value);
        self::$outputArgs[] = $data;

        return 4;
    }

    public static function writeI64($value) {
        // If we are on a 32bit architecture we have to explicitly deal with
        // 64-bit twos-complement arithmetic since PHP wants to treat all ints
        // as signed and any int over 2^31 - 1 as a float
        if (PHP_INT_SIZE == 4) {
            $neg = $value < 0;

            if ($neg) {
                $value *= -1;
            }

            $hi = (int) ($value / 4294967296);
            $lo = (int) $value;

            if ($neg) {
                $hi = ~$hi;
                $lo = ~$lo;
                if (($lo & (int) 0xffffffff) == (int) 0xffffffff) {
                    $lo = 0;
                    $hi++;
                } else {
                    $lo++;
                }
            }
            $data = pack('N2', $hi, $lo);

        } else {
            $hi = $value >> 32;
            $lo = $value & 0xFFFFFFFF;
            $data = pack('N2', $hi, $lo);
        }

        self::$outputArgs[] = $data;
        
        return 8;
    }

    public static function writeDouble($value) {
        $data = pack('d', $value);
        self::$outputArgs[] = $data;
        return 8;
    }

    public static function writeString($value) {
        $len = strlen($value);
        $result = self::writeI32($len);
        if ($len) {
            self::$outputArgs[] = $value;
        }

        return $result + $len;
    }

    public static function readBool(&$str) {
        $data = substr($str, 0, 1);
        $str = substr($str, 1);
        $arr = unpack('c', $data);

        return $arr[1] == 1;
    }

    public static function readByte(&$str) {
        $data = substr($str, 0, 1);
        $str = substr($str, 1);
        $arr = unpack('c', $data);

        return $arr[1];
    }

    public static function readI16(&$str) {
        $data = substr($str, 0, 2);
        $str = substr($str, 2);

        $arr = unpack('n', $data);
        $value = $arr[1];
        if ($value > 0x7fff) {
            $value = 0 - (($value - 1) ^ 0xffff);
        }

        return $value;
    }

    public static function readI32(&$str) {
        $data = substr($str, 0, 4);
        $str = substr($str, 4);

        $arr = unpack('N', $data);
        $value = $arr[1];
        if ($value > 0x7fffffff) {
            $value = 0 - (($value - 1) ^ 0xffffffff);
        }

        return $value;
    }

    public static function readI64(&$str) {
        $data = substr($str, 0, 8);
        $str = substr($str, 8);

        $arr = unpack('N2', $data);

        // If we are on a 32bit architecture we have to explicitly deal with
        // 64-bit twos-complement arithmetic since PHP wants to treat all ints
        // as signed and any int over 2^31 - 1 as a float
        if (PHP_INT_SIZE == 4) {

            $hi = $arr[1];
            $lo = $arr[2];
            $isNeg = $hi < 0;

            // Check for a negative
            if ($isNeg) {
                $hi = ~$hi & (int) 0xffffffff;
                $lo = ~$lo & (int) 0xffffffff;

                if ($lo == (int) 0xffffffff) {
                    $hi++;
                    $lo = 0;
                } else {
                    $lo++;
                }
            }

            // Force 32bit words in excess of 2G to pe positive - we deal wigh sign
            // explicitly below

            if ($hi & (int) 0x80000000) {
                $hi &= (int) 0x7fffffff;
                $hi += 0x80000000;
            }

            if ($lo & (int) 0x80000000) {
                $lo &= (int) 0x7fffffff;
                $lo += 0x80000000;
            }

            $value = $hi * 4294967296 + $lo;

            if ($isNeg) {
                $value = 0 - $value;
            }
        } else {

            // Upcast negatives in LSB bit
            if ($arr[2] & 0x80000000) {
                $arr[2] = $arr[2] & 0xffffffff;
            }

            // Check for a negative
            if ($arr[1] & 0x80000000) {
                $arr[1] = $arr[1] & 0xffffffff;
                $arr[1] = $arr[1] ^ 0xffffffff;
                $arr[2] = $arr[2] ^ 0xffffffff;
                $value = 0 - $arr[1] * 4294967296 - $arr[2] - 1;
            } else {
                $value = $arr[1] * 4294967296 + $arr[2];
            }
        }

        return $value;
    }

    public static function readDouble(&$str) {
        $data = substr($str, 0, 8);
        $str = substr($str, 8);

        $data = strrev($data);
        $arr = unpack('d', $data);
        $value = $arr[1];

        return $value;
    }

    public static function readString(&$str) {
        $len = self::readI32($str);
        $value = substr($str, 0, $len);
        $str = substr($str, $len);

        return $value;
    }

    public static function getWriteBuffer() {
        $str = join('', self::$outputArgs);
        self::$outputArgs = [];

        return $str;
    }
}
