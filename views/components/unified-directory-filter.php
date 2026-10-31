<?php

if (!function_exists('renderCollabModeTabs')) {

    function renderCollabModeTabs(string $activePage, ?int $idLomba = null): void
    {
        $lombaQuery    = ($idLomba !== null && $idLomba > 0) ? '&id_lomba=' . $idLomba : '';
        $isTeamPosts   = $activePage === 'team-posts';
        $isCariPartner = $activePage === 'cari-partner';
        ?>
        <div class="collab-mode-tabs" role="navigation" aria-label="Mode Pencarian Partner dan Tim">
            <a
                href="<?= e(url('?page=team-posts' . $lombaQuery)) ?>"
                class="collab-mode-tab <?= $isTeamPosts ? 'is-active' : '' ?>"
                <?= $isTeamPosts ? 'aria-current="page"' : '' ?>
            >
                <span>Posting Pencarian Tim</span>
            </a>
            <a
                href="<?= e(url('?page=cari-partner' . $lombaQuery)) ?>"
                class="collab-mode-tab <?= $isCariPartner ? 'is-active' : '' ?>"
                <?= $isCariPartner ? 'aria-current="page"' : '' ?>
            >
                <span>Direktori Kandidat Partner</span>
            </a>
        </div>
        <?php
    }
}

