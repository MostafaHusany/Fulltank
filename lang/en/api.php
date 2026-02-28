<?php

return [

    'auth' => [
        'username_required'    => 'Username or phone is required.',
        'password_required'    => 'Password is required.',
        'password_min'         => 'Password must be at least 4 characters.',
        'invalid_credentials'  => 'Invalid username or password.',
        'category_not_allowed' => 'Your account type is not allowed to access this app.',
        'account_inactive'     => 'Your account is inactive. Please contact support.',
        'login_success'        => 'Login successful.',
        'logout_success'       => 'Logged out successfully.',
        'unauthenticated'      => 'Please login to continue.',
        'profile_incomplete'   => 'Your profile is incomplete. Please contact support.',
        'profile_refreshed'    => 'Profile refreshed successfully.',
    ],

    'driver' => [
        'no_vehicle_assigned' => 'No vehicle assigned to your account.',
    ],

    'worker' => [
        'not_assigned' => 'You are not assigned to any station.',

        'otp_required'          => 'OTP code is required.',
        'otp_invalid_format'    => 'OTP code must be exactly 6 digits.',
        'request_not_found'     => 'Fueling request not found or already processed.',
        'request_expired'       => 'This fueling request has expired.',
        'ready_to_fuel'         => 'Request verified. Ready to fuel.',

        'request_id_required'   => 'Request ID is required.',
        'actual_liters_required' => 'Actual liters dispensed is required.',
        'actual_liters_min'     => 'Actual liters must be at least 0.1.',

        'client_wallet_not_found' => 'Client wallet not found.',
        'client_wallet_inactive'  => 'Client wallet is inactive.',
        'insufficient_balance'    => 'Insufficient client balance. Available: :available, Required: :required.',
        'quota_exceeded'          => 'Vehicle quota exceeded. Remaining: :remaining liters.',

        'fueling_completed'       => 'Fueling completed successfully.',

        'transaction_id_required' => 'Transaction ID is required.',
        'transaction_not_found'   => 'Transaction not found.',
        'transaction_not_yours'   => 'You cannot upload proof for this transaction.',
        'image_required'          => 'Pump meter image is required.',
        'image_invalid'           => 'Please upload a valid image file.',
        'image_too_large'         => 'Image size must not exceed 5MB.',
        'proof_uploaded'          => 'Proof image uploaded successfully.',
        'upload_failed'           => 'Failed to upload image. Please try again.',
    ],

    'common' => [
        'success'       => 'Operation successful.',
        'error'         => 'An error occurred.',
        'not_found'     => 'Resource not found.',
        'unauthorized'  => 'Unauthorized access.',
        'forbidden'     => 'Access denied.',
    ],

    'fuel_request' => [
        'amount_required'      => 'Fuel amount is required.',
        'amount_min'           => 'Minimum fuel amount is 1 liter.',
        'amount_max'           => 'Maximum fuel amount is 500 liters.',
        'fuel_type_required'   => 'Fuel type is required.',
        'fuel_type_invalid'    => 'Invalid fuel type selected.',
        'fuel_type_inactive'   => 'Selected fuel type is not available.',

        'no_vehicle'           => 'No vehicle assigned to your account.',
        'vehicle_inactive'     => 'Your vehicle is currently inactive.',
        'no_client'            => 'Your account is not linked to a client.',
        'pending_exists'       => 'You already have an active fueling request. Please complete or cancel it first.',

        'quota_exceeded'       => 'Requested amount exceeds your remaining quota. Remaining: :remaining liters.',
        'no_wallet'            => 'Client wallet not found.',
        'wallet_inactive'      => 'Client wallet is inactive.',
        'insufficient_balance' => 'Insufficient balance. Available: :balance, Required: :required.',

        'created'              => 'Fueling request created successfully.',
        'create_failed'        => 'Failed to create fueling request. Please try again.',
        'not_found'            => 'Fueling request not found or already processed.',
        'cancelled'            => 'Fueling request cancelled successfully.',
        'expired'              => 'Fueling request has expired.',
    ],

    'stations' => [
        'lat_required'  => 'Latitude is required.',
        'lat_invalid'   => 'Latitude must be between -90 and 90.',
        'lng_required'  => 'Longitude is required.',
        'lng_invalid'   => 'Longitude must be between -180 and 180.',
        'not_found'     => 'Station not found.',
        'no_nearby'     => 'No stations found within the specified radius.',
    ],

];
