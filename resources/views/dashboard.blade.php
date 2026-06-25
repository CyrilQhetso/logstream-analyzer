<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LogStream — Universal Log Analyzer</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>

    <style>
        :root {
            --amber:      #E07B39;
            --amber-dim:  #C4652A;
            --amber-glow: rgba(224,123,57,0.15);
            --bg:         #111110;
            --bg-surface: #1C1C1A;
            --bg-card:    #202020;
            --border:     #2A2A27;
            --text:       #F0EDE8;
            --muted:      #7A7A74;
            --dim:        #3E3E3A;
            --shadow:     0 4px 28px rgba(0,0,0,0.55);
        }
        [data-theme="light"] {
            --bg:         #F5F1EB;
            --bg-surface: #ECE8E0;
            --bg-card:    #FFFFFF;
            --border:     #D5CEC4;
            --text:       #1A1A18;
            --muted:      #6B6760;
            --dim:        #B0A89E;
            --shadow:     0 4px 24px rgba(0,0,0,0.08);
        }

        *,*::before,*::after{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            background:var(--bg);color:var(--text);
            font-family:'Inter',system-ui,sans-serif;
            font-size:15px;line-height:1.6;min-height:100vh;
            transition:background .25s,color .25s;
        }
        .mono{font-family:'JetBrains Mono',monospace}

        .shell{max-width:1120px;margin:0 auto;padding:0 1.5rem 5rem}

        .hdr{display:flex;align-items:center;justify-content:space-between;
             padding:1.75rem 0 1.5rem;border-bottom:1px solid var(--border);margin-bottom:2.75rem}
        .logo{display:flex;align-items:center;gap:.625rem}
        .logo-mark{width:32px;height:32px;background:var(--amber);border-radius:7px;
                   display:flex;align-items:center;justify-content:center}
        .logo-text{font-family:'JetBrains Mono',monospace;font-size:1rem;font-weight:600;
                   letter-spacing:-.02em;color:var(--text)}
        .logo-text span{color:var(--amber)}
        .hdr-r{display:flex;align-items:center;gap:.875rem}
        .badge{font-family:'JetBrains Mono',monospace;font-size:.63rem;font-weight:500;
               letter-spacing:.08em;text-transform:uppercase;padding:.22rem .55rem;
               border-radius:999px;border:1px solid var(--border);color:var(--muted)}

        .tog{width:42px;height:24px;background:var(--bg-surface);border:1px solid var(--border);
             border-radius:999px;cursor:pointer;position:relative;transition:background .2s}
        .tog::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;
                    border-radius:50%;background:var(--muted);transition:transform .2s,background .2s}
        [data-theme="light"] .tog::after{transform:translateX(18px);background:var(--amber)}

        .hero{margin-bottom:2.25rem}
        .eyebrow{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:500;
                 letter-spacing:.1em;text-transform:uppercase;color:var(--amber);margin-bottom:.65rem}
        .hero h1{font-size:clamp(1.7rem,4vw,2.4rem);font-weight:600;letter-spacing:-.03em;
                 line-height:1.15;margin-bottom:.75rem}
        .hero p{font-size:.9rem;color:var(--muted);max-width:540px;line-height:1.75}
        .blink{display:inline-block;width:8px;height:1em;background:var(--amber);
               border-radius:2px;animation:blink 1.1s step-end infinite;vertical-align:-.05em;margin-left:3px}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:0}}

        .formats-strip{display:flex;align-items:center;flex-wrap:wrap;gap:.5rem;
                       margin-bottom:2rem;padding:.875rem 1.125rem;
                       background:var(--bg-surface);border:1px solid var(--border);border-radius:10px}
        .formats-label{font-family:'JetBrains Mono',monospace;font-size:.63rem;letter-spacing:.08em;
                       text-transform:uppercase;color:var(--muted);margin-right:.25rem;white-space:nowrap}
        .fmt-tag{font-family:'JetBrains Mono',monospace;font-size:.65rem;padding:.18rem .5rem;
                 border-radius:5px;background:var(--bg-card);border:1px solid var(--border);color:var(--muted)}

        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow)}

        .input-card{padding:0;margin-bottom:2.25rem;overflow:hidden}
        .tabs{display:flex;border-bottom:1px solid var(--border)}
        .tab-btn{
            flex:1;padding:.875rem 1rem;font-family:'JetBrains Mono',monospace;font-size:.75rem;
            font-weight:600;letter-spacing:.06em;text-transform:uppercase;
            background:none;border:none;cursor:pointer;color:var(--muted);
            border-bottom:2px solid transparent;margin-bottom:-1px;
            transition:color .15s,border-color .15s;display:flex;align-items:center;justify-content:center;gap:.5rem
        }
        .tab-btn.active{color:var(--amber);border-bottom-color:var(--amber)}
        .tab-btn:hover:not(.active){color:var(--text)}
        .tab-panel{display:none;padding:1.75rem}
        .tab-panel.active{display:block}

        .drop-zone{border:2px dashed var(--border);border-radius:10px;padding:2.25rem 1.5rem;
                   text-align:center;cursor:pointer;transition:border-color .2s,background .2s;position:relative}
        .drop-zone:hover,.drop-zone.over{border-color:var(--amber);background:var(--amber-glow)}
        .drop-zone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
        .up-icon{width:44px;height:44px;background:var(--amber-glow);border-radius:11px;
                 display:flex;align-items:center;justify-content:center;margin:0 auto .875rem;transition:background .2s}
        .drop-zone:hover .up-icon{background:rgba(224,123,57,.28)}
        .up-icon svg{width:20px;height:20px;color:var(--amber)}
        .drop-zone h3{font-size:.9rem;font-weight:600;margin-bottom:.25rem}
        .drop-zone p{font-size:.78rem;color:var(--muted)}
        .fname{font-family:'JetBrains Mono',monospace;font-size:.75rem;color:var(--amber);margin-top:.5rem;min-height:1.1em}

        .paste-label{display:block;font-family:'JetBrains Mono',monospace;font-size:.65rem;
                     letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:.625rem}
        .paste-area{
            width:100%;min-height:200px;padding:1rem 1.125rem;
            background:var(--bg-surface);border:1.5px solid var(--border);border-radius:10px;
            color:var(--text);font-family:'JetBrains Mono',monospace;font-size:.78rem;
            line-height:1.65;resize:vertical;
            transition:border-color .15s;outline:none
        }
        .paste-area:focus{border-color:var(--amber)}
        .paste-area::placeholder{color:var(--dim)}
        .paste-footer{display:flex;align-items:center;justify-content:space-between;
                      margin-top:.875rem;flex-wrap:wrap;gap:.75rem}
        .paste-hint{font-size:.75rem;color:var(--dim);font-family:'JetBrains Mono',monospace}
        .char-count{font-size:.72rem;color:var(--dim);font-family:'JetBrains Mono',monospace}

        .up-footer{display:flex;align-items:center;justify-content:space-between;
                   margin-top:1.125rem;flex-wrap:wrap;gap:.75rem}
        .up-hint{font-size:.75rem;color:var(--dim);font-family:'JetBrains Mono',monospace}

        .btn{display:inline-flex;align-items:center;gap:.45rem;background:var(--amber);color:#fff;
             font-weight:600;font-size:.85rem;padding:.6rem 1.4rem;border-radius:8px;border:none;
             cursor:pointer;transition:background .15s,transform .1s;letter-spacing:.01em}
        .btn:hover{background:var(--amber-dim)}
        .btn:active{transform:scale(.98)}
        .btn:disabled{opacity:.4;cursor:not-allowed;transform:none}
        .btn-secondary{
            background:transparent;
            border:1px solid var(--border);
            color:var(--text);
        }

        .btn-secondary:hover{
            background:var(--bg-surface);
        }

        .alert{border-radius:8px;padding:.8rem 1rem;font-size:.83rem;margin-bottom:1.5rem;
               border:1px solid;display:flex;gap:.6rem;align-items:flex-start}
        .alert-err{background:rgba(220,60,60,.08);border-color:rgba(220,60,60,.25);color:#e57373}

        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:1rem;margin-bottom:2rem}
        .sc{padding:1.2rem 1.4rem}
        .sc-label{font-family:'JetBrains Mono',monospace;font-size:.62rem;letter-spacing:.09em;
                  text-transform:uppercase;color:var(--muted);margin-bottom:.35rem}
        .sc-val{font-family:'JetBrains Mono',monospace;font-size:1.7rem;font-weight:600;line-height:1}
        .sc-val.accent{color:var(--amber)}
        .sc-sub{font-size:.72rem;color:var(--dim);margin-top:.25rem}
        .sc-val.sm{font-size:1rem;word-break:break-all}

        .fmt-detected{display:inline-flex;align-items:center;gap:.4rem;
                      font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:500;
                      letter-spacing:.06em;text-transform:uppercase;
                      padding:.25rem .65rem;border-radius:6px;
                      background:var(--amber-glow);border:1px solid rgba(224,123,57,.3);color:var(--amber)}

        .rg{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem}
        @media(max-width:720px){.rg{grid-template-columns:1fr}}
        .ch{padding:1.2rem 1.4rem 0;display:flex;align-items:center;justify-content:space-between}
        .ct{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:600;
            letter-spacing:.07em;text-transform:uppercase;color:var(--muted)}
        .cb{padding:1.2rem 1.4rem 1.4rem}
        .chart-wrap{position:relative;height:250px}

        .sev{display:inline-block;font-family:'JetBrains Mono',monospace;font-size:.6rem;
             font-weight:600;letter-spacing:.07em;text-transform:uppercase;padding:.18rem .45rem;border-radius:4px}
        .sEMERGENCY,.sALERT,.sCRITICAL{background:rgba(220,60,60,.15);color:#e57373}
        .sERROR{background:rgba(224,100,57,.15);color:#e07b39}
        .sWARNING{background:rgba(215,185,50,.15);color:#c9a93a}
        .sNOTICE,.sINFO{background:rgba(57,160,224,.15);color:#64aadc}
        .sDEBUG{background:rgba(130,130,130,.15);color:#8a8a85}

        .bl{list-style:none;padding:0;margin:0}
        .bl li{display:flex;align-items:center;gap:.7rem;padding:.55rem 0;border-bottom:1px solid var(--border)}
        .bl li:last-child{border-bottom:none}
        .bar-track{flex:1;height:4px;background:var(--bg-surface);border-radius:99px;overflow:hidden}
        .bar-fill{height:100%;border-radius:99px;transition:width .9s cubic-bezier(.16,1,.3,1)}
        .sev-n{font-family:'JetBrains Mono',monospace;font-size:.76rem;font-weight:600;
               color:var(--text);min-width:3rem;text-align:right}

        .tw{overflow-x:auto}
        .st{width:100%;border-collapse:collapse;font-family:'JetBrains Mono',monospace;font-size:.76rem}
        .st th{text-align:left;padding:.6rem .85rem;border-bottom:1px solid var(--border);
               font-size:.62rem;letter-spacing:.08em;text-transform:uppercase;color:var(--dim);white-space:nowrap}
        .st td{padding:.52rem .85rem;border-bottom:1px solid var(--border);vertical-align:top;color:var(--muted)}
        .st tr:last-child td{border-bottom:none}
        .st td.msg{color:var(--text);font-family:'Inter',sans-serif;font-size:.78rem;max-width:450px;word-break:break-word}
        .st tr:hover td{background:rgba(255,255,255,.02)}
        [data-theme="light"] .st tr:hover td{background:rgba(0,0,0,.02)}

        @keyframes si{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}
        .sr{animation:si .2s ease both}

        .filter-bar{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.75rem}
        .fbtn{font-family:'JetBrains Mono',monospace;font-size:.62rem;font-weight:600;
              letter-spacing:.06em;text-transform:uppercase;padding:.25rem .6rem;border-radius:5px;
              border:1px solid var(--border);background:none;cursor:pointer;color:var(--muted);
              transition:color .15s,background .15s,border-color .15s}
        .fbtn:hover{color:var(--text);border-color:var(--text)}
        .fbtn.on{background:var(--amber-glow);border-color:rgba(224,123,57,.4);color:var(--amber)}
        .filter-count{font-family:'JetBrains Mono',monospace;font-size:.68rem;color:var(--dim);margin-left:auto}

        .fmt-pill{font-family:'JetBrains Mono',monospace;font-size:.55rem;letter-spacing:.05em;
                  text-transform:uppercase;padding:.12rem .38rem;border-radius:3px;
                  background:var(--bg-surface);color:var(--dim);white-space:nowrap}

        .idle{text-align:center;padding:3.5rem 1rem}
        .idle-icon{width:60px;height:60px;background:var(--bg-surface);border-radius:14px;
                   display:flex;align-items:center;justify-content:center;margin:0 auto 1.1rem}
        .idle h2{font-size:1.05rem;font-weight:600;margin-bottom:.35rem}
        .idle p{font-size:.83rem;color:var(--muted);max-width:380px;margin:0 auto}

        .ft{border-top:1px solid var(--border);padding-top:1.4rem;margin-top:2.75rem;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem}
        .ft p{font-family:'JetBrains Mono',monospace;font-size:.68rem;color:var(--dim)}
        .ft a{color:var(--amber);text-decoration:none}

        @media(prefers-reduced-motion:reduce){.sr{animation:none}.bar-fill{transition:none}}
    </style>
</head>
<body>
<div class="shell">

    <header class="hdr">
        <div class="logo">
            <div class="logo-mark">
                <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 5l4 4-4 4M9 13h6" stroke="#fff" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="logo-text">Log<span>Stream</span></span>
        </div>
        <div class="hdr-r">
            <button class="tog" id="themeToggle" aria-label="Toggle theme"></button>
        </div>
    </header>

    @if($errors->any())
        <div class="alert alert-err" role="alert">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:2px">
                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 5v3.5M8 11h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        </div>
    @endif

    <section class="hero">
        <p class="eyebrow">Universal Log Analyzer</p>
        <h1>Parse any log.<br>Understand it instantly.</h1>
        <p>Upload a log file or paste terminal output directly. LogStream auto-detects the format and gives you a severity breakdown, format identification, and a filterable entry stream.</p>
    </section>

    <!-- Supported formats strip -->
    <div class="formats-strip">
        <span class="formats-label">Supports</span>
        <span class="fmt-tag">Laravel</span>
        <span class="fmt-tag">Apache / Nginx</span>
        <span class="fmt-tag">Python / Django</span>
        <span class="fmt-tag">Node.js / Winston</span>
        <span class="fmt-tag">Syslog</span>
        <span class="fmt-tag">Rails</span>
        <span class="fmt-tag">Docker</span>
        <span class="fmt-tag">Generic</span>
    </div>

    <div class="card input-card">
        <div class="tabs" role="tablist">
            <button class="tab-btn active" id="tab-file" role="tab" aria-selected="true" aria-controls="panel-file" onclick="switchTab('file')">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 2h6l3 3v7H2V2z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M8 2v3h3" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                Upload File
            </button>
            <button class="tab-btn" id="tab-paste" role="tab" aria-selected="false" aria-controls="panel-paste" onclick="switchTab('paste')">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1.5" y="3" width="11" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M5 3V2a2 2 0 014 0v1" stroke="currentColor" stroke-width="1.4"/><path d="M4 7h6M4 9.5h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                Paste Logs
            </button>
        </div>

        <div class="tab-panel active" id="panel-file" role="tabpanel">
            <form action="{{ route('log.upload') }}" method="POST" enctype="multipart/form-data" id="fileForm">
                @csrf
                <input type="hidden" name="input_mode" value="file">
                <div class="drop-zone" id="dropZone" tabindex="0" role="button" aria-label="Upload log file">
                    <input type="file" name="log_file" id="fileInput" accept=".log,.txt,.out,.err">
                    <div class="up-icon">
                        <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 13V4M10 4L6.5 7.5M10 4l3.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 14v1.5A1.5 1.5 0 004.5 17h11A1.5 1.5 0 0017 15.5V14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div id="uploadPlaceholder">
                        <h3>Drop your log file here</h3>
                        <p>Accepts .log · .txt · .out · .err — up to 20 MB</p>
                    </div>

                    <div id="uploadedState" style="display:none;">
                        <h3 style="color:var(--amber);"><i class="fa fa-check" aria-hidden="true"></i> File Ready</h3>
                        <p class="fname mono" id="fileNameDisplay"></p>
                    </div>
                </div>
                <div class="up-footer">
                    <button type="submit" class="btn" id="fileSubmit" disabled>
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Analyze File
                    </button>
                </div>
            </form>
        </div>

        <div class="tab-panel" id="panel-paste" role="tabpanel">
            <form action="{{ route('log.upload') }}" method="POST" id="pasteForm">
                @csrf
                <input type="hidden" name="input_mode" value="paste">
                <label class="paste-label" for="pasteArea">Paste log output</label>
                <textarea
                    class="paste-area"
                    name="log_paste"
                    id="pasteArea"
                    placeholder="Paste your terminal log output here..."
                    spellcheck="false"
                    autocomplete="off"
                ></textarea>
                <div class="paste-footer">
                    <span class="paste-hint">Ctrl+V / ⌘V to paste · Any log format accepted</span>
                    <span class="char-count" id="charCount">0 chars</span>
                    <button type="submit" class="btn" id="pasteSubmit" disabled>
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Analyze Paste
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('log_data'))
        @php
            $d       = session('log_data');
            $counts  = $d['severityCounts'];
            $lines   = $d['recentLines'];
            $total   = $d['totalLines'];
            $matched = $d['matchedLines'];
            $fname   = $d['filename'];
            $fmt     = $d['topFormat'] ?? 'unknown';
            $fmts    = $d['detectedFormats'] ?? [];
            $mode    = $d['mode'] ?? 'file';
            $maxC    = $counts ? max($counts) : 1;

            $sevColors = [
                'EMERGENCY' => '#e53935',
                'ALERT'     => '#e57373',
                'CRITICAL'  => '#ef6c00',
                'ERROR'     => '#E07B39',
                'WARNING'   => '#c9a93a',
                'NOTICE'    => '#64aadc',
                'INFO'      => '#4fc3f7',
                'DEBUG'     => '#8a8a85',
            ];

            $fmtLabels = [
                'laravel'      => 'Laravel',
                'apache'       => 'Apache/Nginx',
                'apache_access'=> 'Access Log',
                'python'       => 'Python',
                'node_bracket' => 'Node.js',
                'node_json'    => 'Node JSON',
                'syslog_bsd'   => 'Syslog',
                'rails'        => 'Rails',
                'docker'       => 'Docker',
                'generic'      => 'Generic',
            ];
        @endphp

        <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
            <form action="{{ route('log.reset') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    Analyze Another Log
                </button>
            </form>
        </div>
        @if(($d['skippedLines'] ?? 0) > 0)
            <div class="alert alert-err">
                {{ $d['skippedLines'] }} oversized line(s) were skipped.
            </div>
        @endif

        <div class="stats">
            <div class="card sc">
                <p class="sc-label">Source</p>
                <p class="sc-val sm mono">{{ $mode === 'paste' ? 'Pasted text' : $fname }}</p>
            </div>
            <div class="card sc">
                <p class="sc-label">Detected format</p>
                <p style="margin-top:.4rem">
                    <span class="fmt-detected">{{ $fmtLabels[$fmt] ?? ucfirst($fmt) }}</span>
                </p>
                @if(count($fmts) > 1)
                    <p class="sc-sub">+{{ count($fmts)-1 }} other format{{ count($fmts)>2?'s':'' }}</p>
                @endif
            </div>
            <div class="card sc">
                <p class="sc-label">Lines read</p>
                <p class="sc-val mono">{{ number_format($total) }}</p>
            </div>
            <div class="card sc">
                <p class="sc-label">Entries matched</p>
                <p class="sc-val accent mono">{{ number_format($matched) }}</p>
                <p class="sc-sub">{{ $total > 0 ? round($matched/$total*100) : 0 }}% match rate</p>
            </div>
        </div>

        <div class="rg">
            <div class="card">
                <div class="ch"><span class="ct">Severity Distribution</span></div>
                <div class="cb">
                    <div class="chart-wrap"><canvas id="sevChart"></canvas></div>
                </div>
            </div>
            <div class="card">
                <div class="ch"><span class="ct">Breakdown</span></div>
                <div class="cb">
                    <ul class="bl">
                        @foreach($counts as $sev => $cnt)
                            <li>
                                <span class="sev s{{ $sev }}">{{ $sev }}</span>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width:{{ round($cnt/$maxC*100) }}%;background:{{ $sevColors[$sev] ?? 'var(--amber)' }}"></div>
                                </div>
                                <span class="sev-n">{{ number_format($cnt) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        @if(count($lines) > 0)
            <div class="card" style="margin-bottom:2rem">
                <div class="ch" style="padding-bottom:1rem;flex-wrap:wrap;gap:.75rem">
                    <span class="ct">Log Stream</span>
                    <span class="badge">{{ count($lines) }} entries shown</span>
                </div>
                <div style="padding:0 1.4rem 1rem">
                    <div class="filter-bar" id="filterBar">
                        <button class="fbtn on" data-sev="ALL" onclick="filterStream('ALL',this)">All</button>
                        @foreach(array_keys($counts) as $sev)
                            <button class="fbtn" data-sev="{{ $sev }}" onclick="filterStream('{{ $sev }}',this)">{{ $sev }}</button>
                        @endforeach
                        <span class="filter-count" id="filterCount">{{ count($lines) }} shown</span>
                    </div>
                </div>
                <div class="tw">
                    <table class="st" id="streamTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Timestamp</th>
                                <th>Severity</th>
                                <th>Format</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody id="streamBody">
                            @foreach($lines as $i => $ln)
                                <tr class="sr" data-sev="{{ $ln['severity'] }}" style="animation-delay:{{ min($i*0.02,1.0) }}s">
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $ln['timestamp'] }}</td>
                                    <td><span class="sev s{{ $ln['severity'] }}">{{ $ln['severity'] }}</span></td>
                                    <td><span class="fmt-pill">{{ $fmtLabels[$ln['format']] ?? $ln['format'] }}</span></td>
                                    <td class="msg">{{ $ln['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <script>
        // Chart
        (function(){
            const labels = @json(array_keys($counts));
            const data   = @json(array_values($counts));
            const colors = @json(array_values(array_intersect_key($sevColors, $counts)));
            const isDark = document.documentElement.dataset.theme === 'dark';
            const gridC  = isDark ? '#2A2A27' : '#D5CEC4';

            new Chart(document.getElementById('sevChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: colors.map(c => c + '2E'),
                        borderColor: colors,
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1C1C1A',
                            titleColor: '#F0EDE8',
                            bodyColor: '#7A7A74',
                            borderColor: '#2A2A27',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: { label: c => ` ${c.parsed.y.toLocaleString()} entries` }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#7A7A74', font: { family: 'JetBrains Mono', size: 11 } },
                            border: { color: '#2A2A27' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridC },
                            ticks: { color: '#7A7A74', font: { family: 'JetBrains Mono', size: 11 }, maxTicksLimit: 6 },
                            border: { display: false }
                        }
                    }
                }
            });
        })();

        // Severity filter 
        function filterStream(sev, btn) {
            document.querySelectorAll('#filterBar .fbtn').forEach(b => b.classList.remove('on'));
            btn.classList.add('on');
            const rows = document.querySelectorAll('#streamBody tr');
            let shown = 0;
            rows.forEach(r => {
                const match = sev === 'ALL' || r.dataset.sev === sev;
                r.style.display = match ? '' : 'none';
                if (match) shown++;
            });
            document.getElementById('filterCount').textContent = shown + ' shown';
        }
        </script>

    @else
        <div class="card idle">
            <div class="idle-icon">
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
                    <rect x="3" y="5" width="20" height="16" rx="3" stroke="var(--dim)" stroke-width="1.5"/>
                    <path d="M7 10l4 3-4 3M13 16h6" stroke="var(--dim)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2>No logs analyzed yet</h2>
        </div>
    @endif

    <footer class="ft">
        <p>LogStream &copy; {{ date('Y') }}</p>
    </footer>

</div>

<script>
const html = document.documentElement;
const tog  = document.getElementById('themeToggle');
const stored = localStorage.getItem('ls-theme');
html.dataset.theme = stored ?? (matchMedia('(prefers-color-scheme:dark)').matches ? 'dark' : 'light');
tog.addEventListener('click', () => {
    const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
    html.dataset.theme = next;
    localStorage.setItem('ls-theme', next);
});

function switchTab(which) {
    ['file','paste'].forEach(t => {
        document.getElementById('tab-' + t).classList.toggle('active', t === which);
        document.getElementById('panel-' + t).classList.toggle('active', t === which);
        document.getElementById('tab-' + t).setAttribute('aria-selected', t === which);
    });

    const tabButtons = document.querySelectorAll('.tab-btn');

    tabButtons.forEach((tab, index) => {

        tab.addEventListener('keydown', (e) => {

            if (e.key === 'ArrowRight') {

                e.preventDefault();

                const nextIndex =
                    index === tabButtons.length - 1
                    ? 0
                    : index + 1;

                tabButtons[nextIndex].focus();
                tabButtons[nextIndex].click();
            }

            if (e.key === 'ArrowLeft') {

                e.preventDefault();

                const prevIndex =
                    index === 0
                    ? tabButtons.length - 1
                    : index - 1;

                tabButtons[prevIndex].focus();
                tabButtons[prevIndex].click();
            }
        });

    });
}

const fi   = document.getElementById('fileInput');
const fnd  = document.getElementById('fileNameDisplay');
const fsub = document.getElementById('fileSubmit');
const dz   = document.getElementById('dropZone');

const placeholder = document.getElementById('uploadPlaceholder');
const uploadedState = document.getElementById('uploadedState');

fi.addEventListener('change', () => {
    if (fi.files.length) {

        const file = fi.files[0];
        const size = (file.size / 1024).toFixed(2);

        fnd.textContent = `${file.name} (${size} KB)`;

        placeholder.style.display = 'none';
        uploadedState.style.display = 'block';

        fsub.disabled = false;
    } else {

        placeholder.style.display = 'block';
        uploadedState.style.display = 'none';

        fnd.textContent = '';
        fsub.disabled = true;
    }
});
dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('over'); });
dz.addEventListener('dragleave', () => dz.classList.remove('over'));
dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('over');
    if (e.dataTransfer.files.length) { fi.files = e.dataTransfer.files; fi.dispatchEvent(new Event('change')); }
});
dz.addEventListener('keydown', e => { if (e.key==='Enter'||e.key===' ') fi.click(); });

const pa   = document.getElementById('pasteArea');
const psub = document.getElementById('pasteSubmit');
const cc   = document.getElementById('charCount');

pa.addEventListener('input', () => {
    const len = pa.value.length;
    cc.textContent = len.toLocaleString() + ' chars';
    psub.disabled = len < 5;
});

@if(session('log_data') && (session('log_data')['mode'] ?? '') === 'paste')
    switchTab('paste');
@endif
</script>
</body>
</html>