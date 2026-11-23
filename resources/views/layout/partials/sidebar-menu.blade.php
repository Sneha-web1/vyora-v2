{{--
    Recursive sidebar renderer (data comes from App\Support\MenuRegistry::treeFor()).
    $nodes = nested menu arrays, $level = 0 (top) | 1 | 2
--}}
@foreach ($nodes as $node)
    @php
        $hasChildren = !empty($node['children']);
        $active = \App\Support\MenuRegistry::isActive($node);
        $url = \App\Support\MenuRegistry::url($node['route'] ?? null);
    @endphp

    @if ($hasChildren)
        <li class="submenu {{ $level >= 1 ? 'submenu-two' : '' }}">
            <a href="javascript:void(0);" class="{{ $active ? 'subdrop active' : '' }}">
                @if ($level === 0)
                    <i class="{{ $node['icon'] ?: 'ti ti-point' }}"></i> <span>{{ $node['title'] }}</span>
                    <span class="menu-arrow"></span>
                @else
                    {{ $node['title'] }} <span class="menu-arrow inside-submenu"></span>
                @endif
            </a>
            <ul style="{{ $active ? 'display: block;' : '' }}">
                @include('layout.partials.sidebar-menu', ['nodes' => $node['children'], 'level' => $level + 1])
            </ul>
        </li>
    @else
        <li>
            <a href="{{ $url }}" class="{{ $active ? 'active' : '' }}">
                @if ($level === 0)
                    <i class="{{ $node['icon'] ?: 'ti ti-point' }}"></i><span>{{ $node['title'] }}</span>
                @else
                    {{ $node['title'] }}
                @endif
            </a>
        </li>
    @endif
@endforeach
