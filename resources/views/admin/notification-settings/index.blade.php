@extends('admin.layout')

@section('heading', 'Notification Settings')
@section('subheading', 'Enable or disable configured banking notification events.')

@section('admin-content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.notification-settings.update') }}">
            @csrf
            @method('PATCH')

            @foreach($settings as $setting)
                @if(in_array($setting['eventKey'] ?? '', $allowedEventKeys, true))
                    <div class="admin-toggle-row">
                        <div>
                            <strong>{{ $setting['eventLabel'] ?? ucwords(str_replace('_', ' ', $setting['eventKey'] ?? 'Notification')) }}</strong>
                            <div style="color: var(--admin-soft); margin-top: 5px;">{{ $setting['description'] ?? $setting['eventKey'] }}</div>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="settings[{{ $setting['eventKey'] }}]" value="0">
                            <input type="checkbox" name="settings[{{ $setting['eventKey'] }}]" value="1" @checked((bool) ($setting['enabled'] ?? false))>
                        </label>
                    </div>
                @endif
            @endforeach

            <div class="admin-actions" style="margin-top: 20px;">
                <button class="admin-btn" type="submit">Save Settings</button>
            </div>
        </form>
    </section>
@endsection

