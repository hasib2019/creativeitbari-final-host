{{-- Shared fields for contact / quote / order forms. Honeypot "website" must stay empty. --}}
@props(['messageRequired' => true, 'phoneRequired' => false, 'messageLabel' => 'Message'])
@csrf
<input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="f-name" class="label">Full name <span class="text-rose-500">*</span></label>
        <input id="f-name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" class="input">
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="f-email" class="label">Email <span class="text-rose-500">*</span></label>
        <input id="f-email" type="email" name="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email" class="input">
        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="f-phone" class="label">Phone / WhatsApp @if ($phoneRequired)<span class="text-rose-500">*</span>@endif</label>
        <input id="f-phone" type="tel" name="phone" value="{{ old('phone') }}" @required($phoneRequired) maxlength="30" autocomplete="tel" class="input" placeholder="01XXXXXXXXX">
        @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="f-company" class="label">Company</label>
        <input id="f-company" name="company" value="{{ old('company') }}" maxlength="150" autocomplete="organization" class="input">
    </div>
    {{ $slot }}
    <div class="sm:col-span-2">
        <label for="f-message" class="label">{{ $messageLabel }} @if ($messageRequired)<span class="text-rose-500">*</span>@endif</label>
        <textarea id="f-message" name="message" rows="5" @required($messageRequired) maxlength="5000" class="input">{{ old('message') }}</textarea>
        @error('message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>
