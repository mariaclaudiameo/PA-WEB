<?php
/**
 * Breadcrumb Component — Kebun Ndesa
 * 
 * Cara pakai:
 *   $breadcrumbs = [
 *     ['label' => 'Beranda', 'url' => '/index.php'],
 *     ['label' => 'Wisata',  'url' => '/pages/wisata.php'],
 *     ['label' => 'Kolam Renang'], // halaman aktif, tanpa url
 *   ];
 *   include __DIR__ . '/../includes/breadcrumb.php';
 * 
 * Atau pakai helper di bawah:
 *   breadcrumb([...]);
 */

function breadcrumb(array $items, string $theme = 'light'): void {
    // theme: 'light' (bg putih/cream) | 'dark' (bg forest/gelap) | 'transparent' (di atas hero)
    $isDark = $theme === 'dark' || $theme === 'transparent';
    $isTransparent = $theme === 'transparent';
    ?>
    <nav class="breadcrumb-nav breadcrumb-<?= $theme ?>" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <?php foreach ($items as $i => $item):
                $isLast = ($i === count($items) - 1);
            ?>
            <li class="breadcrumb-item <?= $isLast ? 'breadcrumb-active' : '' ?>">
                <?php if (!$isLast && !empty($item['url'])): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" class="breadcrumb-link">
                        <?php if ($i === 0): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="margin-right:4px;vertical-align:middle;margin-top:-2px;">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                            </svg>
                        <?php endif; ?>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                    <span class="breadcrumb-sep" aria-hidden="true">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </span>
                <?php else: ?>
                    <span class="breadcrumb-current" aria-current="page">
                        <?= htmlspecialchars($item['label']) ?>
                    </span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <style>
    .breadcrumb-nav {
        padding: 12px 0;
        font-size: 12px;
        font-family: 'Jost', sans-serif;
        letter-spacing: .04em;
    }
    .breadcrumb-list {
        list-style: none;
        margin: 0; padding: 0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 2px;
    }
    .breadcrumb-item {
        display: flex;
        align-items: center;
        gap: 2px;
    }
    .breadcrumb-link {
        text-decoration: none;
        font-weight: 400;
        transition: color .2s, opacity .2s;
        display: flex;
        align-items: center;
    }
    .breadcrumb-sep {
        display: flex;
        align-items: center;
        margin: 0 2px;
    }
    .breadcrumb-current {
        font-weight: 500;
    }

    
    .breadcrumb-light .breadcrumb-link   { color: var(--text-muted, #888); }
    .breadcrumb-light .breadcrumb-link:hover { color: var(--forest, #1e3a2f); opacity: 1; }
    .breadcrumb-light .breadcrumb-sep    { color: rgba(0,0,0,.25); }
    .breadcrumb-light .breadcrumb-current{ color: var(--forest, #1e3a2f); }

    
    .breadcrumb-dark .breadcrumb-link    { color: rgba(247,243,237,.5); }
    .breadcrumb-dark .breadcrumb-link:hover { color: var(--gold, #c9a96e); opacity: 1; }
    .breadcrumb-dark .breadcrumb-sep     { color: rgba(247,243,237,.2); }
    .breadcrumb-dark .breadcrumb-current { color: var(--gold, #c9a96e); }

    
    .breadcrumb-transparent {
        position: absolute;
        top: 90px;
        left: 8vw;
        z-index: 10;
    }
    .breadcrumb-transparent .breadcrumb-link    { color: rgba(255,255,255,.6); }
    .breadcrumb-transparent .breadcrumb-link:hover { color: #fff; }
    .breadcrumb-transparent .breadcrumb-sep     { color: rgba(255,255,255,.3); }
    .breadcrumb-transparent .breadcrumb-current { color: rgba(255,255,255,.9); }

    @media(max-width:768px) {
        .breadcrumb-transparent { top: 72px; left: 5vw; }
        .breadcrumb-nav { font-size: 11px; }
    }
    </style>
    <?php
}
?>