if (!function_exists('renderCategoryPillsRow')) {

    function renderCategoryPillsRow(array $config = []): void
    {
        $pills        = !empty($config['pills']) && is_array($config['pills']) ? $config['pills'] : getCategoryPills();
        $activeBidang = (string) ($config['activeBidang'] ?? 'Semua Bidang');
        $ariaLabel    = (string) ($config['ariaLabel'] ?? 'Filter Kategori Bidang');
        $containerId  = (string) ($config['containerId'] ?? '');
        $extraStyle   = (string) ($config['extraStyle'] ?? 'margin-top: 1.35rem;');
        $buttonClass  = trim((string) ($config['buttonClass'] ?? ''));
        $idPrefix     = trim((string) ($config['idPrefix'] ?? ''));
        $ariaControls = trim((string) ($config['ariaControls'] ?? ''));
        $hrefBuilder  = isset($config['hrefBuilder']) && is_callable($config['hrefBuilder']) ? $config['hrefBuilder'] : null;
        ?>
        <div
            class="dash-pills-row"
            <?= $containerId !== '' ? 'id="' . e($containerId) . '"' : '' ?>
            role="tablist"
            aria-label="<?= e($ariaLabel) ?>"
            <?= $extraStyle !== '' ? 'style="' . e($extraStyle) . '"' : '' ?>
        >
            <?php foreach ($pills as $idx => $pill):
                $pillStr      = (string) $pill;
                $isActivePill = strcasecmp($activeBidang, $pillStr) === 0 || ($activeBidang === '' && $idx === 0);
                $fullBtnClass = 'dash-pill-btn'
                    . ($buttonClass !== '' ? ' ' . $buttonClass : '')
                    . ($isActivePill ? ' is-active' : '');
                $btnIdAttr    = $idPrefix !== '' ? 'id="' . e($idPrefix . (int) $idx) . '"' : '';
                $controlsAttr = $ariaControls !== '' ? 'aria-controls="' . e($ariaControls) . '"' : '';
            ?>
                <?php if ($hrefBuilder !== null): ?>
                    <a
                        href="<?= e((string) $hrefBuilder($pillStr)) ?>"
                        <?= $btnIdAttr ?>
                        role="tab"
                        aria-selected="<?= $isActivePill ? 'true' : 'false' ?>"
                        <?= $controlsAttr ?>
                        class="<?= e($fullBtnClass) ?>"
                    >
                        <?= e($pillStr) ?>
                    </a>
                <?php else: ?>
                    <button
                        type="button"
                        <?= $btnIdAttr ?>
                        role="tab"
                        aria-selected="<?= $isActivePill ? 'true' : 'false' ?>"
                        <?= $controlsAttr ?>
                        tabindex="<?= $isActivePill ? '0' : '-1' ?>"
                        class="<?= e($fullBtnClass) ?>"
                        data-bidang="<?= e($pillStr) ?>"
                    >
                        <?= e($pillStr) ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if (!function_exists('renderUnifiedFilterSelect')) {

    function renderUnifiedFilterSelect(array $select): void
    {
        $id          = (string) ($select['id'] ?? 'directory-filter-select');
        $name        = (string) ($select['name'] ?? 'filter');
        $label       = (string) ($select['label'] ?? 'Filter Kategori');
        $ariaLabel   = (string) ($select['ariaLabel'] ?? $label);
        $options     = (array) ($select['options'] ?? []);
        $activeValue = (string) ($select['activeValue'] ?? '');
        $extraClass  = trim((string) ($select['extraClass'] ?? ''));
        $disabled    = !empty($select['disabled']);
        $isAssoc     = !empty($options) && array_keys($options) !== range(0, count($options) - 1);
        $selectClass = 'partner-unified-select' . ($extraClass !== '' ? ' ' . $extraClass : '');
        ?>
        <label class="sr-only" for="<?= e($id) ?>"><?= e($label) ?></label>
        <select
            id="<?= e($id) ?>"
            name="<?= e($name) ?>"
            class="<?= e($selectClass) ?>"
            aria-label="<?= e($ariaLabel) ?>"
            <?= $disabled ? 'disabled' : '' ?>
        >
            <?php foreach ($options as $optKey => $optLabel):
                $valStr     = $isAssoc ? (string) $optKey : (string) $optLabel;
                $labelStr   = (string) $optLabel;
                $isSelected = strcasecmp($activeValue, $valStr) === 0;
            ?>
                <option value="<?= e($valStr) ?>" <?= $isSelected ? 'selected' : '' ?>>
                    <?= e($labelStr) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}

if (!function_exists('renderUnifiedDirectoryFilter')) {

    function renderUnifiedDirectoryFilter(array $config): void
    {
        $ariaLabel         = (string) ($config['ariaLabel'] ?? 'Filter Direktori');
        $heading           = (string) ($config['heading'] ?? 'Filter & Pencarian');
        $description       = (string) ($config['description'] ?? '');
        $descriptionHtml   = (string) ($config['descriptionHtml'] ?? '');
        $formId            = (string) ($config['formId'] ?? 'unified-directory-filter-form');
        $pageName          = (string) ($config['pageName'] ?? '');
        $hiddenParams      = (array) ($config['hiddenParams'] ?? []);
        $showSearch        = array_key_exists('showSearch', $config)
            ? !empty($config['showSearch'])
            : !empty($config['searchInputId']);
        $searchInputId     = (string) ($config['searchInputId'] ?? 'directory-search-input');
        $searchName        = (string) ($config['searchName'] ?? 'q');
        $searchKeyword     = (string) ($config['searchKeyword'] ?? '');
        $searchPlaceholder = (string) ($config['searchPlaceholder'] ?? 'Cari...');
        $searchAriaLabel   = (string) ($config['searchAriaLabel'] ?? $searchPlaceholder);

        $selects = [];
        if (!empty($config['selects']) && is_array($config['selects'])) {
            $selects = $config['selects'];
        } elseif (!empty($config['selectId'])) {
            $selects[] = [
                'id'          => (string) $config['selectId'],
                'name'        => (string) ($config['selectName'] ?? 'bidang'),
                'label'       => (string) ($config['selectLabel'] ?? 'Filter Kategori'),
                'ariaLabel'   => (string) ($config['selectAriaLabel'] ?? ($config['selectLabel'] ?? 'Filter Kategori')),
                'options'     => (array) ($config['selectOptions'] ?? []),
                'activeValue' => (string) ($config['activeSelectValue'] ?? ''),
            ];
        }
        ?>
        <section class="team-selection-card" role="search" aria-label="<?= e($ariaLabel) ?>">
            <div class="partner-filter-heading">
                <h2><?= e($heading) ?></h2>
                <?php if ($descriptionHtml !== ''): ?>
                    <p><?= $descriptionHtml ?></p>
                <?php elseif ($description !== ''): ?>
                    <p><?= e($description) ?></p>
                <?php endif; ?>
            </div>
            <form
                method="get"
                action="<?= e(url('index.php')) ?>"
                class="team-competition-filter partner-unified-filter"
                id="<?= e($formId) ?>"
                novalidate
            >
                <?php if ($pageName !== ''): ?>
                    <input type="hidden" name="page" value="<?= e($pageName) ?>">
                <?php endif; ?>
                <?php foreach ($hiddenParams as $paramKey => $paramVal): ?>
                    <?php if ($paramVal !== null && $paramVal !== ''): ?>
                        <input type="hidden" name="<?= e((string) $paramKey) ?>" value="<?= e((string) $paramVal) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($showSearch): ?>
                    <div class="partner-search-wrap">
                        <svg class="partner-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            type="search"
                            id="<?= e($searchInputId) ?>"
                            name="<?= e($searchName) ?>"
                            value="<?= e($searchKeyword) ?>"
                            class="partner-search-input"
                            placeholder="<?= e($searchPlaceholder) ?>"
                            aria-label="<?= e($searchAriaLabel) ?>"
                            autocomplete="off"
                        >
                    </div>
                <?php endif; ?>

                <?php foreach ($selects as $selectItem): ?>
                    <?php renderUnifiedFilterSelect($selectItem); ?>
                <?php endforeach; ?>
            </form>
        </section>
        <?php
    }
}

if (!function_exists('renderUnifiedActiveFilterBar')) {

    function renderUnifiedActiveFilterBar(array $config): void
    {
        $barId           = (string) ($config['barId'] ?? 'active-directory-filter-bar');
        $labelId         = (string) ($config['labelId'] ?? 'active-directory-filter-label');
        $clearBtnId      = (string) ($config['clearBtnId'] ?? 'btn-clear-directory-filter');
        $hasActiveFilter = !empty($config['hasActiveFilter']);
        $primaryLabel    = (string) ($config['primaryLabel'] ?? 'Semua Bidang');
        $secondaryPrefix = (string) ($config['secondaryPrefix'] ?? '');
        $secondaryValue  = (string) ($config['secondaryValue'] ?? '');
        $searchKeyword   = (string) ($config['searchKeyword'] ?? '');
        ?>
        <div
            class="dash-active-filter-bar"
            id="<?= e($barId) ?>"
            style="<?= $hasActiveFilter ? 'display: flex;' : 'display: none;' ?> margin-bottom: 1.5rem;"
        >
            <span class="active-filter-text" id="<?= e($labelId) ?>">
                Filter Aktif:
                <strong><?= e($primaryLabel) ?></strong>
                <?php if ($secondaryPrefix !== '' && $secondaryValue !== ''): ?>
                    &bull; <?= e($secondaryPrefix) ?>: <strong><?= e($secondaryValue) ?></strong>
                <?php endif; ?>
                <?= $searchKeyword !== '' ? ' &bull; Kata kunci: "' . e($searchKeyword) . '"' : '' ?>
            </span>
            <button type="button" class="btn-clear-inline" id="<?= e($clearBtnId) ?>">Reset Filter &times;</button>
        </div>
        <?php
    }
}

if (!function_exists('renderUnifiedLoadingState')) {

    function renderUnifiedLoadingState(string $id, string $message): void
    {
        ?>
        <div class="partner-loading-state" id="<?= e($id) ?>" role="status" aria-live="polite" style="display: none;">
            <div class="partner-spinner" aria-hidden="true"></div>
            <span><?= e($message) ?></span>
        </div>
        <?php
    }
}
