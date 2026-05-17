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
    private const WIDTH         = 640;
    private const HEIGHT        = 360;
    private const FPS           = 10;
    private const TARGET_FRAMES = 150;
    private const TRAIL_PTS     = 40;   // recent path points shown behind jeep
    private const ZOOM          = 16;   // close birds-eye zoom
    private const CONCURRENCY   = 10;
    private const MAPBOX_STYLE  = 'mapbox/dark-v11';
    private const VIDEO_VERSION = 2;    // bump to bust cached videos

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

        // Build one Guzzle request per frame — camera follows the jeep
        $client   = new Client(['timeout' => 20]);
        $requests = function () use ($frames, $token) {
            $prevBearing = 0;
            foreach ($frames as $i => $pt) {
                $lon = round($pt['longitude'], 6);
                $lat = round($pt['latitude'],  6);

                // Bearing: direction of travel from previous frame
                if ($i > 0) {
                    $prevBearing = $this->calcBearing($frames[$i - 1], $pt);
                }
                $bearing = round($prevBearing, 1);

                // Trail: last TRAIL_PTS traveled points for the path behind the jeep
                $trailStart = max(0, $i - self::TRAIL_PTS);
                $trail      = array_slice($frames, $trailStart, $i - $trailStart + 1);
                $poly       = rawurlencode($this->encodePolyline($trail));

                // Camera: centered on jeep, birds-eye zoom, rotated to direction of travel
                $camera  = "{$lon},{$lat}," . self::ZOOM . ",{$bearing},0";
                $overlay = "path-4+0A84FF-0.9({$poly}),pin-s+FF453A({$lon},{$lat})";

                $url = sprintf(
                    'https://api.mapbox.com/styles/v1/%s/static/%s/%s/%dx%d@2x?access_token=%s&logo=false&attribution=false',
                    self::MAPBOX_STYLE,
                    $overlay,
                    $camera,
                    self::WIDTH, self::HEIGHT,
                    $token,
                );

                yield $i => new Request('GET', $url);
            }
        };

        $saved = [];
        $pool  = new Pool($client, $requests(), [
            'concurrency' => self::CONCURRENCY,
            'fulfilled'   => function ($response, int $i) use ($tmpDir, &$saved) {
                $file = sprintf('%s/frame_%05d.png', $tmpDir, $i);
                file_put_contents($file, $response->getBody()->getContents());
                $saved[$i] = $file;
            },
            'rejected'    => function ($reason, int $i) {
                // frame skipped on error — FFmpeg concat will still work
            },
        ]);
        $pool->promise()->wait();

        if (count($saved) < 2) {
            $this->cleanup($tmpDir);
            return response()->json(['error' => 'Failed to fetch enough frames from Mapbox.'], 500);
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
            '%s -y -r %d -f concat -safe 0 -i %s -c:v libx264 -pix_fmt yuv420p -crf 23 -movflags +faststart %s 2>&1',
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
