<?php

namespace App\Helpers;

class Api {
    public static function restSuccess($message,$data = false) {
        $res['status'] = 200;
        $res['message'] = $message;
        if ($data) {
            $res['data'] = $data;
        }
        return response()->json($res);
    }

    public static function restError($message,$data = false) {
        $res['status'] = 400;
        $res['message'] = $message;
        if ($data) {
            $res['data'] = $data;
        }
        return response()->json($res);
    }
}
