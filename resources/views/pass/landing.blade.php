<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pass->name ?? 'Pass' }} — Add to Wallet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #0f172a; --fg: #e2e8f0; --muted: #94a3b8; --card: #111827; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial; background: linear-gradient(180deg, #0b1220, #0f172a); color: var(--fg); }
        .wrap { max-width: 720px; margin: 0 auto; padding: 40px 20px; }
        .card { background: rgba(17, 24, 39, 0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 28px; backdrop-filter: blur(6px); }
        h1 { margin: 0 0 8px; font-size: 26px; }
        p { margin: 0 0 20px; color: var(--muted); }
        .actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-top: 10px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 16px; border-radius: 12px; text-decoration: none; font-weight: 600; transition: transform .08s ease, box-shadow .2s ease; border: 1px solid rgba(255,255,255,0.08); }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,0,0,0.35); }
        .btn-apple { background: #0b0b0b; color: #fff; }
        .btn-google { background: #fff; color: #111827; }
        .meta { margin-top: 22px; font-size: 12px; color: var(--muted); }
        .id { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; opacity: .8; }
        .header { display:flex; align-items:center; gap:14px; margin-bottom: 6px; }
        .logo { width: 38px; height: 38px; border-radius: 10px; object-fit: cover; border: 1px solid rgba(255,255,255,0.08); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="header">
                <div>
                    <h1>{{ $pass->name ?? 'Pass' }}</h1>
                    <div class="id">Pass ID: {{ $pass->id }}</div>
                </div>
            </div>
            <p>Add this pass to your preferred wallet.</p>
            <div class="actions">
                <a class="btn btn-apple" href="{{ route('public.pass.apple', $pass) }}" rel="noopener">Add to Apple Wallet</a>
                <a class="btn btn-google" href="{{ route('public.pass.google', $pass) }}" rel="noopener">Add to Google Wallet</a>
            </div>
            <div class="meta">Having trouble? Choose your wallet format above.</div>
        </div>
    </div>
</body>
</html>
