<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        Pay {{ $quote->quote_number }} with M-PESA
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>
        :root {
            --kplc-blue: #0057a8;
            --kplc-yellow: #ffcc00;
            --mpesa-green: #159447;
            --page-background: #f4f7fb;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--page-background);
            color: #1f2937;
            font-family: Arial, Helvetica, sans-serif;
        }

        .payment-header {
            padding: 1.25rem;
            background:
                linear-gradient(
                    135deg,
                    #003b75,
                    var(--kplc-blue)
                );
            color: #ffffff;
        }

        .payment-shell {
            max-width: 720px;
            margin: 2rem auto;
            padding: 0 1rem 2rem;
        }

        .payment-card {
            overflow: hidden;
            border: 0;
            border-radius: 1rem;
            background: #ffffff;
            box-shadow:
                0 14px 40px
                rgba(15, 23, 42, 0.12);
        }

        .payment-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .mpesa-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: #e8f7ee;
            color: var(--mpesa-green);
            font-weight: 700;
        }

        .quote-summary {
            padding: 1.5rem;
            background: #f8fafc;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.7rem 0;
            border-bottom: 1px dashed #d8dee8;
        }

        .summary-row:last-child {
            border-bottom: 0;
        }

        .balance-amount {
            color: var(--mpesa-green);
            font-size: 1.6rem;
            font-weight: 800;
        }

        .payment-form {
            padding: 1.5rem;
        }

        .payment-button {
            min-height: 52px;
            border: 0;
            background: var(--mpesa-green);
            color: #ffffff;
            font-weight: 700;
        }

        .payment-button:hover,
        .payment-button:focus {
            background: #0f7d3b;
            color: #ffffff;
        }

        .payment-status {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
        }

        .payment-status.processing {
            display: block;
            background: #fff8db;
            color: #785f00;
        }

        .payment-status.success {
            display: block;
            background: #e8f7ee;
            color: #116c35;
        }

        .payment-status.failed {
            display: block;
            background: #fdecec;
            color: #a61b1b;
        }

        .security-note {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #5f6b7a;
            font-size: 0.87rem;
        }

        @media (max-width: 575.98px) {
            .payment-shell {
                margin-top: 1rem;
            }

            .summary-row {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.25rem;
            }
        }
    </style>
</head>

