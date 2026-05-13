<p>{{ __('Hello,') }}</p>

<p>
    {{ __('Please find attached the price request PDF for request #:id.', ['id' => $priceRequest->id]) }}
</p>

<p>
    {{ __('Contact Person: :name', ['name' => $priceRequest->contact_person]) }}<br>
    {{ __('Company: :name', ['name' => $priceRequest->company_name ?: '-']) }}
</p>

<p>
    {{ __('Shared link (valid for 30 days):') }}<br>
    <a href="{{ $shareUrl }}">{{ $shareUrl }}</a>
</p>