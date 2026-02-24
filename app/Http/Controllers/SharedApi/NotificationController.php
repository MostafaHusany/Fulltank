<?php

namespace App\Http\Controllers\SharedApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SharedApi\NotificationUpdateRequest;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function __construct(private Notification $notification)
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = Notification::query()
        ->where('is_hidden', false)
        ->where('user_id', auth()->user()->id)->get();
        
        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notification = $this->notification->find($id);
        
        return response()->json([
            'success' => true,
            'data' => $notification,
        ], 200);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(NotificationUpdateRequest $request, string $id)
    {
        $data = $request->validated();

        $notification = $this->notification->find($id);

        $status = $this->NotificationUpdateStatus($data['status'], $notification);

        return response()->json([
            'success'   => true,
            'message'   => $status['message']
        ], 200);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notification = $this->notification->find($id);
        
        $notification->update([
            'is_hidden' => true
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Notification has been deleted successfully'
        ], 200);
    }

    private function NotificationUpdateStatus($status, $notification)
    {
        if($status === 'read')
        {
            $notification->update([
                'is_read' => true,
            ]);

            return [
                'message' => 'notification has been read successfully'
            ];
        }
        else {
            
            $notification->update([
                'is_action' => true,
            ]);

            return [
                'message' => 'notification has been visited successfully'
            ];
        }
    }
}
