<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="index.html">POS KASIR</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="index.html">PK</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            {{-- <li class="nav-item dropdown">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <ul class="dropdown-menu">
                    <li class='{{ Request::is('dashboard-general-dashboard') ? 'active' : '' }}'>
                        <a class="nav-link" href="{{ url('dashboard-general-dashboard') }}">General Dashboard</a>
                    </li>
                </ul>
            </li> --}}

            <!-- Home Menu Item -->
            <li class="nav-item">
                <a href="{{ url('/home') }}" class="nav-link"><i class="fas fa-home"></i><span>Home</span></a>
            </li>

            <li class="nav-item">
                <a href="{{ route('user.index') }}" class="nav-link "><i class="fas fa-users"></i><span>Users</span></a>
            </li>

            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link "><i class="fas fa-th-list"></i><span>Categories</span></a>
            </li>

            <li class="nav-item">
                <a href="{{ route('product.index') }}" class="nav-link"><i class="fas fa-cogs"></i><span>Products</span></a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="nav-link" href="{{ route('product.index') }}">All Products</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="{{ route('order.index') }}" class="nav-link "><i class="fas fa-box"></i><span>Orders</span></a>
            </li>

        </ul>
    </aside>
</div>
