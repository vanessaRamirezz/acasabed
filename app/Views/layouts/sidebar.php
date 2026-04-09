<!-- Main Sidebar Container NAVEGADOR DEL LADO IZQUIERDO, MENU PRINCIPAL-->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= base_url('inicio') ?>" class="brand-link">
        <img src="<?= base_url('dist/img/agua.png') ?>" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">ACASABED</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <?php $nombres = session('nombres'); ?>
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div class="info">
                <a class="d-block"><?= esc($nombres) ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            $accesos = session('accesos');
            $current = service('uri')->getSegment(1);

            $menus = [];

            foreach ($accesos as $acceso) {

                // ignorar los que contengan NO_AGRUPAR
                if (!empty($acceso['agrupacion']) && strpos($acceso['agrupacion'], 'NO_AGRUPAR') !== false) {
                    continue;
                }

                $grupo = $acceso['agrupacion'] ?? 'SIMPLE';
                $menus[$grupo][] = $acceso;
            }

            // foreach ($accesos as $acceso) {
            //     $grupo = $acceso['agrupacion'] ?? 'SIMPLE';
            //     $menus[$grupo][] = $acceso;
            // }
            ?>

            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <?php foreach ($menus as $grupo => $items): ?>

                    <?php if ($grupo != 'SIMPLE'): ?>

                        <?php
                        $urls = array_column($items, 'url_acceso');
                        $menuOpen = in_array($current, $urls);
                        ?>

                        <li class="nav-item has-treeview <?= $menuOpen ? 'menu-open' : '' ?>">

                            <a href="#" class="nav-link <?= $menuOpen ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-folder"></i>
                                <p>
                                    <?= $grupo ?>
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">

                                <?php foreach ($items as $item): ?>

                                    <?php $active = ($current == $item['url_acceso']); ?>

                                    <li class="nav-item">
                                        <a href="<?= base_url($item['url_acceso']) ?>" class="nav-link <?= $active ? 'active' : '' ?>">
                                            <i class="nav-icon <?= $item['icono'] ?>"></i>
                                            <p><?= $item['acceso'] ?></p>
                                        </a>
                                    </li>

                                <?php endforeach; ?>

                            </ul>

                        </li>

                    <?php else: ?>

                        <?php foreach ($items as $item): ?>

                            <?php $active = ($current == $item['url_acceso']); ?>

                            <li class="nav-item">
                                <a href="<?= base_url($item['url_acceso']) ?>" class="nav-link <?= $active ? 'active' : '' ?>">
                                    <i class="nav-icon <?= $item['icono'] ?>"></i>
                                    <p><?= $item['acceso'] ?></p>
                                </a>
                            </li>

                        <?php endforeach; ?>

                    <?php endif; ?>

                <?php endforeach; ?>

            </ul>

        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>