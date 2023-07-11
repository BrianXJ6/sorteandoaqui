<x-mail::message>
# {{ __('mails.web_contact.Introduction') }}

{{ __('mails.web_contact.message01') }}

<x-mail::panel>
**{{ __('mails.web_contact.labels.subject') }}:** {{ $data->subject }};<br>
**{{ __('mails.web_contact.labels.name') }}:** {{ $data->name }};<br>
**{{ __('mails.web_contact.labels.email') }}:** {{ $data->email }};<br>
**{{ __('mails.web_contact.labels.phone') }}:** {{ $data->phone }}.
<hr>

**{{ __('mails.web_contact.labels.message') }}:** {{ $data->message }}
</x-mail::panel>

{{ __('mails.web_contact.finalization') }},<br>
{{ config('app.name') }}
</x-mail::message>
