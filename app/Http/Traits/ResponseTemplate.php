<?php
namespace App\Http\Traits;

trait ResponseTemplate
{
    public function responseTemplate ($data = null, $success = true, $msg = null, $code = 200) {
        return response()->json(['success' => $success, 'data' => $data, 'msg' => $msg], $code);
    }
}
