<?php

namespace App\Http\Traits;

use App\Models\FilterAction;
use App\Models\Service;

trait PreOrderFilterAction {

    public function storeAction($data)
    {
        $service_ids = $data['service_ids'];

        try {

            foreach ($service_ids as $id) {
                $service = Service::select('id', 'name')->findOrFail($id);
                
                $data['service'] = $service;
                
                $data['client_id'] = auth()->user()->id;
                
                $data['geo_location'] = [
                    'latitude'  => $data['latitude'],
                    'longitude' => $data['longitude']
                ];
                
                // dd($data['geo_location']);
                unset($data['latitude']);
                unset($data['longitude']);
                unset($data['service_ids']);
    
    
                $action = FilterAction::create([
                    'service_id' => $service->id,
                    'meta'       => json_encode($data)
    
                ]);
            }

            return true;
        } catch (\Throwable $th) {
            dd($th);
            return false;
        }
    }
}