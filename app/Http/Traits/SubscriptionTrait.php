<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Course;
use App\Models\PromoCode;
use App\Models\GradTransaction;
use App\Models\TrackGradePricing;
use App\Models\CourseTransaction;
use App\Models\CourseSubscription;

trait SubscriptionTrait {

    public function createSubscription ($data) {
        /**
         * Required data 
         */
        $target_id = $data['subscription_type'] == 'grade' ? $data['group_id'] : $data['course_id'];

        if ($this->isUserHasSubscription($data['student_id'], $target_id, $data['subscription_type']))
        return ['success' => false, 'subscripe' => false, 'error' => ['course_id' => __('courses_subscriptions.student_already_subscribed')]];

        if ($data['payment_method'] == 'wallet' && !$this->isUserHasBalance($data['student_id'], $target_id, $data))
        return ['success' => false, 'subscripe' => false, 'error' => ['student_id' => __('courses_subscriptions.has_no_balance')]];        

        try {
            DB::beginTransaction();
            
            $subscripe = CourseSubscription::create($data);
            
            $data['subscription_type'] == 'grade' 
                ? $this->createGroupTransaction($data, $subscripe)
                : $this->createCourseTransaction($data, $subscripe);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            dd('err', $th);
            return false;
        }

        // return the success flag, with subscription obj or the error flag 
        return ['success' => isset($subscripe), 'subscripe' => $subscripe, 'error' => null];
    }

    // Not been done.
    public function cancleSubscription ($subscription, $data) {
        // change order status to cancled
        // chcange transaction status to cancled
        // if transaction is electronic return mony to wallet
        try {
            DB::beginTransaction();
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return false;
        }

        return true;
    }

    // START HELPER METHODS
    private function isUserHasBalance ($user_id, $target_id, $data) {
        $target  = null;
        $wallet  = Wallet::where('user_id', $user_id)->first();

        if ($data['subscription_type'] == 'course') {
            $target = Course::find($target_id); // already been tested in the store method validation
        } else {
            $target = TrackGradePricing::find($data['plan_id']);
        }

        return isset($wallet) && $wallet->valide_balance >= $target->price;
    }
    
    private function isUserHasSubscription ($user_id, $target_id, $sub_type = 'course') {
        $query = CourseSubscription::query()
        ->where('student_id', $user_id)
        ->where('status', 'active')
        ->where(function ($q) {
            $q->orWhere('expiry_date', null);
            $q->orWhereDate('expiry_date', '>', Date('Y-m-d'));
        });

        if ($sub_type == 'course')
        $query->where('course_id', $target_id);
    
        if ($sub_type == 'grade')
        $query->where('group_id', $target_id);

        $subscripe = $query->first();

        return isset($subscripe);
    }
    
    private function createGroupTransaction ($data, CourseSubscription $subscripe) {
        // dd(3, $data);
        $group        = $subscripe->group;
        $grade        = $group->grade;
        $price_plan   = $grade->pricingPlans()->find($data['plan_id']);
        $student      = $subscripe->student;
        
        // dd(
        //     // $subscripe,
        //     // $group
        //     // $grade,
        //     // $price_plan,
        //     $student,
        // );

        $subscripe_data = [
            'transaction_num'=> $subscripe->id . rand(1000, 9999),
            
            'payment_method'  => $data['payment_method'],
            'status'          => 'done',
            
            'price'           => $price_plan->price,
            'discount'        => 0,

            'course_subscription_id'  => $subscripe->id,
            'student_id'              => $student->id,
            'grade_id'                => $grade->id,
            'grade_group_id'          => $group->id,
            'track_grade_pricing_id'  => $price_plan->id,

            // Soft data for backup
            'student_name'    => $student->user->name, 
            'grade_ar_title'  => $grade->ar_title, 
            'grade_en_title'  => $grade->en_title, 
            'group_title'     => $group->title,
        ];

        // Save a copy of the relation in the transaction as abackup
        $meta = [
            'student'    => $student->user->load('student')->toArray(),
            'group'      => $group->toArray(),
            'grade'      => $grade->toArray(),
            'price_plan' => $price_plan->toArray(),
        ];

        if ($data['payment_method'] == 'promo-code') {
            $promo_code = PromoCode::query()->where('code', $data['promo_code'])->first();
            $promo_code->is_used = 1;
            $promo_code->save();

            $subscripe_data['promo_code_id']  = $promo_code->id;
            
            $meta['promo_code']     = $promo_code->toArray();
            $meta['payment_method'] = 'promo-code';
        } else if ($data['payment_method'] == 'wallet') {
            $wallet = Wallet::where('user_id', $student->user_id)->first();

            if ($wallet->valide_balance < $price_plan->price)
            throw new Exception("Not valied balance");
            
            $wallet->valide_balance -= $price_plan->price;
            $wallet->save();
        }

        $subscripe_data['meta'] = json_encode($meta);

        $subscripe->transaction()->create($subscripe_data);

        return isset($subscripe->transaction);
    }

