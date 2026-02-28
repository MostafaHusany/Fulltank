<?php

return [
    'title'                   => 'Notifications',
    'no_notifications'        => 'No notifications',
    'no_notifications_desc'   => 'You will be notified about important events here.',
    'mark_all_read'           => 'Mark all as read',
    'all_marked_read'         => 'All notifications marked as read',
    'view_all'                => 'View all notifications',
    'mark_read'               => 'Mark as read',
    'delete'                  => 'Delete',
    'deleted'                 => 'Notification deleted',
    'confirm_delete'          => 'Are you sure you want to delete this notification?',
    'view'                    => 'View',
    'new'                     => 'New',
    'total_notifications'     => 'Total notifications',

    'low_balance_title'       => 'Low Balance Warning',
    'low_balance_message'     => 'Your wallet balance (:balance SAR) has dropped below the threshold (:threshold SAR). Please recharge soon.',

    'quota_warning_title'     => 'Quota Warning',
    'quota_warning_message'   => 'Vehicle :vehicle has reached :percentage% of its monthly fuel quota.',

    'denied_fueling_title'    => 'Fueling Denied',
    'denied_fueling_message'  => 'Fueling request denied for :vehicle (:driver at :station). Reason: :reason',

    'deposit_approved_title'  => 'Deposit Approved',
    'deposit_approved_message'=> 'Your deposit request #:ref for :amount SAR has been approved.',

    'deposit_rejected_title'  => 'Deposit Rejected',
    'deposit_rejected_message'=> 'Your deposit request #:ref for :amount SAR has been rejected.',

    'types' => [
        'low_balance'     => 'Low Balance',
        'quota_warning'   => 'Quota Warning',
        'denied_fueling'  => 'Denied Fueling',
        'deposit_approved'=> 'Deposit Approved',
        'deposit_rejected'=> 'Deposit Rejected',
    ],
];
