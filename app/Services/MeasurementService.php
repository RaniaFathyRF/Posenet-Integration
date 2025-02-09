<?php

// namespace App\Services;

// class MeasurementService
// {
//     private const CONFIDENCE_THRESHOLD = 0.5;

//     public function calculateShoulderWidth(array $leftShoulder, array $rightShoulder): ?float
//     {
//         if (!$this->checkKeypointConfidence([$leftShoulder, $rightShoulder])) {
//             return null;
//         }

//         return $this->calculateDistance(
//             ['x' => $leftShoulder['x'], 'y' => $leftShoulder['y']],
//             ['x' => $rightShoulder['x'], 'y' => $rightShoulder['y']]
//         );
//     }

//     public function calculateChestLength(array $leftShoulder, array $rightShoulder, array $leftHip, array $rightHip): ?float
//     {
//         if (!$this->checkKeypointConfidence([$leftShoulder, $rightShoulder, $leftHip, $rightHip])) {
//             return null;
//         }

//         $shoulderMidY = ($leftShoulder['y'] + $rightShoulder['y']) / 2;
//         $hipMidY = ($leftHip['y'] + $rightHip['y']) / 2;

//         return abs($shoulderMidY - $hipMidY);
//     }

//     public function calculateSleeveLength(array $shoulder, array $elbow, array $wrist): ?float
//     {
//         if (!$this->checkKeypointConfidence([$shoulder, $elbow, $wrist])) {
//             return null;
//         }

//         $shoulderToElbow = $this->calculateDistance(
//             ['x' => $shoulder['x'], 'y' => $shoulder['y']],
//             ['x' => $elbow['x'], 'y' => $elbow['y']]
//         );

//         $elbowToWrist = $this->calculateDistance(
//             ['x' => $elbow['x'], 'y' => $elbow['y']],
//             ['x' => $wrist['x'], 'y' => $wrist['y']]
//         );

//         return $shoulderToElbow + $elbowToWrist;
//     }

//     public function calculateShirtLength(array $leftShoulder, array $rightShoulder, array $leftHip, array $rightHip): ?float
//     {
//         if (!$this->checkKeypointConfidence([$leftShoulder, $rightShoulder, $leftHip, $rightHip])) {
//             return null;
//         }

//         $neckX = ($leftShoulder['x'] + $rightShoulder['x']) / 2;
//         $neckY = ($leftShoulder['y'] + $rightShoulder['y']) / 2;

//         $midHipX = ($leftHip['x'] + $rightHip['x']) / 2;
//         $midHipY = ($leftHip['y'] + $rightHip['y']) / 2;

//         return $this->calculateDistance(
//             ['x' => $neckX, 'y' => $neckY],
//             ['x' => $midHipX, 'y' => $midHipY]
//         );
//     }

//     private function calculateDistance(array $point1, array $point2): float
//     {
//         return sqrt(pow($point2['x'] - $point1['x'], 2) + pow($point2['y'] - $point1['y'], 2));
//     }

//     private function checkKeypointConfidence(array $keypoints): bool
//     {
//         foreach ($keypoints as $keypoint) {
//             if (!isset($keypoint['score']) || $keypoint['score'] < self::CONFIDENCE_THRESHOLD) {
//                 return false;
//             }
//         }
//         return true;
//     }

//     public function calculatePixelToCentimeterRatio(float $measuredChestWidth, float $pixelChestWidth): float
//     {
//         return $measuredChestWidth / $pixelChestWidth;
//     }
// }



// Correct with height only without weigth
// namespace App\Services;

// class MeasurementService
// {
//     private const CONFIDENCE_THRESHOLD = 0.5;

//     private function calculatePixelDistance(array $point1, array $point2): float
//     {
//         return sqrt(pow($point2['x'] - $point1['x'], 2) + pow($point2['y'] - $point1['y'], 2));
//     }

//     private function checkKeypointConfidence(array $keypoints): bool
//     {
//         foreach ($keypoints as $keypoint) {
//             if (empty($keypoint['score']) || $keypoint['score'] < self::CONFIDENCE_THRESHOLD) {
//                 return false;
//             }
//         }
//         return true;
//     }

//     public function calculateMeasurements(array $data, float $heightCm): ?array
//     {
//         // if (!$this->checkKeypointConfidence($data)) {
//         //     return null;
//         // }

//         $nose = $data['nose'];
//         $leftShoulder = $data['leftShoulder'];
//         $rightShoulder = $data['rightShoulder'];
//         $leftElbow = $data['leftElbow'];
//         $rightElbow = $data['rightElbow'];
//         $leftWrist = $data['leftWrist'];
//         $rightWrist = $data['rightWrist'];
//         $leftHip = $data['leftHip'];
//         $rightHip = $data['rightHip'];
//         $leftAnkle = $data['leftAnkle'];
//         $rightAnkle = $data['rightAnkle'];

//         // More stable body height estimation
//         $midAnkle = [
//             'x' => ($leftAnkle['x'] + $rightAnkle['x']) / 2,
//             'y' => ($leftAnkle['y'] + $rightAnkle['y']) / 2,
//         ];

//         $noseToFootPixels = $this->calculatePixelDistance($nose, $midAnkle);
//         $pixelToCmRatio = $heightCm / $noseToFootPixels;

