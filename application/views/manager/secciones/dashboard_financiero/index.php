<?php
$anios_dashboard = array();
if (isset($select_anios) && is_array($select_anios)) {
    foreach ($select_anios as $value => $label) {
        $anios_dashboard[] = (string) $value;
    }
}

$anio_inicial = isset($filtros['anio']) && $filtros['anio']
    ? (int) $filtros['anio']
    : (int) date('Y');
?>

<link rel="stylesheet" href="<?= base_url('assets/dashboard-financiero/dist/assets/dashboard-financiero.css'); ?>?dat=<?= time(); ?>">

<div
    id="dashboard-financiero-root"
    data-base-url="<?= base_url('Admin/DashboardFinanciero'); ?>"
    data-assets-url="<?= base_url('assets/manager/images'); ?>"
    data-initial-year="<?= $anio_inicial; ?>"
    data-years="<?= html_escape(implode(',', $anios_dashboard)); ?>"
></div>

<script type="module" src="<?= base_url('assets/dashboard-financiero/dist/assets/dashboard-financiero.js'); ?>?dat=<?= time(); ?>"></script>
