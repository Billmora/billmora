<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            color: #1d293d;
            line-height: 1.6;
            background: #fff;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .company-logo {
            width: 64px;
            height: auto;
            margin-bottom: 20px;
            border-radius: 5px
        }

        .header {
            width: 100%;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-top: 2px solid #eceeff;
            padding-top: 10px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .header td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .header td.left {
            width: 50%;
            text-align: left;
        }

        .header td.right {
            width: 50%;
            text-align: right;
        }

        .client-info {
            text-align: left;
        }

        .client-info .label {
            font-weight: 600;
            color: #7267ef;
            display: block;
            margin: 5px 0;
        }

        .client-info .value {
            font-size: 14px;
            color: #62748e;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta .label {
            font-weight: 600;
            color: #7267ef;
            display: block;
            margin: 5px 0;
        }

        .invoice-meta .value {
            font-size: 14px;
            color: #62748e;
        }

        /* Items Table */
        .items-section {
            margin: 30px 0;
        }

        .items-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: #45556c;
            margin-bottom: 15px;
        }

        .items-section tbody {
            border-bottom: 2px solid #7267ef;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background: #eceeff;
            border-top: 2px solid #7267ef;
            border-bottom: 2px solid #7267ef;
        }

        th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #7267ef;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px;
            font-size: 13px;
            border-bottom: 1px solid #eceeff;
        }

        .description {
            color: #62748e;
            font-weight: 500;
        }

        .text-right {
            text-align: right;
        }

        .amount {
            font-weight: 600;
            color: #62748e;
        }

        .totals {
            width: 100%;
            margin: 20px 0;
        }

        .totals table {
            width: 100%;
            margin-bottom: 0;
        }

        .totals td {
            border: none;
            padding: 0;
        }

        .totals td.left {
            width: 50%;
            text-align: left;
        }

        .totals td.right {
            width: 50%;
            text-align: right;
        }

        .totals-content {
            width: 100%;
        }

        .total-row {
            width: 100%;
            padding: 10px 0;
            font-size: 13px;
        }

        .total-row table {
            width: 100%;
            margin-bottom: 0;
        }

        .total-row td {
            padding: 0;
            border: none;
        }

        .total-row .label {
            color: #62748e;
            font-weight: 600;
            text-align: left;
        }

        .total-row .value {
            font-weight: 700;
            color: #45556c;
            text-align: right;
        }

        .grand-total {
            width: 100%;
            font-size: 16px;
            font-weight: 700;
            color: #62748e;
            padding: 10px 0;
            border-top: 2px solid #7267ef;
        }

        .grand-total table {
            width: 100%;
            margin-bottom: 0;
        }

        .grand-total td {
            padding: 0;
            border: none;
        }

        .grand-total .label {
            text-align: left;
        }

        .grand-total .value {
            color: #45556c;
            text-align: right;
        }

        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bolder;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-unpaid {
            background: #ffb8bc;
            color: #fb2c36;
            border: 1px solid #fb2c36;
        }

        .status-paid {
            background: #b2f3c1;
            color: #00ab28;
            border: 1px solid #00ab28;
        }

        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #383d41;
        }

        .status-refunded {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #383d41;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eceeff;
            font-size: 14px;
            font-weight: 500;
            color: #45556c;
            text-align: center;
        }

        .footer a {
            color: #7267ef;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img class="company-logo" src="{{ Billmora::getGeneral('company_logo') }}" alt="billmora logo">
        
        <div class="header">
            <table>
                <tr>
                    <td class="left">
                        <div class="client-info">
                            <div style="margin-top: 10px;">
                                <span class="label">{{ __('client/invoices.invoice_number') }}</span>
                                <span class="value">#{{ $invoice->invoice_number }}</span>
                            </div>
                            <div style="margin-top: 10px;">
                                <span class="label">{{ __('client/invoices.bill_to') }}</span>
                                <span class="value">{{ $invoice->user->fullname }}</span>
                            </div>
                            <div style="margin-top: 5px;">
                                <span class="label">{{ __('client/invoices.currency') }}</span>
                                <span class="value">{{ $invoice->currency }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="right">
                        <div class="invoice-meta">
                            <div style="margin-top: 10px;">
                                <span class="label">{{ __('common.status') }}</span>
                                <span class="status-badge status-{{ strtolower($invoice->status) }}">
                                    {{ strtoupper($invoice->status) }}
                                </span>
                            </div>
                            <div style="margin-top: 10px;">
                                <span class="label">{{ __('client/invoices.invoice_date') }}</span>
                                <span class="value">{{ $invoice->created_at->format(Billmora::getGeneral('company_date_format')) }}</span>
                            </div>
                            <div style="margin-top: 5px;">
                                <span class="label">{{ __('client/invoices.due_date') }}</span>
                                <span class="value">{{ $invoice->due_date->format(Billmora::getGeneral('company_date_format')) }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items -->
        <div class="items-section">
            <h3>Invoice Items</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">{{ __('client/invoices.description') }}</th>
                        <th style="width: 12%; text-align: center;">{{ __('client/invoices.quantity') }}</th>
                        <th style="width: 18%; text-align: right;">{{ __('client/invoices.unit_price') }}</th>
                        <th style="width: 20%; text-align: right;">{{ __('client/invoices.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="description">{{ $item->description }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td class="text-right">
                                {{ Currency::format($item->unit_price, $invoice->currency) }}
                            </td>
                            <td class="text-right amount">
                                {{ Currency::format($item->amount, $invoice->currency) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals">
            <table>
                <tr>
                    <td class="left"></td>
                    <td class="right">
                        <div class="totals-content">
                            <div class="total-row">
                                <table>
                                    <tr>
                                        <td class="label">{{ __('client/invoices.subtotal') }}</td>
                                        <td class="value">{{ Currency::format($invoice->subtotal, $invoice->currency) }}</td>
                                    </tr>
                                </table>
                            </div>

                            @if($invoice->discount > 0)
                                <div class="total-row">
                                    <table>
                                        <tr>
                                            <td class="label">{{ __('client/invoices.discount') }}</td>
                                            <td class="value">{{ Currency::format($invoice->discount, $invoice->currency) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif

                            <div class="grand-total">
                                <table>
                                    <tr>
                                        <td class="label">{{ __('client/invoices.total_due') }}</td>
                                        <td class="value">{{ Currency::format($invoice->total, $invoice->currency) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} {{ Billmora::getGeneral('company_name') }} - Powered by <a href="https://billmora.com" target="_blank">Billmora</a></p>
        </div>
    </div>
</body>
</html>