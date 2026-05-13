<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Price Request #:id', ['id' => $priceRequest->id]) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #1f2937;
            background: #f9fafb;
        }

        .card {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        h1 {
            margin-top: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .full {
            grid-column: 1 / -1;
        }

        .item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
        }

        .label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .value {
            font-size: 14px;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>{{ __('Price Request #:id', ['id' => $priceRequest->id]) }}</h1>
        <div class="grid">
            <div class="item">
                <div class="label">{{ __('Company Name') }}</div>
                <div class="value">{{ $priceRequest->company_name ?: '-' }}</div>
            </div>
            <div class="item">
                <div class="label">{{ __('Contact Person') }}</div>
                <div class="value">{{ $priceRequest->contact_person }}</div>
            </div>
            <div class="item">
                <div class="label">{{ __('Email') }}</div>
                <div class="value">{{ $priceRequest->email }}</div>
            </div>
            <div class="item">
                <div class="label">{{ __('Phone') }}</div>
                <div class="value">{{ $priceRequest->phone }}</div>
            </div>
            <div class="item">
                <div class="label">{{ __('Company Type') }}</div>
                <div class="value">{{ $priceRequest->company_type_label ?: '-' }}</div>
            </div>
            <div class="item">
                <div class="label">{{ __('Employee Count') }}</div>
                <div class="value">{{ $priceRequest->employee_count ?: '-' }}</div>
            </div>
            <div class="item">
                <div class="label">{{ __('Budget Range') }}</div>
                <div class="value">{{ $priceRequest->budget_range_label ?: '-' }}</div>
            </div>
            <div class="item">
                <div class="label">{{ __('Timeline') }}</div>
                <div class="value">{{ $priceRequest->timeline_label ?: '-' }}</div>
            </div>
            <div class="item full">
                <div class="label">{{ __('Services Needed') }}</div>
                <div class="value">{{ $priceRequest->services_needed }}</div>
            </div>
            <div class="item full">
                <div class="label">{{ __('Additional Requirements') }}</div>
                <div class="value">{{ $priceRequest->additional_requirements ?: '-' }}</div>
            </div>
            <div class="item full">
                <div class="label">{{ __('Response Notes') }}</div>
                <div class="value">{{ $priceRequest->response_notes ?: '-' }}</div>
            </div>
        </div>
    </div>
</body>

</html>