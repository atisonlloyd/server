<ul class="list-group list_payment_method">
    @php
        $selected = session('selected_payment_method');
        $default = \Botble\Payment\Supports\PaymentHelper::defaultPaymentMethod();
        $selecting = $selected ?: $default;
    @endphp
    {!! apply_filters(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, null, compact('name', 'amount', 'currency', 'selected', 'default', 'selecting')) !!}

    @include('plugins/payment::partials.cod')
    @include('plugins/payment::partials.bank-transfer')
</ul>

