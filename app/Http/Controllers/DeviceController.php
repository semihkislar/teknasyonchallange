<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function register(Request $request)
    {
        $validationRules = [
            'udid' => 'required|max:40|unique:devices',
            'os' => 'required',
            'app_id' => 'required|required|exists:applications,id',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            $validationErrors = $validator->failed();

            if (isset($validationErrors['udid']['Unique'])) {
                return response()->json([
                    'status' => 'failure',
                    'error' => 'There is a device registered with this udid',
                ]);
            }

            if (isset($validationErrors['app_id']['Exists'])) {
                return response()->json([
                    'status' => 'failure',
                    'error' => 'No application with this app_id',
                ]);
            }

            return response()->json([
                'status' => 'failure',
                'error' => 'Invalid Request',
            ]);
        }

        $clientToken = $this->generateToken();
        $deviceData = $request->only(['udid', 'app_id', 'os', 'os_version', 'device_model']);
        $deviceData['client_token'] = $clientToken;
        $device = Device::create($deviceData);
        return ['device' => $device];
    }

    private function generateToken()
    {
        return Str::uuid()->toString();
    }
}
