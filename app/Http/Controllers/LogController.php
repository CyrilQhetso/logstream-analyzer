<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function upload(Request $request)
    {
        $mode = $request->input('input_mode', 'file');

        if ($mode === 'paste') {
            $request->validate([
                'log_paste' => 'required|string|max:524288',
            ]);

            $rawText  = $request->input('log_paste');
            $lines    = preg_split('/\r?\n/', $rawText);
            $filename = 'pasted_log_' . date('YmdHis') . '.txt';

            $result = $this->parseLines($lines);

        } else {
            $request->validate([
                'log_file' => [
                    'required',
                    'file',
                    'max:20480', // 20 MB
                    function ($attribute, $value, $fail) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        if (!in_array($ext, ['log', 'txt', 'out', 'err'])) {
                            $fail('Only .log, .txt, .out, or .err files are accepted.');
                        }
                    },
                ],
            ]);

            $file     = $request->file('log_file');
            $filename = $file->getClientOriginalName();
            $handle   = fopen($file->getRealPath(), 'r');

            if ($handle === false) {
                return back()->withErrors(['log_file' => 'Could not read the uploaded file.']);
            }

            $rawLines = [];
            while (($line = fgets($handle)) !== false) {
                $rawLines[] = trim($line);
            }
            fclose($handle);

            $result = $this->parseLines($rawLines);
        }

        session()->flash('log_data', array_merge($result, ['filename' => $filename, 'mode' => $mode]));

        return redirect()->route('dashboard');
    }

    /**
     * Parse an array of log lines using a multi-format regex engine.
     * Detects: Laravel, Apache/Nginx, Python/Django, Node/Winston,
     *          Syslog, Rails, Docker/systemd, and a generic fallback.
     *
     * @param  string[] $lines
     * @return array
     */
    private function parseLines(array $lines): array
    {
        $severityMap = [
            'EMERG'     => 'EMERGENCY',
            'EMERGENCY' => 'EMERGENCY',
            'ALERT'     => 'ALERT',
            'CRIT'      => 'CRITICAL',
            'CRITICAL'  => 'CRITICAL',
            'FATAL'     => 'CRITICAL',
            'ERR'       => 'ERROR',
            'ERROR'     => 'ERROR',
            'SEVERE'    => 'ERROR',
            'WARN'      => 'WARNING',
            'WARNING'   => 'WARNING',
            'NOTICE'    => 'NOTICE',
            'INFO'      => 'INFO',
            'INFORMATION' => 'INFO',
            'VERBOSE'   => 'INFO',
            'LOG'       => 'INFO',
            'HTTP'      => 'INFO',
            'DEBUG'     => 'DEBUG',
            'TRACE'     => 'DEBUG',
            'SILLY'     => 'DEBUG',
        ];

        $patterns = [

            'laravel' => [
                'regex'   => '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})?)\]\s+\w+\.(EMERGENCY|ALERT|CRITICAL|FATAL|ERROR|WARNING|WARN|NOTICE|INFO|DEBUG):\s*(.+)$/i',
                'groups'  => [1, 2, 3],
            ],


            'apache' => [
                'regex'   => '/^\[([^\]]+)\]\s+\[(emerg|alert|crit|error|warn|notice|info|debug)\](?:\s+\[pid\s+\d+\])?\s+(.+)$/i',
                'groups'  => [1, 2, 3],
            ],

            'apache_access' => [
                'regex'   => '/^\S+\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"[^"]*"\s+(\d{3})\s+/i',
                'groups'  => [1, 2, 3],  // group 3 unused — derived from status code
                'transform' => function (array $m) {
                    $status = (int) $m[2];
                    if ($status >= 500)      $sev = 'ERROR';
                    elseif ($status >= 400)  $sev = 'WARNING';
                    elseif ($status >= 300)  $sev = 'NOTICE';
                    else                     $sev = 'INFO';
                    return [$m[1], $sev, "HTTP {$m[2]} — {$m[0]}"];
                },
            ],

            'python' => [
                'regex'   => '/^(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[,.]?\d*)\s+(CRITICAL|FATAL|ERROR|WARN(?:ING)?|INFO|DEBUG|NOTICE|TRACE)\s+(?:[\w\.]+\s+)?(?:\[[\w\.]+\]\s+)?(.+)$/i',
                'groups'  => [1, 2, 3],
            ],

            'node_bracket' => [
                'regex'   => '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[.\d]*Z?)\s+\[(ERROR|WARN(?:ING)?|INFO|DEBUG|VERBOSE|HTTP|SILLY|TRACE)\](?::\s*)(.+)$/i',
                'groups'  => [1, 2, 3],
            ],

            'node_json' => [
                'regex'   => '/^\{.*?"level"\s*:\s*"(error|warn(?:ing)?|info|debug|verbose|http|silly|trace|fatal|critical)".*?"message"\s*:\s*"([^"]*)".*?"(?:timestamp|time)"\s*:\s*"([^"]*)".*?\}$/i',
                'groups'  => [3, 1, 2],
            ],


            'syslog_bsd' => [
                'regex'   => '/^([A-Z][a-z]{2}\s+\d{1,2}\s+\d{2}:\d{2}:\d{2})\s+\S+\s+\S+(?:\[\d+\])?:\s+(.+)$/i',
                'groups'  => [1, null, 2], // severity derived from content
                'transform' => function (array $m) use ($severityMap) {
                    // Try to detect severity keyword in the message
                    if (preg_match('/\b(emerg|alert|crit(?:ical)?|error|err|warn(?:ing)?|notice|info|debug)\b/i', $m[2], $sm)) {
                        $sev = $severityMap[strtoupper($sm[1])] ?? 'INFO';
                    } else {
                        $sev = 'INFO';
                    }
                    return [$m[1], $sev, $m[2]];
                },
            ],

            'rails' => [
                'regex'   => '/^([DIWEF]),\s*\[([^\]]+)\]\s+(DEBUG|INFO|WARN|ERROR|FATAL)\s*--\s*:\s+(.+)$/i',
                'groups'  => [2, 3, 4],
            ],

            'docker' => [
                'regex'   => '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z)\s+\S+\s+(.+)$/i',
                'groups'  => [1, null, 2],
                'transform' => function (array $m) use ($severityMap) {
                    if (preg_match('/\b(error|err|warn(?:ing)?|info|debug|fatal|critical|notice)\b/i', $m[2], $sm)) {
                        $sev = $severityMap[strtoupper($sm[1])] ?? 'INFO';
                    } else {
                        $sev = 'INFO';
                    }
                    return [$m[1], $sev, $m[2]];
                },
            ],

            'generic' => [
                'regex'   => '/^(.*?)\b(EMERGENCY|EMERG|ALERT|CRITICAL|CRIT|FATAL|SEVERE|ERROR|ERR|WARNING|WARN|NOTICE|INFORMATION|INFO|VERBOSE|LOG|HTTP|DEBUG|TRACE|SILLY)\b[:\s|]+(.+)$/i',
                'groups'  => [1, 2, 3],
            ],
        ];

        $severityCounts = [];
        $recentLines    = [];
        $skippedLines = 0;
        $totalLines     = 0;
        $matchedLines   = 0;
        $detectedFormats = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Skip extremely long lines
            if (strlen($line) > 10000) {
                $skippedLines++;
                continue;
            }

            $totalLines++;
            $matched = false;

            foreach ($patterns as $formatName => $spec) {
                if (!preg_match($spec['regex'], $line, $m)) continue;

                // Either use the transform callback or extract by group indices
                if (isset($spec['transform'])) {
                    [$timestamp, $rawSev, $message] = ($spec['transform'])($m);
                } else {
                    [$ti, $si, $mi] = $spec['groups'];
                    $timestamp = $ti !== null ? ($m[$ti] ?? '—') : '—';
                    $rawSev    = $si !== null ? ($m[$si] ?? 'INFO') : 'INFO';
                    $message   = $mi !== null ? ($m[$mi] ?? $line) : $line;
                }

                $severity = $severityMap[strtoupper(trim($rawSev))] ?? 'INFO';

                $severityCounts[$severity] = ($severityCounts[$severity] ?? 0) + 1;
                $detectedFormats[$formatName] = ($detectedFormats[$formatName] ?? 0) + 1;

                $recentLines[] = [
                    'timestamp' => $timestamp,
                    'severity'  => $severity,
                    'message'   => mb_strimwidth(trim($message), 0, 160, '…'),
                    'format'    => $formatName,
                ];

                if (count($recentLines) > 100) array_shift($recentLines);

                $matchedLines++;
                $matched = true;
                break; // first matching pattern wins
            }
        }

        $order = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];
        uksort($severityCounts, fn($a, $b) => array_search($a, $order) <=> array_search($b, $order));


        arsort($detectedFormats);
        $topFormat = array_key_first($detectedFormats) ?? 'unknown';

        return [
            'severityCounts'  => $severityCounts,
            'recentLines'     => $recentLines,
            'totalLines'      => $totalLines,
            'matchedLines'    => $matchedLines,
            'detectedFormats' => $detectedFormats,
            'topFormat'       => $topFormat,
            'skippedLines'    => $skippedLines,
        ];
    }
}