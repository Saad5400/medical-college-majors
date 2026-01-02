<footer
    style="
        margin: 0.75rem 0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        color: #6b7280;
        text-align: center;
        padding: {{ ($footerPosition === 'sidebar' || $footerPosition === 'sidebar.footer' || $borderTopEnabled === true) ? '0.5rem' : '0' }};
        border-top: {{ ($footerPosition === 'sidebar' || $footerPosition === 'sidebar.footer' || $borderTopEnabled === true) ? '1px solid #e5e7eb' : 'none' }};
        gap: {{ ($footerPosition === 'sidebar' || $footerPosition === 'sidebar.footer') ? '0.5rem' : '1rem' }};
        width: {{ $footerPosition === 'footer' ? '100%' : 'auto' }};
        margin-left: {{ $footerPosition === 'footer' ? 'auto' : '0' }};
        margin-right: {{ $footerPosition === 'footer' ? 'auto' : '0' }};
        padding-left: {{ $footerPosition === 'footer' ? '1rem' : '0' }};
        padding-right: {{ $footerPosition === 'footer' ? '1rem' : '0' }};
    "
    dir="ltr"
>
    <span
        style="
            display: {{ $isHtmlSentence ? 'flex' : 'inline' }};
            gap: {{ $isHtmlSentence ? '0.5rem' : '0' }};
        "
    >
        &copy; {{ now()->format('Y') }} -
        @if($sentence)
            @if($isHtmlSentence)
                <span style="display: flex;">
                    {!! $sentence !!}
                </span>
            @else
                {{ $sentence }}
            @endif
        @else
            {{ config('filament-easy-footer.app_name') }}
        @endif
    </span>

    @if($githubEnabled)
        <livewire:devonab.filament-easy-footer.github-version
            :show-logo="$showLogo"
            :show-url="$showUrl"
        />
    @endif

    @if($logoPath)
        <span style="display: flex; align-items: center;">
            @if($logoUrl)
                <a href="{{ $logoUrl }}" style="display: inline-flex;">
            @endif

            <img
                src="{{ $logoPath }}"
                alt="Logo"
                style="
                    height: {{ $logoHeight }}px;
                    width: auto;
                    object-fit: contain;
                "
            >

            @if($logoUrl)
                </a>
            @endif
        </span>
    @endif

    @if($loadTime)
        <span
            style="{{ ($footerPosition === 'sidebar' || $footerPosition === 'sidebar.footer') ? 'width: 100%;' : '' }}">
            {{ $loadTimePrefix ?? '' }} {{ $loadTime }}s
        </span>
    @endif

    @if(count($links) > 0)
        <ul
            style="
                display: flex;
                gap: 0.5rem;
                list-style: none;
                padding: 0;
                margin: 0;
            "
        >
            @foreach($links as $link)
                <li>
                    <a
                        href="{{ $link['url'] }}"
                        target="_blank"
                        style="
                            color: #2563eb;
                            text-decoration: none;
                        "
                        onmouseover="this.style.textDecoration='underline'"
                        onmouseout="this.style.textDecoration='none'"
                    >
                        {{ $link['title'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</footer>
