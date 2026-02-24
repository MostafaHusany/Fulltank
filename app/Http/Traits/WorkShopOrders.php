<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Client;
use App\Models\WorkShop;

trait WorkShopOrders {

    public function createOrder ($data) {
        $client_id  = auth()->user()->category == 'client' ? auth()->user()->id : $data['client_id'];

        $workshop   = WorkShop::findOrFail($data['work_shop_id']);

        $parsedData = $this->parseOrderData($workshop, $data, $client_id, true);
        
        $order_data    = $parsedData['order_data']; 
        $services_data = $parsedData['services_data'];
        
        try {
            DB::beginTransaction();
            
            $order = $workshop->orders()->create($order_data);
            $order->services()->sync($services_data);
            $order->transaction()->create($this->parseTransactionData($order));
            
            
            if ($order->payment_type == 'online')
            $this->startWalletTransfear($order->transaction);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            
            // dd($th);
            return false;
            // return response()->json(['data' => null, 'success' => false, 'message' => $th->getMessage()], 500);
            // return $this->responseTemplate(null, false, $th->getMessage());
            // return $this->responseTemplate(null, false, ENV('APP_DEBUG') ? $th->getMessage() : __('workshops_orders.object_error'));
        }

        return $order;
    }

    public function updateOrder ($data, $order) {
        $client_id  = auth()->user()->category == 'client' ? auth()->user()->id : $data['client_id'];
        
        $workshop   = WorkShop::findOrFail($data['work_shop_id']);

        $parsedData = $this->parseOrderData($workshop, $data, $client_id, true);

        $order_data    = $parsedData['order_data']; 
        $services_data = $parsedData['services_data'];

        try {
            DB::beginTransaction();
            
            if($order->payment_type == 'online') // cancle old transaction
            $this->cancleWalletTransfear($order->transaction);

            $order->update($order_data);
            $order->services()->sync($services_data);

            $order->transaction()->update($this->parseTransactionData($order));
            $order->load(['transaction']);
            
            if($order->payment_type == 'online') // re-calculate the transaction
            $this->startWalletTransfear($order->transaction);
            
            DB::commit();

        } catch (\Throwable $th) {
            DB::rollback();
            // dd($th);
            return $this->responseTemplate(null, false, $th->getMessage());
            // return $this->responseTemplate(null, false, ENV('APP_DEBUG') ? $th->getMessage() : __('workshops_orders.object_error'));
        }

        return $this->responseTemplate($order, true, __('workshops_orders.object_updated'));
    }
    
    public function reCalculateOrder ($order) {
        
        if($order->payment_type == 'online') // cancle old transaction
        $this->cancleWalletTransfear($order->transaction);

        $services = $order->services()->get();
        $total    = $this->calculateTotalServices($services);

        $order_meta = (array) json_decode($order->meta);
        $order_meta['reserved_services'] = $services;

        $order->update([
            'meta'                      => json_encode($order_meta),
            'total_price'               => $total['total_price'],
            'total_astimated_time'      => $total['total_esimated_time']['hour'],
            'total_astimated_time_type' => $total['time_type']
        ]);
        $order->transaction()->update($this->parseTransactionData($order));
        $order->load(['transaction']);

        if($order->payment_type == 'online') // re-calculate the transaction
        $this->startWalletTransfear($order->transaction);

        return $order;
    }

    public function cancleOrder ($order) {
        // change order status to cancled
        // chcange transaction status to cancled
        // if transaction is electronic return mony to wallet
        try {
            DB::beginTransaction();
            
            if (isset($order->payment_type) && $order->payment_type == 'online')
            $this->cancleWalletTransfear($order->transaction);
            
            $order->status = 'canceled';
            $order->save();

            $order->transaction->status = 'canceled'; 
            $order->transaction->save();
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            // dd($th);

            return false;
        }

        return true;
    }

