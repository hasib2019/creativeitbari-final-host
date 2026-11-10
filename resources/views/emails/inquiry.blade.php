<x-mail::message>
# New {{ \App\Models\Inquiry::TYPES[$inquiry->type] ?? 'Inquiry' }}

<x-mail::table>
| Field | Value |
|:--|:--|
| Name | {{ $inquiry->name }} |
| Email | {{ $inquiry->email }} |
@if ($inquiry->phone)
| Phone | {{ $inquiry->phone }} |
@endif
@if ($inquiry->company)
| Company | {{ $inquiry->company }} |
@endif
@if ($inquiry->item_name)
| Item | {{ $inquiry->item_name }} |
@endif
@if ($inquiry->budget)
| Budget | {{ $inquiry->budget }} |
@endif
@if ($inquiry->subject)
| Subject | {{ $inquiry->subject }} |
@endif
</x-mail::table>

@if ($inquiry->message)
**Message**

{{ $inquiry->message }}
@endif

<x-mail::button :url="url('/admin')">
Open Admin Panel
</x-mail::button>
</x-mail::message>