//         // Shoulder Width
//         $shoulderWidth = $this->calculatePixelDistance($leftShoulder, $rightShoulder) * $pixelToCmRatio;

//         // Sleeve Length (Left Arm)
//         $shoulderToElbow = $this->calculatePixelDistance($leftShoulder, $leftElbow);
//         $elbowToWrist = $this->calculatePixelDistance($leftElbow, $leftWrist);
//         $sleeveLength = ($shoulderToElbow + $elbowToWrist) * $pixelToCmRatio;

//         // Shirt Length (Nose to Mid Hip)
//         $midHip = [
//             'x' => ($leftHip['x'] + $rightHip['x']) / 2,
//             'y' => ($leftHip['y'] + $rightHip['y']) / 2,
//         ];
//         $shirtLength = $this->calculatePixelDistance($nose, $midHip) * $pixelToCmRatio;

//         // Body height
//         $bodyHeight = $noseToFootPixels * $pixelToCmRatio;

//         return [
//             'body_height' => round($bodyHeight, 2),
//             'shoulder_width' => round($shoulderWidth, 2),
//             'sleeve_length' => round($sleeveLength, 2),
//             'shirt_length' => round($shirtLength, 2),
//         ];
//     }
// }

namespace App\Services;

class MeasurementService
{

    private function calculatePixelDistance(array $point1, array $point2): float
    {
        return sqrt(pow($point2['x'] - $point1['x'], 2) + pow($point2['y'] - $point1['y'], 2));
    }


    public function calculateMeasurements(array $data, float $heightCm, float $weightKg): ?array
    {
        $nose = $data['nose'];
        $leftShoulder = $data['leftShoulder'];
        $rightShoulder = $data['rightShoulder'];
        $leftElbow = $data['leftElbow'];
        $rightElbow = $data['rightElbow'];
        $leftWrist = $data['leftWrist'];
        $rightWrist = $data['rightWrist'];
        $leftHip = $data['leftHip'];
        $rightHip = $data['rightHip'];
        $leftKnee = $data['leftKnee'];
        $rightKnee = $data['rightKnee'];
        $leftAnkle = $data['leftAnkle'];
        $rightAnkle = $data['rightAnkle'];

        $midAnkle = [
            'x' => ($leftAnkle['x'] + $rightAnkle['x']) / 2,
            'y' => ($leftAnkle['y'] + $rightAnkle['y']) / 2,
        ];

        $noseToFootPixels = $this->calculatePixelDistance($nose, $midAnkle);
        $pixelToCmRatio = $heightCm / $noseToFootPixels;

        $estimatedShoulderWidth = 0.23 * $heightCm + 0.10 * $weightKg;

        $shoulderPixelWidth = $this->calculatePixelDistance($leftShoulder, $rightShoulder);
        $shoulderWidth = $shoulderPixelWidth * $pixelToCmRatio;

        $shoulderWidth *= ($estimatedShoulderWidth / $shoulderWidth);
        $shoulderToElbow = $this->calculatePixelDistance($leftShoulder, $leftElbow);
        $elbowToWrist = $this->calculatePixelDistance($leftElbow, $leftWrist);
        $sleeveLength = ($shoulderToElbow + $elbowToWrist) * $pixelToCmRatio;

        $midHip = [
            'x' => ($leftHip['x'] + $rightHip['x']) / 2,
            'y' => ($leftHip['y'] + $rightHip['y']) / 2,
        ];
        $shirtLength = $this->calculatePixelDistance($nose, $midHip) * $pixelToCmRatio;

        // Trousers
        $waistWidth = $this->calculatePixelDistance($leftHip, $rightHip) * $pixelToCmRatio;

        $hipWidth = $waistWidth * 1.1;

        $outseamLength = $this->calculatePixelDistance($midHip, $midAnkle) * $pixelToCmRatio;

        $crotch = [
            'x' => ($leftHip['x'] + $rightHip['x']) / 2,
            'y' => ($leftHip['y'] + $rightHip['y']) / 2 + ($leftKnee['y'] - $leftHip['y']) / 2,
        ];
        $inseamLength = $this->calculatePixelDistance($crotch, $midAnkle) * $pixelToCmRatio;

        $midThighLeft = [
            'x' => $leftHip['x'],
            'y' => ($leftHip['y'] + $leftKnee['y']) / 2,
        ];
        $midThighRight = [
            'x' => $rightHip['x'],
            'y' => ($rightHip['y'] + $rightKnee['y']) / 2,
        ];
        $thighWidth = $this->calculatePixelDistance($midThighLeft, $midThighRight) * $pixelToCmRatio;

        $bodyHeight = $noseToFootPixels * $pixelToCmRatio;

        return [
            'body_height' => round($bodyHeight, 2),
            'shoulder_width' => round($shoulderWidth, 2),
            'sleeve_length' => round($sleeveLength, 2),
            'shirt_length' => round($shirtLength, 2),
            'waist_width' => round($waistWidth, 2),
            'hip_width' => round($hipWidth, 2),
            'outseam_length' => round($outseamLength, 2),
            'inseam_length' => round($inseamLength, 2),
            'thigh_width' => round($thighWidth, 2),
        ];
    }
}
