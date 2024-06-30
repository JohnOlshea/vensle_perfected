<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\BusinessDetails;

/**
 * @group Business Details Management
 *
 * APIs to manage business details
 */
class BusinessDetailsController extends Controller
{

    public function getBusinessDetails()
    {
        try {
            $user = auth()->user();
            $businessDetails = $user->businessDetails;
	    //dd($businessDetails);

            return response()->json(['businessDetails' => $businessDetails]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                'user_id' => 'required|exists:users,id',
                'business_name' => 'required|string',
                'business_email' => 'unique:business_details,business_email|nullable|email',
                'phone' => 'required|string',
                'business_address' => 'required|string',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'certificate' => 'nullable|string',
                'profile_picture' => 'nullable|string',
                ]
            );

            $businessDetails = BusinessDetails::create($request->all());

            return response()->json(['message' => 'Business details created successfully', 'data' => $businessDetails]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $businessDetails = BusinessDetails::where('user_id', $id)->first();

            if (!$businessDetails) {
                return response()->json(['message' => 'Business details not found'], 404);
            }

            return response()->json(['data' => $businessDetails]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request)
    {
	    try {
		$user = auth()->user();
		$businessDetails = $user->businessDetails;

		// Check if businessDetails exists, if not, create a new entry
		if (!$businessDetails) {
		    $businessDetails = new BusinessDetails();
		    $businessDetails->user_id = $user->id;
		}

		$rules = [
		    'business_name' => 'sometimes|required|string',
		    'business_email' => 'sometimes|unique:business_details,business_email,' . $businessDetails->id . '|nullable|email',
		    'phone' => 'sometimes|required|string',
		    'business_address' => 'sometimes|required|string',
		    'bank_name' => 'sometimes|required|string',
		    'account_number' => 'sometimes|required|string',
		];

		if ($request->hasFile('certificate') && !is_string($request->input('certificate'))) {
			$rules['certificate'] = 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx';
		}

		if ($request->hasFile('profile_picture') && !is_string($request->input('profile_picture'))) {
			$rules['profile_picture'] = 'nullable|image|mimes:jpeg,jpg,png|max:2048';
		}

		$request->validate($rules);

		if ($request->hasFile('certificate') && !is_string($request->input('certificate'))) {
		    $this->deleteFileIfExists("uploads/business/" . $businessDetails->certificate);
		    $certificate = $this->uploadFile($request->file('certificate'));
		    $businessDetails->certificate = $certificate;
		}

		if ($request->hasFile('profile_picture') && !is_string($request->input('profile_picture'))) {
		    $this->deleteFileIfExists("uploads/business/" . $businessDetails->profile_picture);
		    $profilePicture = $this->uploadFile($request->file('profile_picture'));
		    $businessDetails->profile_picture = $profilePicture;
		}

		// Update other fields
		$businessDetails->update($request->except(['certificate', 'profile_picture']));

		return response()->json(['message' => 'Business details updated successfully', 'data' => $businessDetails]);
	    } catch (\Exception $e) {
		return response()->json(['error' => $e->getMessage()], 500);
	    }
    }

    private function deleteFileIfExists($filePath)
    {
        if ($filePath && file_exists(public_path($filePath))) {
            unlink(public_path($filePath));
        }
    }

    private function uploadFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $imageName = Str::random(32) . "." . $extension;
        $file->move('uploads/business/', $imageName);

        return $imageName;
    }

    /*public function update(Request $request)
    {
        try {
            $user = auth()->user();
            $businessDetails = $user->businessDetails;

            $request->validate(
                [
                'business_name' => 'nullable|required',
                'business_email' => 'unique:business_details,business_email,' . $businessDetails->id . '|nullable|email',
                'phone' => 'nullable|string',
                'business_address' => 'nullable|string',
                'bank_name' => 'nullable|string',
                'account_number' => 'nullable|string',
                'certificate' => $request->input('certificate_status') === 'new'
                ? 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx'
                : 'sometimes|string',
                'profile_picture' => $request->input('profile_picture_status') === 'new'
                ? 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
                : 'sometimes|string',
                ]
            );


            if ($request->profile_picture_status === 'new' && $request->hasFile('profile_picture')) {
                // Delete existing profile picture if it exists
                if ($businessDetails->profile_picture) {
                    File::delete(public_path('uploads/' . $businessDetails->profile_picture));
                }

                $profilePicture = $request->file('profile_picture');
                $profilePictureName = Str::random(32) . '.' . $profilePicture->getClientOriginalExtension();
                $profilePicture->move(public_path('uploads/'), $profilePictureName);

                $businessDetails->profile_picture = $profilePictureName;
            }

            // Handle certificate
            if ($request->certificate_status === 'new' && $request->hasFile('certificate')) {
                // Delete existing certificate if it exists
                if ($businessDetails->certificate) {
                    File::delete(public_path('uploads/' . $businessDetails->certificate));
                }

                $certificate = $request->file('certificate');
                $certificateName = Str::random(32) . '.' . $certificate->getClientOriginalExtension();
                $certificate->move(public_path('uploads/'), $certificateName);

                $businessDetails->certificate = $certificateName;
            }

            $businessDetails->update($request->except(['profile_picture', 'certificate']));

            return response()->json(['message' => 'Business details updated successfully', 'data' => $businessDetails]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }*/



    public function destroy($id)
    {
        try {
            $businessDetails = BusinessDetails::findOrFail($id);

            $businessDetails->delete();

            return response()->json(['message' => 'Business details deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
