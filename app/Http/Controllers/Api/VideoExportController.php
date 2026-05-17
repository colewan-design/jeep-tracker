<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Storage;

class VideoExportController extends Controller
{
    private const WIDTH         = 720;
    private const HEIGHT        = 1280; // portrait (matches phone screen)
    private const FPS           = 30;
    private const TARGET_FRAMES = 300;
    private const TRAIL_PTS     = 60;   // traveled path behind jeep
    private const AHEAD_PTS     = 999;  // remaining path ahead — show all
    private const ZOOM          = 17;   // same as playback
    private const CONCURRENCY   = 10;
    private const MAPBOX_STYLE  = 'teekunana/cmp9b3i4r006p01sn0xyqhyre'; // custom style
    private const VIDEO_VERSION = 3;    // bump to bust cached videos

    public function export(Trip $trip)
    {
        $points = $trip->route_points ?? [];

        if (count($points) < 2) {
            return response()->json(['error' => 'Not enough GPS data for this trip.'], 422);
        }

        // Versioned cache key — bump VIDEO_VERSION to regenerate all
        $videoPath = "videos/trip_{$trip->id}_v" . self::VIDEO_VERSION . ".mp4";
        if (Storage::disk('public')->exists($videoPath)) {
            return response()->json(['url' => Storage::disk('public')->url($videoPath)]);
        }

        $token  = env('MAPBOX_TOKEN');
        $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');

        // Sample route to TARGET_FRAMES evenly-spaced frames
        $frames = $this->sample($points, self::TARGET_FRAMES);

        // Temp directory for PNG frames
        $tmpDir = storage_path('app/tmp/video_' . $trip->id . '_' . time());
        mkdir($tmpDir, 0755, true);

        // Build one Guzzle request per frame — matches the in-app playback look
        $client   = new Client(['timeout' => 20]);
        $requests = function () use ($frames, $token) {
            $total = count($frames);
            foreach ($frames as $i => $pt) {
                $lon = round($pt['longitude'], 6);
                $lat = round($pt['latitude'],  6);

                // Traveled path behind jeep — gray (#3A3A3C), width 6
                $trailStart   = max(0, $i - self::TRAIL_PTS);
                $trail        = array_slice($frames, $trailStart, $i - $trailStart + 1);
                $trailPoly    = rawurlencode($this->encodePolyline($trail));

                // Remaining path ahead — cyan (#00C8FF), width 8
                $aheadEnd     = min($total - 1, $i + self::AHEAD_PTS);
                $ahead        = array_slice($frames, $i, $aheadEnd - $i + 1);
                $aheadPoly    = rawurlencode($this->encodePolyline($ahead));

                // Flat north-up camera, jeep centered (pitch=0, bearing=0 — same as playback)
                $camera  = "{$lon},{$lat}," . self::ZOOM . ",0,0";

                // White arrow pin at jeep position to match the on-screen arrow
                $overlay = "path-6+3A3A3C-0.85({$trailPoly}),path-8+00C8FF({$aheadPoly}),pin-s+FFFFFF({$lon},{$lat})";

                $url = sprintf(
                    'https://api.mapbox.com/styles/v1/%s/static/%s/%s/%dx%d?access_token=%s&logo=false&attribution=false',
                    self::MAPBOX_STYLE,
                    $overlay,
                    $camera,
                    self::WIDTH, self::HEIGHT,
                    $token,
                );

                yield $i => new Request('GET', $url);
            }
        };

        $saved  = [];
        $errors = [];
        $pool   = new Pool($client, $requests(), [
            'concurrency' => self::CONCURRENCY,
            'fulfilled'   => function ($response, int $i) use ($tmpDir, &$saved, &$errors) {
                $body = $response->getBody()->getContents();
                // Mapbox returns JSON error bodies with 4xx status
                if ($response->getStatusCode() >= 400) {
                    $errors[$i] = 'HTTP ' . $response->getStatusCode() . ': ' . substr($body, 0, 200);
                    return;
                }
                $file = sprintf('%s/frame_%05d.png', $tmpDir, $i);
                file_put_contents($file, $body);
                $saved[$i] = $file;
            },
            'rejected'    => function ($reason, int $i) use (&$errors) {
                $errors[$i] = $reason->getMessage();
            },
        ]);
        $pool->promise()->wait();

        if (count($saved) < 2) {
            $this->cleanup($tmpDir);
            $sample = array_slice($errors, 0, 3, true);
            return response()->json([
                'error'  => 'Failed to fetch enough frames from Mapbox.',
                'frames_ok'   => count($saved),
                'frames_fail' => count($errors),
                'sample_errors' => $sample,
                'token_set' => !empty(env('MAPBOX_TOKEN')),
            ], 500);
        }

        ksort($saved);

        // Write concat list for FFmpeg
        $listFile = $tmpDir . '/frames.txt';
        file_put_contents(
            $listFile,
            implode("\n", array_map(fn($f) => "file '" . str_replace('\\', '/', $f) . "'", $saved)),
        );

        // Encode to MP4
        Storage::disk('public')->makeDirectory('videos');
        $outPath = storage_path("app/public/{$videoPath}");
        $cmd     = sprintf(
            '%s -y -r %d -f concat -safe 0 -i %s -c:v libx264 -pix_fmt yuv420p -crf 20 -preset fast -movflags +faststart %s 2>&1',
            escapeshellarg($ffmpeg),
            self::FPS,
            escapeshellarg($listFile),
            escapeshellarg($outPath),
        );
        exec($cmd, $output, $exitCode);

        $this->cleanup($tmpDir);

        if ($exitCode !== 0 || !file_exists($outPath)) {
            return response()->json([
                'error'  => 'FFmpeg encoding failed.',
                'detail' => implode("\n", array_slice($output, -10)),
            ], 500);
        }

        return response()->json(['url' => Storage::disk('public')->url($videoPath)]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function sample(array $points, int $max): array
    {
        $n = count($points);
        if ($n <= $max) return $points;
        $out  = [];
        $step = ($n - 1) / ($max - 1);
        for ($i = 0; $i < $max; $i++) {
            $out[] = $points[(int) round($i * $step)];
        }
        return $out;
    }

    private function calcBearing(array $from, array $to): float
    {
        $lat1 = deg2rad($from['latitude']);
        $lat2 = deg2rad($to['latitude']);
        $dLon = deg2rad($to['longitude'] - $from['longitude']);
        $y    = sin($dLon) * cos($lat2);
        $x    = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
        return (rad2deg(atan2($y, $x)) + 360) % 360;
    }

    private function encodePolyline(array $points): string
    {
        $out     = '';
        $prevLat = 0;
        $prevLon = 0;
        foreach ($points as $p) {
            $lat = (int) round($p['latitude']  * 1e5);
            $lon = (int) round($p['longitude'] * 1e5);
            $out .= $this->encodeChunk($lat - $prevLat);
            $out .= $this->encodeChunk($lon - $prevLon);
            $prevLat = $lat;
            $prevLon = $lon;
        }
        return $out;
    }

    private function encodeChunk(int $v): string
    {
        $v   = $v < 0 ? ~($v << 1) : ($v << 1);
        $out = '';
        while ($v >= 0x20) {
            $out .= chr((0x20 | ($v & 0x1f)) + 63);
            $v >>= 5;
        }
        return $out . chr($v + 63);
    }

    private function cleanup(string $dir): void
    {
        foreach (glob($dir . '/*.png') as $f) @unlink($f);
        @unlink($dir . '/frames.txt');
        @rmdir($dir);
    }
}
