<x-mail::message>
    # Welcome, {{ $user->name }}!

    Thank you for registering with our application. We're excited to have you on board.

    Your account details are:

    - Email: {{ $user->email }}

    <x-mail::button :url="route('/')">
        Visit Our Application
    </x-mail::button>


</x-mail::message>