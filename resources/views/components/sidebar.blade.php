<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="index.html">CURUG PINANG</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="index.html">CURUG PINANG</a>
        </div>
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-fire"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item ">
                <a href="{{ route('users.index') }}" class="nav-link "><i class="fas fa-columns"></i>
                    <span>Pengguna</span></a>
            </li>

            <li class="nav-item ">
                <a href="{{ route('categories.index') }}" class="nav-link "><i class="fas fa-columns"></i>
                    <span>Kategori</span></a>
            </li>

            <li class="nav-item ">
                <a href="{{ route('products.index') }}" class="nav-link "><i class="fas fa-columns"></i>
                    <span>Tiket</span></a>
            </li>

            <li class="nav-item ">
                <a href="{{ route('transaksi.index') }}" class="nav-link "><i class="fas fa-columns"></i>
                    <span>Transaksi</span></a>
            </li>

    </aside>
</div>
