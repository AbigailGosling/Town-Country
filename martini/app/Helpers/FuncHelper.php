<?php
namespace App\Helpers;

use App\Models\CommentLogging;

class FuncHelper
{
    static function custom_intersect(array $arrayOne, array $arrayTwo):array
    {
        //Fastest array intersect https://stackoverflow.com/a/53203232/1856411
        $first = array_flip($arrayOne);
        $second = array_flip($arrayTwo);

        $x = array_intersect_key($first, $second);

        return array_flip($x);
    }
    public static function custom_unique(array $array):array
    {
		$x = array();
		foreach ($array as $item)
		{
			if ($item !== null && $item !== "")$x[$item] = true;
		}
		return array_keys($x);
	}
    public static function loggedDataChange(int $user_id, string $type, int $id, ?string $body = "")
    {
        if ($body == null) $body = "";
        $log = CommentLogging::where('type',$type)->where('entity_id',$id)->orderBy('id','desc')->firstOrNew();
        if ($log->exists && $log->body == $body) return;
        $log->entity_id = $id;
        $log->type = $type;
        $log->body = $body;
        $log->user_id = $user_id;
        $log->save();
    }
    public static function floorDec($val, $precision = 2) {
		if ($precision < 0) { $precision = 0; }
		$numPointPosition = intval(strpos($val, '.'));
		if ($numPointPosition === 0) { //$val is an integer
			return $val;
		}
		return floatval(substr($val, 0, $numPointPosition + $precision + 1));
	}
	public static function ceilDec($value, $precision = 2) {
		$offset = 0.5;
		if ($precision !== 0)
			$offset /= pow(10, $precision);
		$final = round($value + $offset, $precision, PHP_ROUND_HALF_DOWN);
		return ($final == -0 ? 0 : $final);
	}
    public static function array_search_multidim(array $array,string|int $column,mixed $needle):mixed
    {
        return $array[array_search($needle, array_column($array, $column))];
    }
    public static function array_consecutive(array $d):bool{
        for($i=0;$i<count($d);$i++) {
            if(isset($d[$i+1]) && $d[$i]+1 != $d[$i+1]) {
                return false;
            }
        }
        return true;
    }
}
