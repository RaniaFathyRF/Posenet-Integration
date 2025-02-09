<?php

namespace App\Http\Controllers;

use App\Models\Trial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\MeasurementService;
use App\Models\Size;


class TrialController extends Controller
{
    private const PER_PAGE = 12;
    private $measurementService;
    private const CHEST_CIRCUMFERENCE_FACTOR = 2.5;
    private const WAIST_CIRCUMFERENCE_FACTOR = 2.5;
    private const NECK_CIRCUMFERENCE_FACTOR = 0.5;


    public function __construct(MeasurementService $measurementService)
    {
        $this->measurementService = $measurementService;
    }

    public function index()
    {
        $trials = Trial::orderBy('created_at', 'desc')
            ->paginate(self::PER_PAGE);

        return view('trial.index', compact('trials'));
    }

    public function create()
    {
        return view('trial.create');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'trial_name' => 'required|string|max:255',
                'height' => 'required|numeric',
                'weight' => 'required|numeric',
                'gender' => 'required|in:male,female',
                'data' => 'required|array',
            ]);

            $data = $request->input('data');
            $rightEye = $data['rightEye'] ?? null;
            $leftEye = $data['leftEye'] ?? null;
            $nose = $data['nose'] ?? null;
            $rightShoulder = $data['rightShoulder'] ?? null;
            $leftShoulder = $data['leftShoulder'] ?? null;
            $leftElbow = $data['leftElbow'] ?? null;
            $rightElbow = $data['rightElbow'] ?? null;
            $leftWrist = $data['leftWrist'] ?? null;
            $rightWrist = $data['rightWrist'] ?? null;
            $leftHip = $data['leftHip'] ?? null;
            $rightHip = $data['rightHip'] ?? null;
            $leftAnkle = $data['leftAnkle'] ?? null;

            $measurements = $this->measurementService->calculateMeasurements($data, $validatedData['height'], $validatedData['weight']);

            $trial = Trial::create([
                'trial_name' => $validatedData['trial_name'],
                'height' => $validatedData['height'],
                'weight' => $validatedData['weight'],
                'gender' => $validatedData['gender'],
                'righteye' => $rightEye,
                'lefteye' => $leftEye,
                'rightshoulder' => $rightShoulder,
                'leftshoulder' => $leftShoulder,
                'rightElbow' => $rightElbow,
                'leftElbow' => $leftElbow,
                'rightWrist' => $rightWrist,
                'leftWrist' => $leftWrist,
                'rightHip' => $rightHip,
                'leftHip' => $leftHip,
                'image_data' => $data['image'] ?? null,
                "measurements" => json_encode($measurements),
            ]);

            return response()->json([
                'success' => true,
                'trial' => $trial,
                // 'sizes' =>  json_encode($sizes)
            ], 201);
        } catch (\Exception $e) {
            Log::error('Trial creation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Trial $trial)
    {
        $size = $this->getRecommendedSize($trial->gender, $trial->height, $trial->weight, $trial->weight);

        return view('trial.show', [
            'trial' => $trial,
            'measurements' => $trial->measurements,
            'size' => $size,
        ]);
    }

    public function getRecommendedSize($gender, $height, $width, $weight)
    {
        $size = DB::table('sizes_gender')
            ->where('gender', $gender)
            ->where('height_min', '<=', $height)
            ->where('height_max', '>=', $height)
            ->where('weight_min', '<=', $width)
            ->where('weight_max', '>=', $width)
            ->first();

        Log::info('size', [$size]);

        if (!$size) {
            Log::info('Size not found for height: ' . $height . ' and weight: ' . $weight);
            $size = DB::table('sizes_gender')
                ->where('gender', $gender)
                ->where('weight_min', '<=', $weight)
                ->where('weight_max', '>=', $weight)
                ->first();
        }

        return $size ? $size->size : 'Size not found';
    }


    function destroy(Trial $trial)
    {
        $trial->delete();
        return redirect()->route('trials.index')->with('success', 'Trial deleted successfully.');
    }
}
