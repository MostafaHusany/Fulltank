<?php

namespace App\Http\Traits;

use Illuminate\Support\Str;

use App\Models\OTP;

trait OtpGeneration {

    public function generateOTP($user)
    {
        $OTP = new OTP;

        $old_code = $OTP->where('user_id', $user->id)->first();

        if (isset($old_code)) {
            $old_code->delete();
        }

        $otp = $OTP->create([
            'user_id' => $user->id,
            'code'    => rand(0, 9999) ,
            'uuid'    => Str::random()
        ]);

        return $otp->first();
    }

    public function validateOTP($code, $user)
    {
        $OTP  = OTP::query()
            ->where('code', $code)
            ->where('user_id', $user->id)
            ->first();

        if (isset($OTP) && $OTP->created_at->format('y/m/d') === now()->format('y/m/d')) {
        
            $res = [
                'success' => true,
                'message' => 'Phone has been verified successfully',
            ];

            if (isset($user->phone_verified_at))
                $res['uuid'] = $OTP->uuid;
            
            
            if (!isset($user->phone_verified_at)) {
                $user->update(['phone_verified_at' => now()]);
                $OTP->delete();
            }

            return $res;
        }
    }

    public function validateOtpByUUID($uuid, $new_password)
    {
        $OTP = OTP::query()
            ->where('uuid', $uuid)
            ->first();

        if (isset($OTP) && $OTP->created_at->format('y/m/d') === now()->format('y/m/d')) {
            
            $user = $OTP->user->update([
                'password' => bcrypt($new_password)
            ]);

            $OTP->delete();

            return true;
        }
    }
}