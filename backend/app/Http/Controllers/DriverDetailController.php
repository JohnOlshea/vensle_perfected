<?php

namespace App\Http\Controllers;

use App\Models\DriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverDetailController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        //$this->validate($request, [
	$request->validate([
            'license_identification_number' => 'required|string',
            'vehicle_registration_number' => 'required|string',
            'vehicle_make_model' => 'required|string',
            'vehicle_color' => 'required|string',
            'license_plate_number' => 'required|string',
	    'license_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'vehicle_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        $userId = Auth::id();


        // Handle license image upload
        $licenseImage = $request->file('license_image');
        $licenseImagePath = $licenseImage->store('license_images', 'public');

        // Handle vehicle photo upload
        $vehiclePhoto = $request->file('vehicle_photo');
        $vehiclePhotoPath = $vehiclePhoto->store('vehicle_photos', 'public');

        // Prepare data for creating or updating the driver details
        $data = [
            'user_id' => $userId,
            'license_identification_number' => $request->input('license_identification_number'),
            'vehicle_registration_number' => $request->input('vehicle_registration_number'),
            'vehicle_make_model' => $request->input('vehicle_make_model'),
            'vehicle_color' => $request->input('vehicle_color'),
            'license_plate_number' => $request->input('license_plate_number'),
            'license_image_path' => $licenseImagePath,
            'vehicle_photo_path' => $vehiclePhotoPath,
        ];

        // Create or update the driver details
        $driver = DriverDetail::updateOrCreate(
            ['user_id' => $userId],
            $data
        );

        // Return a response
        return response()->json([
            'message' => 'Driver details saved successfully',
            'driver' => $driver,
        ]);	
    }    
}
