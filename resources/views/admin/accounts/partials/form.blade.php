@if($isCreate)
    <div class="admin-field">
        <label for="accountNumber">Account Number</label>
        <input id="accountNumber" name="accountNumber" value="{{ old('accountNumber', $account['accountNumber'] ?? '') }}" required>
    </div>
@endif

<div class="admin-field">
    <label for="accountType">Account Type</label>
    <select id="accountType" name="accountType" required>
        @foreach(['SimpleAccess', 'Savings', 'Business'] as $type)
            <option value="{{ $type }}" @selected(old('accountType', $account['accountType'] ?? '') === $type)>{{ $type }}</option>
        @endforeach
    </select>
</div>

<div class="admin-field">
    <label for="balance">Balance</label>
    <input id="balance" name="balance" type="number" step="0.01" min="0" value="{{ old('balance', $account['balance'] ?? 0) }}" required>
</div>

<div class="admin-field">
    <label for="monthlyMaintenanceFee">Monthly Maintenance Fee</label>
    <input id="monthlyMaintenanceFee" name="monthlyMaintenanceFee" type="number" step="0.01" min="0" value="{{ old('monthlyMaintenanceFee', $account['monthlyMaintenanceFee'] ?? 0) }}">
</div>

<div class="admin-field">
    <label for="interestRate">Interest Rate</label>
    <input id="interestRate" name="interestRate" type="number" step="0.0001" min="0" value="{{ old('interestRate', $account['interestRate'] ?? 0) }}">
</div>

@if($isCreate)
    <div class="admin-field">
        <label for="openedAt">Opened At</label>
        <input id="openedAt" name="openedAt" type="date" value="{{ old('openedAt') }}">
    </div>

    <div class="admin-field">
        <label for="customer">Customer ID</label>
        <input id="customer" name="customer" type="number" min="1" value="{{ old('customer') }}">
    </div>
@endif

