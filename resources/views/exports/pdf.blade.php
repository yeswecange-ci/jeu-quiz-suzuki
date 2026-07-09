<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 11px; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .subtitle { color: #6b7280; font-size: 11px; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #111827;
            color: #fff;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
        }
        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:nth-child(even) { background: #f5f6f8; }
        .empty { padding: 20px; text-align: center; color: #6b7280; }
        .meta { margin-top: 14px; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="subtitle">{{ $subtitle }}</p>

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ count($headers) }}">Aucune donnée à afficher</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
</body>
</html>
