@extends('layouts.app')

@php
    $pageTitle = 'Loan Products - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Loan Products</h2>
    <p>Explore our lending options, advertised rates, key benefits, and eligibility requirements before applying.</p>
@endsection

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #163d7a, #1d4ed8);
        color: white;
        border-radius: 22px;
        padding: 26px;
        margin-bottom: 24px;
        box-shadow: 0 10px 24px rgba(29, 78, 216, 0.22);
    }

    .hero-section h3 {
        margin: 0 0 10px;
        font-size: 28px;
        color: white;
    }

    .hero-section p {
        margin: 0;
        color: #dbeafe;
        line-height: 1.7;
        max-width: 860px;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 22px;
    }

    .product-card {
        background: white;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    body.dark-mode .product-card,
    body.dark-mode .info-panel {
        background: rgba(17,24,39,0.92);
        border-color: #334155;
    }

    .product-top {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
    }

    .product-title h3 {
        margin: 0 0 6px;
        color: #163d7a;
        font-size: 22px;
    }

    body.dark-mode .product-title h3,
    body.dark-mode .info-panel h3 {
        color: #bfdbfe;
    }

    .product-subtitle {
        font-size: 13px;
        color: #6b7280;
    }

    body.dark-mode .product-subtitle,
    body.dark-mode .feature-list li,
    body.dark-mode .detail-row,
    body.dark-mode .info-panel p,
    body.dark-mode .disclaimer-text {
        color: #cbd5e1;
    }

    .badge {
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: #dbeafe;
        color: #1d4ed8;
        white-space: nowrap;
    }

    .rate-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 16px;
    }

    body.dark-mode .rate-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .rate-box .label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 6px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .rate-box .value {
        font-size: 28px;
        font-weight: 800;
        color: #163d7a;
    }

    body.dark-mode .rate-box .value {
        color: #bfdbfe;
    }

    .detail-list {
        display: grid;
        gap: 10px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
        color: #4b5563;
    }

    body.dark-mode .detail-row {
        border-bottom-color: #334155;
    }

    .detail-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .detail-row strong {
        color: #111827;
    }

    body.dark-mode .detail-row strong {
        color: #f3f4f6;
    }

    .feature-list {
        margin: 0;
        padding-left: 18px;
    }

    .feature-list li {
        margin-bottom: 8px;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.5;
    }

    .feature-list li:last-child {
        margin-bottom: 0;
    }

    .desc-text,
    .disclaimer-text {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.7;
        margin: 0;
    }

    .product-actions {
        margin-top: auto;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .apply-btn,
    .secondary-btn {
        text-decoration: none;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .apply-btn {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: white;
        box-shadow: 0 8px 18px rgba(29, 78, 216, 0.2);
    }

    .secondary-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #dbeafe;
    }

    body.dark-mode .secondary-btn {
        background: rgba(30, 41, 59, 0.75);
        color: #bfdbfe;
        border-color: #334155;
    }

    .info-section {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
        margin-top: 26px;
    }

    .info-panel {
        background: white;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }

    .info-panel h3 {
        margin-top: 0;
        margin-bottom: 12px;
        color: #163d7a;
        font-size: 20px;
    }

    .info-panel p {
        margin: 0 0 10px;
        color: #4b5563;
        line-height: 1.7;
        font-size: 14px;
    }

    .info-panel p:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 960px) {
        .info-section {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <section class="hero-section">
        <h3>Find the right loan for your needs</h3>
        <p>
            Bank of Fiji offers simple loan products with competitive advertised rates, flexible repayment terms,
            and a quick online application process. Review the product details below and apply directly from the
            product that best matches your needs.
        </p>
    </section>

    @if(!empty($loanProducts) && count($loanProducts) > 0)
        <div class="products-grid">
            @foreach($loanProducts as $product)
                @php
                    $name = $product['name'] ?? 'Loan Product';
                    $loanType = $product['loanType'] ?? '';
                    $advertisedRate = (float)($product['advertisedRate'] ?? 0);
                    $maxAmount = (float)($product['maxAmount'] ?? 0);
                    $maxTenure = $product['maxTenureMonths'] ?? '-';
                    $repaymentExample = $product['repaymentExample'] ?? '';
                    $targetCustomer = $product['targetCustomer'] ?? '';
                    $description = $product['description'] ?? '';
                    $features = $product['features'] ?? '';
                    $eligibility = $product['eligibilityCriteria'] ?? '';
                    $documents = $product['requiredDocuments'] ?? '';
                    $processingTime = $product['processingTime'] ?? '';
                    $heroBadge = $product['heroBadge'] ?? '';
                    $disclaimer = $product['disclaimer'] ?? '';
                @endphp

                <div class="product-card">
                    <div class="product-top">
                        <div class="product-title">
                            <h3>{{ $name }}</h3>
                            <div class="product-subtitle">{{ $targetCustomer ?: 'Suitable for a wide range of eligible customers' }}</div>
                        </div>

                        @if(!empty($heroBadge))
                            <span class="badge">{{ $heroBadge }}</span>
                        @endif
                    </div>

                    <div class="rate-box">
                        <div class="label">Advertised Rate</div>
                        <div class="value">From {{ number_format($advertisedRate, 2) }}% p.a.</div>
                    </div>

                    <div class="detail-list">
                        <div class="detail-row">
                            <span>Maximum Amount</span>
                            <strong>${{ number_format($maxAmount, 2) }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Maximum Term</span>
                            <strong>{{ $maxTenure }} months</strong>
                        </div>
                        <div class="detail-row">
                            <span>Repayment Example</span>
                            <strong>{{ $repaymentExample ?: 'Available on selected terms' }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Processing Time</span>
                            <strong>{{ $processingTime ?: 'Subject to assessment' }}</strong>
                        </div>
                    </div>

                    @if(!empty($description))
                        <p class="desc-text">{{ $description }}</p>
                    @endif

                    @if(!empty($features))
                        <div>
                            <strong style="display:block; margin-bottom:8px;">Key Features</strong>
                            <ul class="feature-list">
                                @foreach(preg_split("/\r\n|\n|\r/", $features) as $line)
                                    @if(trim($line) !== '')
                                        <li>{{ trim($line) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($eligibility))
                        <div>
                            <strong style="display:block; margin-bottom:8px;">Eligibility</strong>
                            <p class="desc-text">{{ $eligibility }}</p>
                        </div>
                    @endif

                    @if(!empty($documents))
                        <div>
                            <strong style="display:block; margin-bottom:8px;">Required Documents</strong>
                            <p class="desc-text">{{ $documents }}</p>
                        </div>
                    @endif

                    @if(!empty($disclaimer))
                        <p class="disclaimer-text"><strong>Important:</strong> {{ $disclaimer }}</p>
                    @endif

                    <div class="product-actions">
                        <a href="{{ route('loan-application', ['type' => $loanType]) }}" class="apply-btn">Apply Now</a>
                        <a href="{{ route('my-loans') }}" class="secondary-btn">View My Loans</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <section class="info-panel">
            <h3>No active loan products found</h3>
            <p>Please add active loan products in the backend to display them here.</p>
        </section>
    @endif

    <div class="info-section">
        <section class="info-panel">
            <h3>Why choose BoF loans?</h3>
            <p>Our loan products are designed to offer competitive rates, flexible repayment structures, and a straightforward application process.</p>
            <p>Customers can compare key product details online, submit an application digitally, and track their submitted loan requests from the portal.</p>
        </section>

        <section class="info-panel">
            <h3>Important lending notice</h3>
            <p>Advertised rates are indicative and may vary depending on the customer profile, income verification, credit assessment, and supporting documents provided.</p>
            <p>Final approved terms are subject to Bank of Fiji’s lending policies and applicable conditions.</p>
        </section>
    </div>
@endsection