    public function completeOrder ($order) {
        // finish the transaction
        // make the money from pendding status to valied balance for the workshop manager
        try {
            DB::beginTransaction();
            
            if (isset($order->payment_type) && $order->payment_type == 'online')
            $this->finshWalletTransfear($order->transaction);

            $order->status = 'completed';
            $order->save();

            $order->transaction->status = 'completed';
            $order->transaction->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            // dd($th->getMessage());

            return false;
        }

        return true;
    }
    
    // START HELPER METHODS
    // START HELPER METHODS
    // START HELPER METHODS
    private function parseOrderData ($workshop, $data, $client_id, $is_new = false) {
        /** 
         * Shape the data structure that will be used for creating new order/services
         */
        $client = Client::with(['user'])->findOrFail($client_id);

        $order_services = $workshop->services()
        ->whereIn('service_id', $data['service_ids'])->get();
        
        $total = $this->calculateTotalServices($order_services, $is_new);
        
        $order_data = [
            'client_id'    => $client_id,
            'type'         => $data['type'],
            'car_id'       => $data['car_id'],
            'total_price'  => $total['total_price'],
            'status'       => 'pendding',
            'payment_type' => isset($data['payment_type']) ? $data['payment_type'] : 'cash', 
            'meta'         => json_encode([
                'reserved_services' => $order_services,
                'workshop'          => $workshop,
                'client'            => $client,
                'car'               => $client->cars()->findOrFail($data['car_id'])
            ]),
            'total_astimated_time'      =>  $total['total_esimated_time']['hour'],
            'total_astimated_time_type' =>  $total['time_type'],
            'reservation_date'          =>  $data['type'] == 'scheduled' 
                ? (isset($data['reservation_date']) ? $data['reservation_date'] : Date('Y-m-d')) : null
        ];

        $services_data = [];
        foreach ($order_services as $service) {
            $services_data[$service->id] = [
                'price'                 => $service->pivot->price,
                'created_at'            => Date('Y-m-d'),
                'updated_at'            => Date('Y-m-d'),	
                'astimated_time'        => $service->pivot->astimated_time,
                'astimated_time_type'   => $service->pivot->astimated_time_type,
            ];
        }

        return ['order_data' => $order_data, 'services_data' => $services_data];
    }

    private function calculateTotalServices ($order_services, $is_new = false) {
        $total_price = 0;

        $total_estimated_time = ['day' => 0, 'hour' => 0];
        
        foreach ($order_services as $service) {
            if ($is_new || in_array($service->pivot->status, ['approved', 'initial'])) {
                $total_price += $service->pivot->price;

                if ($service->pivot->astimated_time_type === 'day') {
                    $total_estimated_time['day'] += $service->pivot->astimated_time;
                }
                else {
                    $total_estimated_time['hour'] += $service->pivot->astimated_time;
                }
            }
        }

        $estimated_time_arr = $order_services->pluck('pivot.astimated_time_type')->toArray();

        $time_type;

        if (in_array('day', $estimated_time_arr)) {
            $day_hours = $total_estimated_time['day']*24;
            $total_estimated_time['hour'] += $day_hours;
        }

        return [
            'total_price'         => $total_price,
            'total_esimated_time' => $total_estimated_time,
            'time_type'           => 'hour',
        ];
    }

    private function parseTransactionData ($order) {
        return [
            'order_id'        => $order->id, 
            'client_id'       => $order->client_id,
            'work_shop_id'    => $order->work_shop_id,
            'total_price'     => $order->total_price, 
            'manager_id'      => $order->workshop->work_shop_manager_id,
            'payment_type'    => $order->payment_type, 
            'reviewe_status'  => 'not settled',
            'transaction_num' => $order->id . rand('10000', '99999'),
            'meta'  => json_encode([
                'order'    => $order,
                'workshop' => $order->workshop,
                'client'   => User::with(['client'])->findOrFail($order->client_id),
                'manager'  => $order->workshop,
            ])
        ];
    }

    // Wallet Actions ...
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