<x-mail::message>
    # Role Request Answered


    @if ($roleRequest->status == 'approved')
    Your role request has been approved. You have a new role in our application.
    @else
    Your role request has been denied. We are sorry to inform you that you are not eligible to get
    a new role in our application.
    @endif

    Thanks, {{ config('app.name') }}

    <x-mail::button :url="route('/')">
        Visit Our Application
    </x-mail::button>
</x-mail::message>