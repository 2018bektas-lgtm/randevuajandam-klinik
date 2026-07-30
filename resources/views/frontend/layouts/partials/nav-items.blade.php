{{--
  Ortak menü satırları (hiyerarşili).
  $mode = 'desktop' | 'mobile' | 'list' | 'delogis'
  $nav  = site_nav() sonucu
--}}
@php
    $mode = $mode ?? 'desktop';
    $nav = $nav ?? (function_exists('site_nav') ? site_nav(isset($doktor) && is_array($doktor) ? $doktor : null) : []);
@endphp

@if($mode === 'delogis')
    @foreach ($nav as $item)
        @php
            $children = $item['children'] ?? [];
            $active = ! empty($item['match']) && request()->routeIs($item['match']);
            if (! $active && $children) {
                foreach ($children as $ch) {
                    if (! empty($ch['match']) && request()->routeIs($ch['match'])) { $active = true; break; }
                }
            }
        @endphp
        <li class="{{ $active ? 'current' : '' }}{{ ! empty($children) ? ' dropdown' : '' }}">
            <a href="{{ $item['href'] }}"
               @if(!empty($item['external'])) target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a>
            @if(! empty($children))
                <ul>
                    @foreach($children as $child)
                        @php $cActive = ! empty($child['match']) && request()->routeIs($child['match']); @endphp
                        <li class="{{ $cActive ? 'current' : '' }}">
                            <a href="{{ $child['href'] }}"
                               @if(!empty($child['external'])) target="_blank" rel="noopener" @endif>{{ $child['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
@elseif($mode === 'list')
    @foreach ($nav as $item)
        @php
            $children = $item['children'] ?? [];
            $active = ! empty($item['match']) && request()->routeIs($item['match']);
        @endphp
        <li class="nav-item {{ ! empty($children) ? 'dropdown' : '' }} {{ $active ? 'active' : '' }}">
            <a class="nav-link" href="{{ $item['href'] }}"
               @if(!empty($item['external'])) target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a>
            @if(! empty($children))
                <ul class="sub-menu">
                    @foreach($children as $child)
                        <li><a href="{{ $child['href'] }}"
                               @if(!empty($child['external'])) target="_blank" rel="noopener" @endif>{{ $child['label'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
@elseif($mode === 'mobile')
    @foreach ($nav as $item)
        @php
            $children = $item['children'] ?? [];
            $active = !empty($item['match']) && request()->routeIs($item['match']);
        @endphp
        <a href="{{ $item['href'] }}"
           class="{{ $active ? 'active' : '' }}"
           @if(!empty($item['external'])) target="_blank" rel="noopener" @endif>
            {{ $item['label'] }}
        </a>
        @foreach($children as $child)
            @php $cActive = !empty($child['match']) && request()->routeIs($child['match']); @endphp
            <a href="{{ $child['href'] }}"
               class="mobile-nav-child {{ $cActive ? 'active' : '' }}"
               @if(!empty($child['external'])) target="_blank" rel="noopener" @endif>
                {{ $child['label'] }}
            </a>
        @endforeach
    @endforeach
@else
    {{-- desktop links / dropdown --}}
    @foreach ($nav as $item)
        @php
            $children = $item['children'] ?? [];
            $active = !empty($item['match']) && request()->routeIs($item['match']);
            if (! $active && $children) {
                foreach ($children as $ch) {
                    if (!empty($ch['match']) && request()->routeIs($ch['match'])) { $active = true; break; }
                }
            }
        @endphp
        @if(!empty($children))
            <div class="nav-item has-dropdown {{ $active ? 'active' : '' }}">
                <a href="{{ $item['href'] }}"
                   class="{{ $active ? 'active' : '' }}"
                   @if(!empty($item['external'])) target="_blank" rel="noopener" @endif>
                    {{ $item['label'] }}
                    <span class="nav-caret" aria-hidden="true">▾</span>
                </a>
                <div class="nav-dropdown" role="menu">
                    @foreach($children as $child)
                        @php $cActive = !empty($child['match']) && request()->routeIs($child['match']); @endphp
                        <a href="{{ $child['href'] }}"
                           class="{{ $cActive ? 'active' : '' }}"
                           role="menuitem"
                           @if(!empty($child['external'])) target="_blank" rel="noopener" @endif>
                            {{ $child['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <a href="{{ $item['href'] }}"
               class="{{ $active ? 'active' : '' }}"
               @if(!empty($item['external'])) target="_blank" rel="noopener" @endif>
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
@endif
