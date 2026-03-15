@extends('layouts.app')

@php
    $pageTitle = 'Transfer Money - BoF Online Banking';
@endphp

@section('topcard')
    <h2>Transfer Money</h2>
    <p>Send money within BoF or transfer to other local banks and wallets from one secure form.</p>
@endsection

@push('styles')
<style>
    .content-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
    }

    .panel {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    body.dark-mode .panel {
        background: rgba(17,24,39,0.92);
    }

    .panel h3 {
        margin-top: 0;
        margin-bottom: 18px;
        color: #163d7a;
        font-size: 24px;
    }

    body.dark-mode .panel h3,
    body.dark-mode .tips-box h4,
    body.dark-mode .transfer-summary h4,
    body.dark-mode .saved-beneficiary-box h4,
    body.dark-mode .schedule-box h4 {
        color: #bfdbfe;
    }

    .section-note {
        color: #6b7280;
        font-size: 13px;
        margin-top: -8px;
        margin-bottom: 18px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #1f2937;
    }

    body.dark-mode label {
        color: #e5e7eb;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 12px 14px;
        margin-bottom: 18px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        background: #fff;
        color: #111827;
    }

    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea {
        background: #111827;
        color: #f3f4f6;
        border-color: #334155;
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
    }

    .submit-btn {
        width: 100%;
        background: #1d4ed8;
        color: white;
        border: none;
        padding: 14px 18px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
    }

    .submit-btn:hover {
        background: #1e40af;
    }

    .info-card {
        background: linear-gradient(135deg, #163d7a, #1d4ed8);
        color: white;
        border-radius: 18px;
        padding: 22px;
        margin-bottom: 18px;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
    }

    .info-card h4 {
        margin: 0 0 8px;
        font-size: 16px;
        color: #dbeafe;
        font-weight: 500;
    }

    .info-card .balance {
        font-size: 28px;
        font-weight: bold;
        margin: 12px 0;
    }

    .info-card .meta {
        font-size: 14px;
        color: #dbeafe;
        margin-top: 8px;
    }

    .tips-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
    }

    body.dark-mode .tips-box,
    body.dark-mode .transfer-summary,
    body.dark-mode .transfer-type-box,
    body.dark-mode .saved-beneficiary-box,
    body.dark-mode .schedule-box {
        background: rgba(15, 23, 42, 0.85);
        border-color: #334155;
    }

    .tips-box h4 {
        margin-top: 0;
        color: #163d7a;
        margin-bottom: 12px;
    }

    .tips-box p {
        margin: 0 0 10px;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.5;
    }

    body.dark-mode .tips-box p,
    body.dark-mode .section-note,
    body.dark-mode .dynamic-help,
    body.dark-mode .saved-beneficiary-note,
    body.dark-mode .schedule-note {
        color: #cbd5e1;
    }

    .tips-box p:last-child {
        margin-bottom: 0;
    }

    .transfer-summary {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .transfer-summary h4 {
        margin-top: 0;
        margin-bottom: 12px;
        color: #163d7a;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    body.dark-mode .summary-row {
        border-bottom-color: #334155;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row strong {
        color: #111827;
    }

    body.dark-mode .summary-row strong {
        color: #f3f4f6;
    }

    .transfer-type-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 18px;
    }

    .transfer-type-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .transfer-type-option {
        position: relative;
    }

    .transfer-type-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .transfer-type-card {
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 14px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        min-height: 94px;
    }

    body.dark-mode .transfer-type-card {
        background: #111827;
        border-color: #334155;
    }

    .transfer-type-card:hover {
        border-color: #1d4ed8;
        transform: translateY(-1px);
    }

    .transfer-type-option input[type="radio"]:checked + .transfer-type-card {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
        background: #eff6ff;
    }

    body.dark-mode .transfer-type-option input[type="radio"]:checked + .transfer-type-card {
        background: rgba(30, 58, 138, 0.22);
    }

    .transfer-type-card strong {
        display: block;
        font-size: 14px;
        color: #163d7a;
        margin-bottom: 6px;
    }

    body.dark-mode .transfer-type-card strong {
        color: #bfdbfe;
    }

    .transfer-type-card span {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.4;
    }

    body.dark-mode .transfer-type-card span {
        color: #cbd5e1;
    }

    .dynamic-help {
        font-size: 12px;
        color: #6b7280;
        margin-top: -10px;
        margin-bottom: 16px;
    }

    .hidden-field {
        display: none;
    }

    .saved-beneficiary-box,
    .schedule-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 18px;
    }

    .saved-beneficiary-box h4,
    .schedule-box h4 {
        margin: 0 0 8px;
        color: #163d7a;
        font-size: 16px;
    }

    .saved-beneficiary-note,
    .schedule-note {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .schedule-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        font-weight: 600;
        color: #1f2937;
    }

    body.dark-mode .schedule-checkbox {
        color: #e5e7eb;
    }

    .schedule-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: #1d4ed8;
        flex-shrink: 0;
    }

    .schedule-fields {
        margin-top: 12px;
    }

    @media (max-width: 960px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .transfer-type-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <div class="content-grid">
        <section class="panel">
            <h3>Transfer Details</h3>
            <p class="section-note">Choose whether you are sending money within BoF or to another local bank or wallet.</p>

            <form method="POST" action="{{ route('transfer.submit') }}">
                @csrf

                @if(!empty($beneficiaries) && count($beneficiaries) > 0)
                    <div class="saved-beneficiary-box">
                        <h4>Use Saved Beneficiary</h4>
                        <div class="saved-beneficiary-note">Select a saved recipient to auto-fill transfer details.</div>

                        <select id="saved_beneficiary">
                            <option value="">-- Select Saved Beneficiary --</option>
                            @foreach($beneficiaries as $beneficiary)
                                <option
                                    value="{{ $beneficiary['id'] }}"
                                    data-mode="{{ strtolower($beneficiary['transferMode'] ?? '') === 'localbank' ? 'local_bank' : 'internal' }}"
                                    data-name="{{ $beneficiary['beneficiaryName'] ?? '' }}"
                                    data-institution="{{ $beneficiary['institutionName'] ?? '' }}"
                                    data-account="{{ $beneficiary['accountNumber'] ?? '' }}"
                                >
                                    {{ $beneficiary['nickname'] ?? '' }} - {{ $beneficiary['accountNumber'] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="transfer-type-box">
                    <label style="margin-bottom: 12px;">Transfer Type</label>

                    <div class="transfer-type-grid">
                        <label class="transfer-type-option">
                            <input
                                type="radio"
                                name="transfer_mode"
                                value="internal"
                                {{ old('transfer_mode', 'internal') === 'internal' ? 'checked' : '' }}
                            >
                            <div class="transfer-type-card">
                                <strong>BoF Internal Transfer</strong>
                                <span>Transfer between Bank of Fiji accounts.</span>
                            </div>
                        </label>

                        <label class="transfer-type-option">
                            <input
                                type="radio"
                                name="transfer_mode"
                                value="local_bank"
                                {{ old('transfer_mode') === 'local_bank' ? 'checked' : '' }}
                            >
                            <div class="transfer-type-card">
                                <strong>Other Local Bank / Wallet</strong>
                                <span>Send to M-Paisa, MyCash, BSP, ANZ, Westpac, or BRED.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <label for="from_account_id">From Account</label>
                <select name="from_account_id" id="from_account_id" required>
                    @foreach($customer['accounts'] ?? [] as $account)
                        <option value="{{ $account['id'] }}" {{ old('from_account_id') == $account['id'] ? 'selected' : '' }}>
                            {{ $account['accountNumber'] }} - {{ $account['accountType'] }} - ${{ number_format((float)($account['balance'] ?? 0), 2) }}
                        </option>
                    @endforeach
                </select>

                <div id="internal-transfer-fields">
                    <label for="to_account_number">Destination BoF Account Number</label>
                    <input
                        type="text"
                        id="to_account_number"
                        name="to_account_number"
                        value="{{ old('to_account_number') }}"
                        placeholder="Enter destination BoF account number"
                    >
                    <div class="dynamic-help">Use this for transfers to another Bank of Fiji account.</div>
                </div>

                <div id="local-bank-fields" class="hidden-field">
                    <label for="destination_institution_id">Destination Institution</label>
                    <select id="destination_institution_id" name="destination_institution_id">
                        <option value="">-- Select Institution --</option>
                        @foreach($otherLocalBanks ?? [] as $bank)
                            <option
                                value="{{ $bank['id'] }}"
                                data-name="{{ $bank['name'] ?? '' }}"
                                data-category="{{ $bank['category'] ?? '' }}"
                                data-label="{{ $bank['accountLabel'] ?? 'Account Number' }}"
                                data-placeholder="{{ $bank['accountPlaceholder'] ?? 'Enter destination account number' }}"
                                {{ old('destination_institution_id') == $bank['id'] ? 'selected' : '' }}
                            >
                                {{ $bank['name'] ?? '' }}
                            </option>
                        @endforeach
                    </select>

                    <label for="destination_account_number" id="destination-account-label">Account Number</label>
                    <input
                        type="text"
                        id="destination_account_number"
                        name="destination_account_number"
                        value="{{ old('destination_account_number') }}"
                        placeholder="Enter destination account number"
                    >

                    <label for="beneficiary_name">Beneficiary Name</label>
                    <input
                        type="text"
                        id="beneficiary_name"
                        name="beneficiary_name"
                        value="{{ old('beneficiary_name') }}"
                        placeholder="Enter beneficiary name"
                    >

                    <div class="dynamic-help" id="institution-help-text">
                        Use this section for transfers to local banks and wallet providers.
                    </div>
                </div>

                <div class="schedule-box">
                    <h4>Schedule Transfer</h4>
                    <div class="schedule-note">Choose this if you want the transfer to happen in the future instead of immediately.</div>

                    <label class="schedule-checkbox" for="is_scheduled_transfer">
                        <input
                            type="checkbox"
                            id="is_scheduled_transfer"
                            name="is_scheduled_transfer"
                            value="1"
                            {{ old('is_scheduled_transfer') ? 'checked' : '' }}
                        >
                        <span>Schedule this transfer</span>
                    </label>

                    <div id="scheduled-transfer-fields" class="schedule-fields hidden-field">
                        <label for="scheduled_date">Scheduled Date & Time</label>
                        <input
                            type="datetime-local"
                            id="scheduled_date"
                            name="scheduled_date"
                            value="{{ old('scheduled_date') }}"
                        >

                        <label for="frequency">Frequency</label>
                        <select id="frequency" name="frequency">
                            <option value="Once" {{ old('frequency', 'Once') === 'Once' ? 'selected' : '' }}>Once</option>
                            <option value="Daily" {{ old('frequency') === 'Daily' ? 'selected' : '' }}>Daily</option>
                            <option value="Weekly" {{ old('frequency') === 'Weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="Monthly" {{ old('frequency') === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                </div>

                <label for="amount">Amount</label>
                <input
                    type="number"
                    step="0.01"
                    min="1"
                    id="amount"
                    name="amount"
                    value="{{ old('amount') }}"
                    placeholder="Enter amount"
                    required
                >

                <label for="description">Remarks / Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Add a note for this transfer"
                >{{ old('description') }}</textarea>

                <button type="submit" class="submit-btn">
                    {{ old('is_scheduled_transfer') ? 'Schedule Transfer' : 'Send Transfer' }}
                </button>
            </form>
        </section>

        <section>
            @php
                $firstAccount = $customer['accounts'][0] ?? null;
            @endphp

            @if($firstAccount)
                <div class="info-card">
                    <h4>Primary Banking Profile</h4>
                    <div class="balance">${{ number_format((float)($firstAccount['balance'] ?? 0), 2) }}</div>
                    <div class="meta">Account Number: {{ $firstAccount['accountNumber'] ?? '' }}</div>
                    <div class="meta">Account Type: {{ $firstAccount['accountType'] ?? '' }}</div>
                </div>
            @endif

            <div class="transfer-summary">
                <h4>Transfer Summary</h4>
                <div class="summary-row">
                    <span>Supported Modes</span>
                    <strong>Internal + Local Bank</strong>
                </div>
                <div class="summary-row">
                    <span>Execution</span>
                    <strong>Instant or Scheduled</strong>
                </div>
                <div class="summary-row">
                    <span>Reference</span>
                    <strong>Auto-generated</strong>
                </div>
            </div>

            <div class="tips-box">
                <h4>Transfer Tips</h4>
                <p>Use internal transfer for BoF-to-BoF account movement.</p>
                <p>Use local bank transfer for M-Paisa, MyCash, BSP, ANZ, Westpac, and BRED.</p>
                <p>You can select a saved beneficiary to automatically fill transfer details.</p>
                <p>Use scheduled transfer when you want funds sent on a future date.</p>
                <p>Always confirm the institution, account number, and beneficiary name before submitting.</p>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    const internalRadio = document.querySelector('input[name="transfer_mode"][value="internal"]');
    const localBankRadio = document.querySelector('input[name="transfer_mode"][value="local_bank"]');

    const internalFields = document.getElementById('internal-transfer-fields');
    const localBankFields = document.getElementById('local-bank-fields');

    const toAccountNumber = document.getElementById('to_account_number');
    const destinationInstitution = document.getElementById('destination_institution_id');
    const destinationAccountNumber = document.getElementById('destination_account_number');
    const beneficiaryName = document.getElementById('beneficiary_name');
    const destinationAccountLabel = document.getElementById('destination-account-label');
    const institutionHelpText = document.getElementById('institution-help-text');
    const savedBeneficiary = document.getElementById('saved_beneficiary');

    const scheduleCheckbox = document.getElementById('is_scheduled_transfer');
    const scheduleFields = document.getElementById('scheduled-transfer-fields');
    const scheduledDate = document.getElementById('scheduled_date');
    const frequency = document.getElementById('frequency');

    function toggleTransferMode() {
        const isInternal = internalRadio.checked;

        if (isInternal) {
            internalFields.classList.remove('hidden-field');
            localBankFields.classList.add('hidden-field');

            toAccountNumber.required = true;
            destinationInstitution.required = false;
            destinationAccountNumber.required = false;
            beneficiaryName.required = false;
        } else {
            internalFields.classList.add('hidden-field');
            localBankFields.classList.remove('hidden-field');

            toAccountNumber.required = false;
            destinationInstitution.required = true;
            destinationAccountNumber.required = true;
            beneficiaryName.required = true;
        }
    }

    function updateInstitutionFields() {
        if (!destinationInstitution) return;

        const selected = destinationInstitution.options[destinationInstitution.selectedIndex];

        if (!selected || !selected.value) {
            destinationAccountLabel.textContent = 'Account Number';
            destinationAccountNumber.placeholder = 'Enter destination account number';
            institutionHelpText.textContent = 'Use this section for transfers to local banks and wallet providers.';
            return;
        }

        const label = selected.getAttribute('data-label') || 'Account Number';
        const placeholder = selected.getAttribute('data-placeholder') || 'Enter destination account number';
        const name = selected.getAttribute('data-name') || 'institution';
        const category = selected.getAttribute('data-category') || '';

        destinationAccountLabel.textContent = label;
        destinationAccountNumber.placeholder = placeholder;
        institutionHelpText.textContent = 'You are sending to ' + name + (category ? ' (' + category + ')' : '') + '.';
    }

    function applySavedBeneficiary() {
        if (!savedBeneficiary || !savedBeneficiary.value) return;

        const selected = savedBeneficiary.options[savedBeneficiary.selectedIndex];
        const mode = selected.getAttribute('data-mode') || '';
        const name = selected.getAttribute('data-name') || '';
        const institution = selected.getAttribute('data-institution') || '';
        const account = selected.getAttribute('data-account') || '';

        if (mode === 'internal') {
            if (internalRadio) {
                internalRadio.checked = true;
                toggleTransferMode();
            }

            if (toAccountNumber) {
                toAccountNumber.value = account;
            }
        } else if (mode === 'local_bank') {
            if (localBankRadio) {
                localBankRadio.checked = true;
                toggleTransferMode();
            }

            if (destinationAccountNumber) {
                destinationAccountNumber.value = account;
            }

            if (beneficiaryName) {
                beneficiaryName.value = name;
            }

            if (destinationInstitution) {
                let matched = false;

                for (let i = 0; i < destinationInstitution.options.length; i++) {
                    const optionText = destinationInstitution.options[i].text.toLowerCase();
                    const optionName = (destinationInstitution.options[i].getAttribute('data-name') || '').toLowerCase();

                    if (
                        optionText.includes(institution.toLowerCase()) ||
                        optionName.includes(institution.toLowerCase())
                    ) {
                        destinationInstitution.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }

                if (matched) {
                    updateInstitutionFields();
                }
            }
        }
    }

    function toggleScheduleFields() {
        if (!scheduleCheckbox || !scheduleFields) return;

        if (scheduleCheckbox.checked) {
            scheduleFields.classList.remove('hidden-field');
            scheduledDate.required = true;
            frequency.required = true;
        } else {
            scheduleFields.classList.add('hidden-field');
            scheduledDate.required = false;
            frequency.required = false;
        }
    }

    if (internalRadio && localBankRadio) {
        internalRadio.addEventListener('change', toggleTransferMode);
        localBankRadio.addEventListener('change', toggleTransferMode);
        toggleTransferMode();
    }

    if (destinationInstitution) {
        destinationInstitution.addEventListener('change', updateInstitutionFields);
        updateInstitutionFields();
    }

    if (savedBeneficiary) {
        savedBeneficiary.addEventListener('change', applySavedBeneficiary);
    }

    if (scheduleCheckbox) {
        scheduleCheckbox.addEventListener('change', toggleScheduleFields);
        toggleScheduleFields();
    }
</script>
@endpush