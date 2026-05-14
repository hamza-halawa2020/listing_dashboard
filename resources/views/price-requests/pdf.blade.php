<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>{{ __('Price Request #:id', ['id' => $priceRequest->id]) }}</title>
    <style>
        @page {
            margin: 18px;
        }

        body {
            font-family: dejavusans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #0f172a;
            direction:
                {{ $isArabic ? 'rtl' : 'ltr' }}
            ;
            text-align:
                {{ $isArabic ? 'right' : 'left' }}
            ;
        }

        .header {
            background: #0f172a;
            color: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
        }

        .title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            color: #cbd5e1;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.info td {
            width: 50%;
            vertical-align: top;
            padding: 6px;
        }

        .cell {
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 10px 12px;
            min-height: 58px;
        }

        .label {
            font-size: 10px;
            color: #475569;
            margin: 0 0 6px;
            font-weight: 700;
        }

        .value {
            font-size: 13px;
            margin: 0;
            word-wrap: break-word;
        }

        .block {
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-top: 10px;
        }

        .block-title {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }

        .block-text {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 class="title">{{ __('Price Request #:id', ['id' => $priceRequest->id]) }}</h1>
        <div class="meta">
            {{ __('Created At') }}: {{ $priceRequest->created_at?->format('Y-m-d H:i') ?? '-' }}
            |
            {{ __('Responded') }}: {{ $priceRequest->status ? __('Yes') : __('No') }}
        </div>
    </div>

    <table class="info">
        <tr>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Company Name') }}</p>
                    <p class="value">{{ $priceRequest->company_name ?: '-' }}</p>
                </div>
            </td>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Contact Person') }}</p>
                    <p class="value">{{ $priceRequest->contact_person ?: '-' }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Email') }}</p>
                    <p class="value">{{ $priceRequest->email ?: '-' }}</p>
                </div>
            </td>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Phone') }}</p>
                    <p class="value">{{ $priceRequest->phone ?: '-' }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Company Type') }}</p>
                    <p class="value">{{ $priceRequest->company_type_label ?: '-' }}</p>
                </div>
            </td>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Employee Count') }}</p>
                    <p class="value">{{ $priceRequest->employee_count ?: '-' }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Budget Range') }}</p>
                    <p class="value">{{ $priceRequest->budget_range_label ?: '-' }}</p>
                </div>
            </td>
            <td>
                <div class="cell">
                    <p class="label">{{ __('Timeline') }}</p>
                    <p class="value">{{ $priceRequest->timeline_label ?: '-' }}</p>
                </div>
            </td>
        </tr>
    </table>

    <div class="block">
        <p class="block-title">{{ __('Services Needed') }}</p>
        <p class="block-text">{{ $priceRequest->services_needed ?: '-' }}</p>
    </div>

    <div class="block">
        <p class="block-title">{{ __('Additional Requirements') }}</p>
        <p class="block-text">{{ $priceRequest->additional_requirements ?: '-' }}</p>
    </div>

    <div class="block">
        <p class="block-title">{{ __('Response Notes') }}</p>
        <p class="block-text">{{ $priceRequest->response_notes ?: '-' }}</p>
    </div>
</body>

</html>