@extends('admin.layout')

@section('heading', 'Chatbot FAQs')
@section('subheading', 'Manage active answers used by the BoF Virtual Assistant.')
@section('actions')
    <a href="{{ route('admin.chatbot-faqs.create') }}" class="admin-btn">Create FAQ</a>
@endsection

@section('admin-content')
    <section class="admin-card">
        <form method="GET" action="{{ route('admin.chatbot-faqs.index') }}" class="admin-form-grid">
            <div class="admin-field">
                <label for="search">Search</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Question or keyword">
            </div>
            <div class="admin-field">
                <label for="category">Category</label>
                <input id="category" name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Accounts">
            </div>
            <div class="admin-field">
                <label for="isActive">Status</label>
                <select id="isActive" name="isActive">
                    <option value="">All statuses</option>
                    <option value="1" @selected((string) ($filters['isActive'] ?? '') === '1')>Active</option>
                    <option value="0" @selected((string) ($filters['isActive'] ?? '') === '0')>Inactive</option>
                </select>
            </div>
            <div class="admin-actions">
                <button class="admin-btn" type="submit">Apply Filters</button>
                <a class="admin-btn secondary" href="{{ route('admin.chatbot-faqs.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Keywords</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                        @php
                            $keywords = $faq['keywords'] ?? [];
                            if (is_string($keywords)) {
                                $decodedKeywords = json_decode($keywords, true);
                                $keywords = is_array($decodedKeywords) ? $decodedKeywords : [$keywords];
                            }
                        @endphp
                        <tr>
                            <td>{{ $faq['question'] ?? '-' }}</td>
                            <td>{{ $faq['category'] ?? '-' }}</td>
                            <td>
                                <span class="admin-badge {{ !empty($faq['isActive']) ? 'admin-badge-green' : 'admin-badge-slate' }}">
                                    {{ !empty($faq['isActive']) ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-chip-list">
                                    @forelse(array_slice((array) $keywords, 0, 4) as $keyword)
                                        <span class="admin-badge admin-badge-blue">{{ $keyword }}</span>
                                    @empty
                                        -
                                    @endforelse
                                    @if(count((array) $keywords) > 4)
                                        <span class="admin-badge admin-badge-slate">+{{ count((array) $keywords) - 4 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td><a class="admin-btn secondary" href="{{ route('admin.chatbot-faqs.edit', $faq['documentId'] ?? $faq['id']) }}">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No chatbot FAQs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