    private function createCourseTransaction ($data, CourseSubscription $subscripe) {
        $course  = $subscripe->course;
        $student = $subscripe->student;
        
        $subscripe_data = [
            'transaction_num'=> $subscripe->id . rand(1000, 9999),
            
            'payment_method'  => $data['payment_method'],
            'status'          => 'done',
            
            'price'           => $course->price,
            'trainer_ratio'   => $course->trainer_ratio,
            'trainer_value'   => $data['payment_method'] == 'free' ? 0 : $course->price * ($course->trainer_ratio / 100),
            'discount'        => 0,

            'course_id'       => $course->id,
            'student_id'      => $student->user_id,
            'trainer_id'      => $course->trainer->id,

            // Soft data for backup
            'student_name'    => $student->user->name, 
            'course_ar_title' => $course->ar_title, 
            'course_en_title' => $course->ar_title, 
            'tariner_name'    => $course->trainer->name,
        ];

        // Save a copy of the relation in the transaction as abackup
        $meta = [
            'student' => $student->user->load('student')->toArray(),
            'course'  => $course->toArray(),
            'trainer' => $course->trainer->toArray()
        ];

        if ($data['payment_method'] == 'promo-code') {
            $promo_code = PromoCode::query()->where('code', $data['promo_code'])->first();
            $promo_code->is_used = 1;
            $promo_code->save();

            $subscripe_data['promo_code_id']  = $promo_code->id;
            
            $meta['promo_code']     = $promo_code->toArray();
            $meta['payment_method'] = 'promo-code';
        } else if ($data['payment_method'] == 'wallet') {
            $wallet = Wallet::where('user_id', $student->user_id)->first();

            if ($wallet->valide_balance < $course->price)
            throw new Exception("Not valied balance");
            
            $wallet->valide_balance -= $course->price;
            $wallet->save();
        }

        $subscripe_data['meta'] = json_encode($meta);

        $subscripe->transaction()->create($subscripe_data);

        return isset($subscripe->transaction);
    }

    // Wallet Actions ... Old code, not been used !
    private function walletAction ($student, $course) {
        $wallet = Wallet::where('user_id', $student->student_id)->first();

        if ($wallet->valide_balance < $course->price)
        throw new Exception("Not valied balance");
        
        $wallet->valide_balance -= $course->price;
        $wallet->save();
    }

    private function promoAction ($transaction) {
        $promo_code = PromoCode::query()->where('code', $request->promo_code)->first();
        $promo_code->is_used = 1;
        $promo_code->save();

        $data['promo_code_id']  = $promo_code->id;
        
        $meta['payment_method'] = 'promo-code';
        $meta['promo_code']     = $promo_code->toArray();
    }

    private function startWalletTransfear ($transaction) {
        $client_wallet   = Wallet::where('user_id', $transaction->client_id)->first();
        $manager_wallet  = Wallet::where('user_id', $transaction->manager_id)->first();

        if ($client_wallet->valide_balance < $transaction->total_price)
        throw new Exception("Not valied balance");

        $client_wallet->valide_balance    -= $transaction->total_price;
        $manager_wallet->pendding_balance += $transaction->total_price;
        
        $client_wallet->save();
        $manager_wallet->save();
    }

    private function cancleWalletTransfear ($transaction) {
        $client_wallet   = Wallet::where('user_id', $transaction->client_id)->first();
        $manager_wallet  = Wallet::where('user_id', $transaction->manager_id)->first();
        
        $client_wallet->valide_balance    += $transaction->total_price;
        $manager_wallet->pendding_balance -= $transaction->total_price;

        $client_wallet->save();
        $manager_wallet->save();
    }

    private function finshWalletTransfear ($transaction) {
        $manager_wallet  = Wallet::where('user_id', $transaction->manager_id)->first();

        $manager_wallet->pendding_balance  -= $transaction->total_price;
        $manager_wallet->valide_balance    += $transaction->total_price;

        $manager_wallet->save();
    }

    private function updateWallettransaction ($transaction, $extra_amount) {
        $client_wallet   = Wallet::where('user_id', $transaction->client_id)->first();
        $manager_wallet  = Wallet::where('user_id', $transaction->manager_id)->first();
        
        $client_wallet->valide_balance    += $extra_amount;
        $manager_wallet->pendding_balance -= $extra_amount;
        
        $client_wallet->save();
        $manager_wallet->save();
    }

}