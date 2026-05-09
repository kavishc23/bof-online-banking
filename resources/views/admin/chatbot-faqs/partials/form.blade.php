@php
    $keywords = $faq['keywords'] ?? [];
    if (is_string($keywords)) {
        $decodedKeywords = json_decode($keywords, true);
        $keywords = is_array($decodedKeywords) ? $decodedKeywords : [$keywords];
    }
@endphp

<div class="admin-field full">
    <label for="question">Question</label>
    <input id="question" name="question" value="{{ old('question', $faq['question'] ?? '') }}" maxlength="255" required>
</div>

<div class="admin-field full">
    <label for="keywords">Keywords</label>
    <input id="keywords" name="keywords" value="{{ old('keywords', implode(', ', (array) $keywords)) }}" maxlength="2000" required>
</div>

<div class="admin-field full">
    <label for="answer">Answer</label>
    <textarea id="answer" name="answer" rows="6" maxlength="5000" required>{{ old('answer', $faq['answer'] ?? '') }}</textarea>
</div>

<div class="admin-field">
    <label for="category">Category</label>
    <input id="category" name="category" value="{{ old('category', $faq['category'] ?? '') }}" maxlength="100" required>
</div>

<div class="admin-field">
    <label for="isActive">Status</label>
    <select id="isActive" name="isActive">
        <option value="1" @selected((bool) old('isActive', $faq['isActive'] ?? true) === true)>Active</option>
        <option value="0" @selected((string) old('isActive', $faq['isActive'] ?? true) === '0')>Inactive</option>
    </select>
</div>

