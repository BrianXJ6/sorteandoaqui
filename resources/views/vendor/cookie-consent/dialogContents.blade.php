<div class="js-cookie-consent cookie-consent fixed-bottom bg-gradient text-bg-primary text-center shadow-inverse">
    <div class="container py-4">
        <p class="m-0">{!! __('messages.cookie.message') !!}</p>
        <p>
            <a class="link-dark" href="{{ asset('storage/docs/cookies.pdf') }}" target="__blank" title="{{ __('messages.cookie.link_title') }}">
                {{ __('messages.cookie.doc') }}
            </a>
        </p>
        <button class="js-cookie-consent-agree btn btn-dark px-5 shadow" title="{{ __('messages.cookie.agree') }}">
            {{ __('messages.cookie.agree') }}
        </button>
    </div>
</div>