<body>
    <header class="payment-header">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <i class="fas fa-bolt fa-2x"></i>
                </div>

                <div>
                    <h1 class="h4 mb-1 fw-bold">
                        Meter Calibration Laboratory
                    </h1>

                    <div class="text-white-50">
                        Secure quote payment
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="payment-shell">
        <div class="card payment-card">
            <div class="payment-card-header">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <div class="mpesa-badge mb-2">
                            <i class="fas fa-mobile-screen-button"></i>
                            M-PESA
                        </div>

                        <h2 class="h5 fw-bold mb-1">
                            Pay Quote
                            {{ $quote->quote_number }}
                        </h2>

                        <p class="text-muted mb-0">
                            Enter the M-PESA phone number that
                            should receive the payment prompt.
                        </p>
                    </div>
                </div>
            </div>

            <div class="quote-summary">
                <div class="summary-row">
                    <span class="text-muted">
                        Quote number
                    </span>

                    <strong>
                        {{ $quote->quote_number }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span class="text-muted">
                        Customer
                    </span>

                    <strong>
                        {{
                            $quote->customer?->company_name
                            ?? $quote->customer?->name
                            ?? 'Customer'
                        }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span class="text-muted">
                        Total amount
                    </span>

                    <strong>
                        {{ $quote->currency }}
                        {{ number_format($quote->total_amount, 2) }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span class="text-muted">
                        Amount already paid
                    </span>

                    <strong>
                        {{ $quote->currency }}
                        {{ number_format($quote->amount_paid, 2) }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span class="text-muted">
                        Balance due
                    </span>

                    <span class="balance-amount">
                        {{ $quote->currency }}
                        {{ number_format($outstandingBalance, 2) }}
                    </span>
                </div>
            </div>

            @if($outstandingBalance > 0)
                <div class="payment-form">
                    <div
                        id="paymentError"
                        class="alert alert-danger d-none"
                    ></div>

                    <form id="mpesaPaymentForm">
                        <div class="mb-3">
                            <label
                                for="phone_number"
                                class="form-label fw-semibold"
                            >
                                M-PESA phone number
                            </label>

                            <input
                                type="tel"
                                id="phone_number"
                                name="phone_number"
                                class="form-control form-control-lg"
                                placeholder="0712345678"
                                maxlength="20"
                                required
                                autocomplete="tel"
                            >

                            <div class="form-text">
                                Use a Safaricom number registered
                                for M-PESA.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label
                                for="amount"
                                class="form-label fw-semibold"
                            >
                                Amount to pay
                            </label>

                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    KES
                                </span>

                                <input
                                    type="number"
                                    id="amount"
                                    name="amount"
                                    class="form-control"
                                    value="{{ (int) round($outstandingBalance) }}"
                                    min="1"
                                    max="{{ (int) floor($outstandingBalance) }}"
                                    step="1"
                                    required
                                >
                            </div>
                        </div>

                        <button
                            type="submit"
                            id="payButton"
                            class="btn payment-button w-100"
                        >
                            <i class="fas fa-mobile-screen-button me-2"></i>
                            Send M-PESA Prompt
                        </button>
                    </form>

                    <div
                        id="paymentStatus"
                        class="payment-status"
                    ></div>
                </div>
            @else
                <div class="p-4">
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-circle-check me-2"></i>

                        This quote has been fully paid.
                    </div>
                </div>
            @endif

            <div class="security-note">
                <i class="fas fa-shield-halved me-1"></i>

                You will enter your M-PESA PIN only on the
                secure prompt displayed on your phone.
                This website does not request or store your PIN.
            </div>
        </div>
    </main>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const form = document.getElementById(
                    'mpesaPaymentForm'
                );

                if (!form) {
                    return;
                }

                const payButton = document.getElementById(
                    'payButton'
                );

                const errorBox = document.getElementById(
                    'paymentError'
                );

                const statusBox = document.getElementById(
                    'paymentStatus'
                );

                const csrfToken = document
                    .querySelector(
                        'meta[name="csrf-token"]'
                    )
                    ?.getAttribute('content');

                let pollingTimer = null;
                let pollingAttempts = 0;
                const maximumPollingAttempts = 40;

                function setProcessing(processing) {
                    payButton.disabled = processing;

                    payButton.innerHTML = processing
                        ? `
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Sending Prompt...
                        `
                        : `
                            <i class="fas fa-mobile-screen-button me-2"></i>
                            Send M-PESA Prompt
                        `;
                }

                function showError(message) {
                    errorBox.textContent = message;
                    errorBox.classList.remove('d-none');
                }

                function clearError() {
                    errorBox.textContent = '';
                    errorBox.classList.add('d-none');
                }

                function showStatus(type, message) {
                    statusBox.className =
                        'payment-status ' + type;

                    statusBox.innerHTML = message;
                }

                async function pollPaymentStatus(url) {
                    pollingAttempts++;

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(
                                data.message
                                || 'Payment status could not be checked.'
                            );
                        }

                        const payment = data.payment;

                        if (payment.status === 'completed') {
                            clearInterval(pollingTimer);

                            showStatus(
                                'success',
                                `
                                    <div class="fw-bold mb-1">
                                        <i class="fas fa-circle-check me-1"></i>
                                        Payment successful
                                    </div>

                                    <div>
                                        Receipt:
                                        <strong>
                                            ${payment.receipt_number || ''}
                                        </strong>
                                    </div>

                                    <div>
                                        Amount:
                                        <strong>
                                            KES ${Number(
                                                payment.paid_amount
                                            ).toLocaleString(
                                                undefined,
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                }
                                            )}
                                        </strong>
                                    </div>
                                `
                            );

                            form.classList.add('d-none');

                            return;
                        }

                        if (
                            [
                                'failed',
                                'cancelled',
                                'timeout'
                            ].includes(payment.status)
                        ) {
                            clearInterval(pollingTimer);
                            setProcessing(false);

                            showStatus(
                                'failed',
                                `
                                    <div class="fw-bold mb-1">
                                        <i class="fas fa-circle-xmark me-1"></i>
                                        Payment not completed
                                    </div>

                                    <div>
                                        ${
                                            payment.message
                                            || 'The transaction was not completed.'
                                        }
                                    </div>
                                `
                            );

                            return;
                        }

                        if (
                            pollingAttempts
                            >= maximumPollingAttempts
                        ) {
                            clearInterval(pollingTimer);
                            setProcessing(false);

                            showStatus(
                                'processing',
                                `
                                    Payment confirmation is taking longer
                                    than expected. Check your M-PESA messages
                                    or refresh this page later.
                                `
                            );
                        }
                    } catch (error) {
                        if (
                            pollingAttempts
                            >= maximumPollingAttempts
                        ) {
                            clearInterval(pollingTimer);
                            setProcessing(false);

                            showError(
                                error.message
                                || 'Payment status could not be checked.'
                            );
                        }
                    }
                }

                form.addEventListener(
                    'submit',
                    async function (event) {
                        event.preventDefault();

                        clearError();
                        setProcessing(true);

                        showStatus(
                            'processing',
                            `
                                <i class="fas fa-spinner fa-spin me-2"></i>
                                Sending an M-PESA payment prompt...
                            `
                        );

                        try {
                            const response = await fetch(
                                @json($initiateUrl),
                                {
                                    method: 'POST',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'Content-Type':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            csrfToken,

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },

                                    body: JSON.stringify({
                                        phone_number:
                                            document
                                                .getElementById(
                                                    'phone_number'
                                                )
                                                .value,

                                        amount:
                                            document
                                                .getElementById(
                                                    'amount'
                                                )
                                                .value,
                                    }),
                                }
                            );

                            const data = await response.json();

                            if (
                                !response.ok
                                || !data.success
                            ) {
                                const validationErrors =
                                    data.errors
                                    ? Object.values(
                                        data.errors
                                    ).flat().join(' ')
                                    : null;

                                throw new Error(
                                    validationErrors
                                    || data.message
                                    || 'Payment could not be initiated.'
                                );
                            }

                            showStatus(
                                'processing',
                                `
                                    <div class="fw-bold mb-1">
                                        <i class="fas fa-mobile-screen-button me-1"></i>
                                        Check your phone
                                    </div>

                                    <div>
                                        ${data.message}
                                    </div>
                                `
                            );

                            pollingTimer = setInterval(
                                function () {
                                    pollPaymentStatus(
                                        data.status_url
                                    );
                                },
                                3000
                            );

                            pollPaymentStatus(
                                data.status_url
                            );
                        } catch (error) {
                            setProcessing(false);

                            showStatus(
                                'failed',
                                `
                                    <i class="fas fa-circle-xmark me-1"></i>
                                    ${
                                        error.message
                                        || 'Payment could not be initiated.'
                                    }
                                `
                            );
                        }
                    }
                );
            }
        );
    </script>
</body>
</html>
