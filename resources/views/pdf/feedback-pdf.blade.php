<!DOCTYPE html>
<html>
<head>
    <title>Feedback Report PDF</title>
    <style>
        @font-face {
    font-family: 'NotoSansEmoji';
    src: url('{{ public_path('fonts/NotoSans-Regular.ttf') }}') format('truetype');
}

       body {  font-family: 'NotoSansEmoji', sans-serif; font-size: 12px; }
        .filters { margin-bottom: 15px; }
        .filters p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f0f0f0; }
        .logo { width: 100px; }
    </style>
</head>
<body>
<table width="100%">
        <tr>
            <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
            <td style="text-align: right;"><h2>{{ __('report.Feedback Reports') }}</h2></td>
            <td style="text-align: right;"><p><strong>{{ __('report.Branch') }}:</strong> {{ $filters['Branch Name'] }}</p></td>
            <td style="text-align: right;"><p>{{ $filters['Created From'] }} <strong>To:</strong> {{ $filters['Created Until'] }}</p></td>
        </tr>
    </table>
    <h2>{{ __('report.Feedback Report') }}</h2>
    <p><strong>{{ __('report.Branch') }}:</strong> {{ $filters['Branch Name'] }}</p>
    <p><strong>{{ __('report.From') }}:</strong> {{ $filters['Created From'] }} <strong>{{ __('report.To') }}:</strong> {{ $filters['Created Until'] }}</p>

    <table>
        <thead>
            <tr>
              
                <th>{{ __('report.name') }}</th>
                <th>{{ __('report.Question') }}</th>
                <th>{{ __('report.Rating') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($records as $report)
            <tr>
           
                <td>
                    @php
                        $name = !empty($report->user_id) ? $report->user->name : ($report->queues->name ?? 'N/A');
                    @endphp
                    {{ $name }}
                </td>
                <td>{{ $report->question ?? 'N/A' }}</td>
                <!-- <td>{{ \App\Models\Queue::getEmojiText()[$report->rating]['emoji'] ?? 'N/A' }}</td> -->
                <td>{{ $report->rating ?? 'N/A' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
