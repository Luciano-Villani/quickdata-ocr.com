import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './styles.css';

const MONTHS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
const MONTH_OPTIONS = [
  { value: '', label: 'Anio completo' },
  { value: '1', label: 'Enero' },
  { value: '2', label: 'Febrero' },
  { value: '3', label: 'Marzo' },
  { value: '4', label: 'Abril' },
  { value: '5', label: 'Mayo' },
  { value: '6', label: 'Junio' },
  { value: '7', label: 'Julio' },
  { value: '8', label: 'Agosto' },
  { value: '9', label: 'Septiembre' },
  { value: '10', label: 'Octubre' },
  { value: '11', label: 'Noviembre' },
  { value: '12', label: 'Diciembre' },
];

const PERIOD_MONTH_OPTIONS = MONTH_OPTIONS.filter((item) => item.value);

const TABS = [
  { id: 'finanzas', label: 'Finanzas' },
  { id: 'comparativo', label: 'Analisis comparativo' },
  { id: 'consumos', label: 'Consumos' },
  { id: 'eficiencia', label: 'Eficiencia energetica' },
];

const COLORS = ['#075cf7', '#20a8f7', '#6f35d3', '#28b979', '#ffa51f', '#ff7d1e', '#9aa9bd'];

function variationPercent(row) {
  if (!row) return null;
  return row.variacion_porcentual ?? row.porcentaje ?? null;
}

function moneyCompact(value) {
  const amount = Number(value || 0);
  const abs = Math.abs(amount);
  if (abs >= 1000000000) return `$ ${(amount / 1000000000).toLocaleString('es-AR', { maximumFractionDigits: 2 })} MM`;
  if (abs >= 1000000) return `$ ${(amount / 1000000).toLocaleString('es-AR', { maximumFractionDigits: 2 })} M`;
  return `$ ${amount.toLocaleString('es-AR', { maximumFractionDigits: 0 })}`;
}

function numberCompact(value) {
  return Number(value || 0).toLocaleString('es-AR', { maximumFractionDigits: 0 });
}

function pct(value) {
  if (value === null || value === undefined || Number.isNaN(Number(value))) return '-';
  return `${Number(value).toLocaleString('es-AR', { maximumFractionDigits: 1 })}%`;
}

function monthName(value) {
  return MONTH_OPTIONS.find((item) => item.value === String(value))?.label || '';
}

function monthShort(value) {
  return MONTHS[Number(value) - 1] || '';
}

function ytdRangeLabel(month, year) {
  if (!month) return `YTD ${year}`;
  return `Ene-${monthShort(month)} ${year}`;
}

function monthlyPoint(rows, month) {
  return (rows || []).find((row) => Number(row.mes) === Number(month)) || null;
}

function monthYoYVariation(currentRows, previousRows, month) {
  const current = monthlyPoint(currentRows, month);
  const previous = monthlyPoint(previousRows, month);
  const currentTotal = Number(current?.total || 0);
  const previousTotal = Number(previous?.total || 0);
  if (!current || previousTotal <= 0) return null;
  return ((currentTotal - previousTotal) / previousTotal) * 100;
}

function cleanSecretaria(value) {
  return String(value || '-')
    .replace(/^SECRETARIA\s+(DE\s+|DEL\s+|DE LA\s+|)/i, '')
    .replace(/^JEFATURA\s+DE\s+/i, '')
    .replace(/^H\.?C\.?D\.?$/i, 'HCD')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

function cleanName(value) {
  return String(value || '-')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

function formatDimensionName(value, type) {
  if (type === 'secretaria') return cleanSecretaria(value);
  return cleanName(value);
}

function groupRowsByDisplayName(rows, type, limit = 10) {
  const grouped = new Map();
  (rows || []).forEach((row) => {
    const label = formatDimensionName(row.nombre || row.secretaria || row.id, type);
    const current = grouped.get(label) || { ...row, nombre: label, total: 0, facturas: 0 };
    current.total += Number(row.total || 0);
    current.facturas += Number(row.facturas || 0);
    grouped.set(label, current);
  });
  return Array.from(grouped.values())
    .sort((a, b) => Number(b.total || 0) - Number(a.total || 0))
    .slice(0, limit);
}

function formatOptions(options, type) {
  return (options || []).map((item) => ({
    ...item,
    label: item.value ? formatDimensionName(item.label, type) : item.label,
  }));
}

function classNames(...items) {
  return items.filter(Boolean).join(' ');
}

function useApi(baseUrl, filters) {
  const query = useMemo(() => {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== '' && value !== null && value !== undefined) params.set(key, value);
    });
    return params.toString();
  }, [filters]);

  const get = async (path, extra = {}) => {
    const params = new URLSearchParams(query);
    Object.entries(extra).forEach(([key, value]) => {
      if (value === null) {
        params.delete(key);
      } else if (value !== '' && value !== undefined) {
        params.set(key, value);
      }
    });
    const response = await fetch(`${baseUrl}/api/${path}?${params.toString()}`, { credentials: 'same-origin' });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
  };

  return { get };
}

function App() {
  const root = document.getElementById('dashboard-financiero-root');
  const baseUrl = root?.dataset.baseUrl || '/Admin/DashboardFinanciero';
  const assetsUrl = root?.dataset.assetsUrl || '/assets/manager/images';
  const initialYear = root?.dataset.initialYear || String(new Date().getFullYear());
  const previousYear = String(Math.max(0, Number(initialYear) - 1));
  const initialYears = (root?.dataset.years || initialYear).split(',').filter(Boolean);

  const [activeTab, setActiveTab] = useState('finanzas');
  const [filters, setFilters] = useState({
    anio: initialYear,
    mes: '',
    periodo_a_anio: previousYear,
    periodo_a_mes_desde: '',
    periodo_a_mes_hasta: '',
    periodo_b_anio: initialYear,
    periodo_b_mes_desde: '',
    periodo_b_mes_hasta: '',
    secretaria: '',
    programa: '',
    proyecto: '',
    objeto: '',
    proveedor: '',
    dependencia: '',
    cuenta: '',
    unidad_medida: '',
  });
  const [compareDraftFilters, setCompareDraftFilters] = useState(null);

  const [options, setOptions] = useState({
    anios: initialYears.map((year) => ({ value: year, label: year })),
    meses_por_anio: {},
    secretarias: [],
    programas: [],
    proyectos: [],
    objetos: [],
    proveedores: [],
    dependencias: [],
    unidades_medida: [],
  });
  const [compareOptions, setCompareOptions] = useState(null);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const initialEfficiencyFilters = {
    anio: initialYear,
    mes_desde: '1',
    mes_hasta: '12',
    modo: 'financiera',
    ventana: '36',
    problema: '',
    segmento: '',
    tarifa: '',
    dependencia: '',
    cuenta: '',
    medidor: '',
  };
  const [efficiencyFilters, setEfficiencyFilters] = useState(initialEfficiencyFilters);
  const [efficiencyDraft, setEfficiencyDraft] = useState(initialEfficiencyFilters);
  const [efficiencyView, setEfficiencyView] = useState('financiera');
  const [efficiencyData, setEfficiencyData] = useState(null);
  const [efficiencyLoading, setEfficiencyLoading] = useState(false);
  const [efficiencyError, setEfficiencyError] = useState('');
  const api = useApi(baseUrl, filters);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError('');

    async function loadDashboard() {
      const filtros = await api.get('filtros');
      const resumenYtd = await api.get('resumen', { mes: null });
      const corteMes = resumenYtd.data?.corte?.mes_hasta || null;
      const ytdExtra = { mes: null };
      if (corteMes) ytdExtra.mes_hasta = corteMes;
      const periodoExtra = filters.mes ? { mes: filters.mes, mes_hasta: null } : ytdExtra;
      const evolucionExtra = filters.mes ? { mes: null, mes_hasta: null } : ytdExtra;
      const comparativoExtra = {
        periodo_a_anio: filters.periodo_a_anio,
        periodo_a_mes_desde: filters.periodo_a_mes_desde,
        periodo_a_mes_hasta: filters.periodo_a_mes_hasta,
        periodo_b_anio: filters.periodo_b_anio,
        periodo_b_mes_desde: filters.periodo_b_mes_desde,
        periodo_b_mes_hasta: filters.periodo_b_mes_hasta,
        secretaria: filters.secretaria,
        programa: filters.programa,
        proyecto: filters.proyecto,
        objeto: filters.objeto,
        proveedor: filters.proveedor,
        dependencia: filters.dependencia,
        cuenta: filters.cuenta,
      };

      const [resumenPeriodo, evolucion, secretarias, secretariasContexto, proveedores, dependencias, objetos, crecimiento, pareto, forecast, servicios, comparativo] = await Promise.all([
        api.get('resumen', periodoExtra),
        api.get('evolucion', evolucionExtra),
        api.get('ranking', { ...periodoExtra, dimension: 'secretaria', limite: 30 }),
        api.get('ranking', { ...periodoExtra, dimension: 'secretaria', limite: 30, secretaria: null }),
        api.get('ranking', { ...periodoExtra, dimension: 'proveedor', limite: 30 }),
        api.get('ranking', { ...periodoExtra, dimension: 'dependencia', limite: 30 }),
        api.get('ranking', { ...periodoExtra, dimension: 'objeto', limite: 30 }),
        api.get('crecimiento', { ...periodoExtra, dimension: 'secretaria', limite: 8 }),
        api.get('pareto', { ...periodoExtra, dimension: 'dependencia', limite: 5 }),
        api.get('forecast', periodoExtra),
        api.get('servicios', periodoExtra),
        api.get('comparativo', comparativoExtra),
      ]);

        if (cancelled) return;
        setOptions({
          anios: filtros.filtros?.anios?.length ? filtros.filtros.anios : options.anios,
          meses_por_anio: filtros.filtros?.meses_por_anio || {},
          secretarias: filtros.filtros?.secretarias || [],
          programas: filtros.filtros?.programas || [],
          proyectos: filtros.filtros?.proyectos || [],
          objetos: filtros.filtros?.objetos || [],
          proveedores: filtros.filtros?.proveedores || [],
          dependencias: filtros.filtros?.dependencias || [],
          unidades_medida: filtros.filtros?.unidades_medida || [],
        });
        setData({
          resumenPeriodo: resumenPeriodo.data || {},
          resumenYtd: resumenYtd.data || {},
          evolucion: evolucion.data || {},
          secretarias: secretarias.data || [],
          secretariasContexto: secretariasContexto.data || [],
          proveedores: proveedores.data || [],
          dependencias: dependencias.data || [],
          objetos: objetos.data || [],
          crecimiento: crecimiento.data || [],
          pareto: pareto.data || {},
          forecast: forecast.data || {},
          servicios: servicios.data || {},
          comparativo: comparativo.data || {},
        });
    }

    loadDashboard()
      .catch((err) => {
        if (!cancelled) setError(err.message || 'No se pudo cargar el dashboard.');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [
    filters.anio,
    filters.mes,
    filters.periodo_a_anio,
    filters.periodo_a_mes_desde,
    filters.periodo_a_mes_hasta,
    filters.periodo_b_anio,
    filters.periodo_b_mes_desde,
    filters.periodo_b_mes_hasta,
    filters.secretaria,
    filters.programa,
    filters.proyecto,
    filters.objeto,
    filters.proveedor,
    filters.dependencia,
    filters.cuenta,
    filters.unidad_medida,
  ]);

  useEffect(() => {
    if (activeTab !== 'comparativo' || !compareDraftFilters) return undefined;

    let cancelled = false;
    const timer = window.setTimeout(() => {
      api.get('filtros', compareDraftFilters)
        .then((filtros) => {
          if (cancelled) return;
          setCompareOptions({
            anios: filtros.filtros?.anios?.length ? filtros.filtros.anios : options.anios,
            meses_por_anio: filtros.filtros?.meses_por_anio || options.meses_por_anio || {},
            secretarias: filtros.filtros?.secretarias || [],
            programas: filtros.filtros?.programas || [],
            proyectos: filtros.filtros?.proyectos || [],
            objetos: filtros.filtros?.objetos || [],
            proveedores: filtros.filtros?.proveedores || [],
            dependencias: filtros.filtros?.dependencias || [],
            unidades_medida: filtros.filtros?.unidades_medida || [],
          });
        })
        .catch(() => {
          if (!cancelled) setCompareOptions(null);
        });
    }, 250);

    return () => {
      cancelled = true;
      window.clearTimeout(timer);
    };
  }, [activeTab, compareDraftFilters]);

  useEffect(() => {
    if (activeTab !== 'eficiencia') return undefined;

    let cancelled = false;
    setEfficiencyLoading(true);
    setEfficiencyError('');

    api.get('eficiencia', {
      mes: null,
      periodo_a_anio: null,
      periodo_a_mes_desde: null,
      periodo_a_mes_hasta: null,
      periodo_b_anio: null,
      periodo_b_mes_desde: null,
      periodo_b_mes_hasta: null,
      programa: null,
      proyecto: null,
      objeto: null,
      proveedor: null,
      unidad_medida: null,
      secretaria: null,
      ...efficiencyFilters,
    })
      .then((response) => {
        if (!cancelled) setEfficiencyData(response.data || {});
      })
      .catch((err) => {
        if (!cancelled) setEfficiencyError(err.message || 'No se pudo cargar eficiencia energetica.');
      })
      .finally(() => {
        if (!cancelled) setEfficiencyLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [
    activeTab,
    efficiencyFilters.anio,
    efficiencyFilters.mes_desde,
    efficiencyFilters.mes_hasta,
    efficiencyFilters.segmento,
    efficiencyFilters.tarifa,
    efficiencyFilters.dependencia,
    efficiencyFilters.cuenta,
    efficiencyFilters.medidor,
    efficiencyFilters.modo,
    efficiencyFilters.ventana,
    efficiencyFilters.problema,
  ]);

  const onFilter = (key, value) => {
    setFilters((current) => {
      if (key === 'anio') {
        return {
          ...current,
          anio: value,
          periodo_a_anio: String(Math.max(0, Number(value) - 1)),
          periodo_b_anio: value,
        };
      }
      return { ...current, [key]: value };
    });
  };

  const buildCompareFilterState = (base, key, value) => {
    const next = { ...base, [key]: value };
    if (key === 'secretaria') {
      next.programa = '';
      next.proyecto = '';
      next.dependencia = '';
      next.cuenta = '';
    }
    if (key === 'programa') {
      next.proyecto = '';
      next.dependencia = '';
      next.cuenta = '';
    }
    if (key === 'proyecto') {
      next.dependencia = '';
      next.cuenta = '';
    }
    if (key === 'dependencia') {
      next.cuenta = '';
    }
    return next;
  };

  const onCompareDraftFilter = (key, value) => {
    setCompareDraftFilters((current) => buildCompareFilterState(current || filters, key, value));
  };

  const onCompareDrilldownFilter = (key, value) => {
    setCompareDraftFilters(null);
    setCompareOptions(null);
    setFilters((current) => buildCompareFilterState(current, key, value));
  };

  const applyCompareFilters = () => {
    if (!compareDraftFilters) return;
    setFilters((current) => ({ ...current, ...compareDraftFilters }));
    setCompareDraftFilters(null);
    setCompareOptions(null);
  };

  const clearFilters = () => {
    setFilters((current) => ({
      ...current,
      mes: '',
      secretaria: '',
      programa: '',
      proyecto: '',
      objeto: '',
      proveedor: '',
      dependencia: '',
      cuenta: '',
      unidad_medida: '',
    }));
    setCompareDraftFilters(null);
    setCompareOptions(null);
  };

  const clearCompareFilters = () => {
    const cleared = {
      mes: '',
      secretaria: '',
      programa: '',
      proyecto: '',
      objeto: '',
      proveedor: '',
      dependencia: '',
      cuenta: '',
      unidad_medida: '',
    };
    setCompareDraftFilters(null);
    setCompareOptions(null);
    setFilters((current) => ({ ...current, ...cleared }));
  };

  const onEfficiencyDraftFilter = (key, value) => {
    setEfficiencyDraft((current) => {
      const next = { ...current, [key]: value };
      if (key === 'segmento' || key === 'tarifa') {
        next.dependencia = '';
        next.cuenta = '';
        next.medidor = '';
      }
      if (key === 'dependencia') {
        next.cuenta = '';
        next.medidor = '';
      }
      if (key === 'cuenta') {
        next.medidor = '';
      }
      return next;
    });
  };

  const applyEfficiencyFilters = () => {
    setEfficiencyFilters({ ...efficiencyDraft });
  };

  const clearEfficiencyFilters = () => {
    const cleared = { ...initialEfficiencyFilters, modo: efficiencyView === 'operativa' ? 'operativa' : 'financiera' };
    setEfficiencyDraft(cleared);
    setEfficiencyFilters(cleared);
  };

  const changeEfficiencyView = (nextView) => {
    const modo = nextView === 'operativa' ? 'operativa' : 'financiera';
    const operationalReset = nextView === 'operativa'
      ? {
          anio: initialEfficiencyFilters.anio,
          mes_desde: initialEfficiencyFilters.mes_desde,
          mes_hasta: initialEfficiencyFilters.mes_hasta,
        }
      : {};
    setEfficiencyView(nextView);
    setEfficiencyDraft((current) => ({ ...current, ...operationalReset, modo, problema: '' }));
    setEfficiencyFilters((current) => ({ ...current, ...operationalReset, modo, problema: '' }));
  };

  return (
    <div className="qdf-shell">
      <Header
        activeTab={activeTab}
        setActiveTab={setActiveTab}
        assetsUrl={assetsUrl}
      />
      <main className="qdf-main">
        {activeTab === 'finanzas' && (
          <Finanzas
            data={data}
            loading={loading}
            error={error}
            filters={filters}
            options={compareOptions || options}
            onFilter={onFilter}
            clearFilters={clearFilters}
          />
        )}
        {activeTab === 'comparativo' && (
          <ComparativoV2
            data={data}
            loading={loading}
            error={error}
            filters={compareDraftFilters || filters}
            appliedFilters={filters}
            options={compareOptions || options}
            onFilter={onCompareDraftFilter}
            onDrilldown={onCompareDrilldownFilter}
            clearFilters={clearCompareFilters}
            applyFilters={applyCompareFilters}
            hasPendingFilters={Boolean(compareDraftFilters)}
          />
        )}
        {activeTab === 'eficiencia' && (
          <EficienciaEnergetica
            data={efficiencyData}
            loading={efficiencyLoading}
            error={efficiencyError}
            filters={efficiencyDraft}
            appliedFilters={efficiencyFilters}
            options={efficiencyData?.opciones || {}}
            years={options.anios}
            onFilter={onEfficiencyDraftFilter}
            applyFilters={applyEfficiencyFilters}
            clearFilters={clearEfficiencyFilters}
            view={efficiencyView}
            onViewChange={changeEfficiencyView}
          />
        )}
        {activeTab === 'consumos' && <ComingSoon activeTab={activeTab} />}
        {(loading || efficiencyLoading) && <LoadingOverlay />}
      </main>
    </div>
  );
}

function LoadingOverlay({ text = 'Analizando datos del municipio' }) {
  return (
    <div className="qdf-loading-overlay" role="status" aria-live="polite">
      <div className="qdf-loading-card">
        <span className="qdf-loading-ring">
          <img src="/assets/dashboard-financiero/quickdata-spinner.png" alt="" />
        </span>
        <strong>{text}</strong>
        <small>Preparando indicadores financieros...</small>
      </div>
    </div>
  );
}

function Header({ activeTab, setActiveTab, assetsUrl }) {
  return (
    <header className="qdf-header">
      <div className="qdf-brand">
        <img src="/assets/dashboard-financiero/quickdata-doc-intelligence.png" alt="QuickData Document Intelligence" className="qdf-logo-quickdata" />
        <span className="qdf-brand-line" />
        <img src={`${assetsUrl}/Logo-mvl2.png`} alt="Vivamos Vicente Lopez" className="qdf-logo-mvl-text" />
      </div>

      <nav className="qdf-tabs" aria-label="Dashboards">
        {TABS.map((tab) => (
          <button
            type="button"
            key={tab.id}
            className={classNames('qdf-tab', activeTab === tab.id && 'active')}
            onClick={() => setActiveTab(tab.id)}
          >
            {tab.label}
          </button>
        ))}
      </nav>
    </header>
  );
}

function Finanzas({ data, loading, error, filters, options, onFilter, clearFilters }) {
  if (error) return <StateBox title="No se pudo cargar el dashboard" text={error} />;
  const actual = filters.mes ? (data?.resumenPeriodo?.actual || {}) : (data?.resumenYtd?.actual || {});
  const ytdActual = data?.resumenYtd?.actual || {};
  const periodo = data?.resumenPeriodo?.actual || {};
  const compYtd = filters.mes ? (data?.resumenPeriodo?.comparativas || {}) : (data?.resumenYtd?.comparativas || {});
  const secretariasDisplay = groupRowsByDisplayName(data?.secretarias || [], 'secretaria', 30);
  const secretariasContexto = groupRowsByDisplayName(data?.secretariasContexto || data?.secretarias || [], 'secretaria', 30);
  const dependenciasDisplay = groupRowsByDisplayName(data?.dependencias || [], 'dependencia', 30);
  const objetosDisplay = groupRowsByDisplayName(data?.objetos || [], 'objeto', 30);
  const topSecretaria = secretariasDisplay?.[0];
  const topProveedor = data?.proveedores?.[0];
  const topSecretariaPct = actual.total > 0 ? (Number(topSecretaria?.total || 0) / Number(actual.total || 1)) * 100 : 0;
  const topProveedorPct = actual.total > 0 ? (Number(topProveedor?.total || 0) / Number(actual.total || 1)) * 100 : 0;
  const topDependencias = dependenciasDisplay;
  const topDependenciasTotal = topDependencias.reduce((sum, row) => sum + Number(row.total || 0), 0);
  const concentration = data?.pareto?.rows?.slice(0, 5) || [];
  const concentrationPct = concentration.reduce((sum, row) => sum + Number(row.porcentaje_total || 0), 0);
  const corteMes = data?.resumenYtd?.corte?.mes_hasta || null;
  const corteInfo = data?.resumenYtd?.corte || {};
  const periodMonthLabel = filters.mes ? monthName(filters.mes) : monthName(corteMes);
  const periodLabel = `${periodMonthLabel} ${filters.anio}`;
  const scopeLabel = filters.mes ? periodLabel : `YTD ${filters.anio}`;
  const periodCompareLabel = periodMonthLabel ? `vs. ${periodMonthLabel} ${Number(filters.anio) - 1}` : `vs. ${Number(filters.anio) - 1}`;
  const periodDelta = monthYoYVariation(data?.evolucion?.actual || [], data?.evolucion?.anterior || [], filters.mes || corteMes);
  const ytdSubtitle = `vs. ${ytdRangeLabel(corteMes, Number(filters.anio) - 1)}`;
  const promedioMensualYtd = corteMes ? Number(ytdActual.total || 0) / Number(corteMes) : 0;
  const bottomPanels = financeBottomPanels({
    filters,
    anio: filters.anio,
    scopeLabel,
    total: Number(actual.total || 0),
    secretarias: secretariasDisplay,
    dependencias: topDependencias,
    proveedores: data?.proveedores || [],
    objetos: objetosDisplay,
    evolucion: data?.evolucion?.actual || [],
  });

  return (
    <section className={classNames('qdf-page', loading && 'is-loading')}>
      <div className="qdf-title-row">
        <div>
          <h1>Finanzas</h1>
          <span>Vision general del gasto municipal</span>
        </div>
        <FilterStrip filters={filters} options={options} onFilter={onFilter} clearFilters={clearFilters} />
      </div>

      {!filters.mes && <DataQualityNotice corte={corteInfo} year={filters.anio} />}

      <section className="qdf-kpis">
        {filters.mes ? (
          <>
            <Kpi icon="$" title="Gasto mes seleccionado" value={moneyCompact(actual.total)} delta={variationPercent(compYtd.anio_anterior)} subtitle={periodCompareLabel} color="blue" />
            <Kpi icon="+" title="Variacion interanual del mes" value={pct(variationPercent(compYtd.anio_anterior))} subtitle={periodCompareLabel} color="green" />
            <Kpi icon="#" title="Facturas del mes" value={numberCompact(actual.facturas)} subtitle={periodLabel} color="blue" />
            <Kpi icon="#" title="Promedio por factura" value={moneyCompact(actual.promedio)} subtitle={periodLabel} color="purple" />
            <Kpi icon="#" title="Top secretaria del mes" value={topSecretaria?.nombre || '-'} subtitle={`${moneyCompact(topSecretaria?.total)} - ${pct(topSecretariaPct)}`} color="blue" />
            <Kpi icon="o" title="Top proveedor del mes" value={topProveedor?.nombre || '-'} subtitle={`${moneyCompact(topProveedor?.total)} - ${pct(topProveedorPct)}`} color="purple" />
          </>
        ) : (
          <>
            <Kpi icon="$" title="Gasto total acumulado (YTD)" value={moneyCompact(actual.total)} delta={variationPercent(compYtd.anio_anterior)} subtitle={ytdSubtitle} color="blue" />
            <Kpi icon="#" title="Gasto ultimo mes cerrado" value={moneyCompact(periodo.total)} delta={periodDelta} subtitle={periodCompareLabel} color="blue" />
            <Kpi icon="+" title="Variacion interanual (YTD)" value={pct(variationPercent(compYtd.anio_anterior))} subtitle={`${ytdRangeLabel(corteMes, filters.anio)} vs. ${ytdRangeLabel(corteMes, Number(filters.anio) - 1)}`} color="green" />
            <Kpi icon="#" title="Promedio mensual YTD" value={moneyCompact(promedioMensualYtd)} subtitle={ytdRangeLabel(corteMes, filters.anio)} color="purple" />
            <Kpi icon="#" title="Top secretaria (YTD)" value={topSecretaria?.nombre || '-'} subtitle={`${moneyCompact(topSecretaria?.total)} - ${pct(topSecretariaPct)}`} color="blue" />
            <Kpi icon="o" title="Top proveedor (YTD)" value={topProveedor?.nombre || '-'} subtitle={`${moneyCompact(topProveedor?.total)} - ${pct(topProveedorPct)}`} color="purple" />
          </>
        )}
      </section>

      <section className="qdf-grid qdf-grid-middle">
        <Panel title="Evolucion mensual del gasto" subtitle="Millones de pesos">
          <LineChart actual={data?.evolucion?.actual || []} previous={data?.evolucion?.anterior || []} year={Number(filters.anio)} />
        </Panel>
        <Panel title={`Distribucion del gasto ${scopeLabel}`} subtitle="Por Secretaria">
          <DonutWithBars
            rows={secretariasDisplay}
            barRows={secretariasContexto}
            centerTitle="Total"
            centerValue={moneyCompact(actual.total)}
            totalGeneral={Number(actual.total || 0)}
            selectedValue={filters.secretaria}
          />
        </Panel>
      </section>

      <section className="qdf-grid qdf-grid-bottom">
        {bottomPanels.map((panel) => (
          <Panel title={panel.title} subtitle={panel.subtitle} key={panel.key}>
            {panel.type === 'monthly' ? (
              <MonthlySummary rows={panel.rows} totalGeneral={panel.totalGeneral} />
            ) : (
              <CompactBarRanking rows={panel.rows} totalGeneral={panel.totalGeneral} limit={10} numbered={panel.numbered} />
            )}
          </Panel>
        ))}
      </section>

      <footer className="qdf-footer-note">
        <span>i</span> Los datos se actualizan desde facturas consolidadas de Proveedores y Electromecanica.
      </footer>
    </section>
  );
}

function ComparativoV2({ data, loading, error, filters, appliedFilters, options, onFilter, onDrilldown, clearFilters, applyFilters, hasPendingFilters }) {
  if (error) return <StateBox title="No se pudo cargar el comparativo" text={error} />;

  const comparativo = data?.comparativo || {};
  const periodos = comparativo.periodos || {};
  const periodoA = periodos.a || {};
  const periodoB = periodos.b || {};
  const kpiA = periodoA.kpis || {};
  const kpiB = periodoB.kpis || {};
  const labelA = periodoA.label || `Periodo A ${filters.periodo_a_anio || filters.anio}`;
  const labelB = periodoB.label || `Periodo B ${filters.periodo_b_anio || Number(filters.anio) - 1}`;
  const variacionPeriodo = periodos.variacion || {};
  const secretariasPeriodo = normalizePeriodRows(periodos.secretarias || [], 'secretaria');
  const programasPeriodo = normalizePeriodRows(periodos.programas || [], 'programa');
  const proyectosPeriodo = normalizePeriodRows(periodos.proyectos || [], 'proyecto');
  const proveedoresPeriodo = normalizePeriodRows(periodos.proveedores || [], 'proveedor');
  const dependenciasPeriodo = normalizePeriodRows(periodos.dependencias || [], 'dependencia');
  const objetosPeriodo = normalizePeriodRows(periodos.objetos || [], 'objeto');
  const cuentasPeriodo = normalizeAccountRows(comparativo.cuentas || []);
  const facturasPeriodo = normalizeInvoiceRows(comparativo.facturas || []);
  const totalA = Number(kpiA.total || 0);
  const totalB = Number(kpiB.total || 0);
  const diff = Number(variacionPeriodo.delta ?? (totalB - totalA));
  const diffPct = variacionPeriodo.porcentaje ?? (totalA > 0 ? (diff / totalA) * 100 : null);
  const facturasA = Number(kpiA.facturas || 0);
  const facturasB = Number(kpiB.facturas || 0);
  const facturasPct = facturasA > 0 ? ((facturasB - facturasA) / facturasA) * 100 : null;
  const mainVariation = periodos.principal_variacion || buildPrincipalVariation(objetosPeriodo, proveedoresPeriodo, dependenciasPeriodo);
  const activeLevel = getCompareActiveLevel(filters);
  const mainTable = getCompareMainTable(activeLevel, {
    secretarias: secretariasPeriodo,
    programas: programasPeriodo,
    proyectos: proyectosPeriodo,
    dependencias: dependenciasPeriodo,
    cuentas: cuentasPeriodo,
    facturas: facturasPeriodo,
  });
  const contextTitle = buildCompareContextTitle(filters);
  const insights = buildCompareInsights({ diff, diffPct, mainVariation, proveedores: proveedoresPeriodo, labelA, labelB });

  return (
    <section className={classNames('qdf-page qdf-comparativo-page', loading && 'is-loading')}>
      <div className="qdf-title-row qdf-comparativo-title">
        <div>
          <h1>Analisis Comparativo</h1>
          <span>Analisis y evolucion del gasto</span>
        </div>
        <button type="button" className="qdf-export-button">Exportar</button>
      </div>

      <CompareFilterBarDeferred
        filters={filters}
        appliedFilters={appliedFilters || filters}
        options={options}
        onFilter={onFilter}
        clearFilters={clearFilters}
        applyFilters={applyFilters}
        hasPendingFilters={hasPendingFilters}
      />

      <section className="qdf-compare-kpis">
        <Kpi icon="$" title={`Gasto ${labelA}`} value={moneyCompact(totalA)} subtitle={`${numberCompact(kpiA.facturas)} facturas`} color="blue" />
        <Kpi icon="$" title={`Gasto ${labelB}`} value={moneyCompact(totalB)} subtitle={`${numberCompact(kpiB.facturas)} facturas`} color="green" />
        <Kpi icon="+" title="Diferencia $" value={moneyCompact(diff)} delta={diffPct} subtitle={diff >= 0 ? 'Aumento' : 'Disminucion'} color="purple" />
        <Kpi icon="%" title="Diferencia %" value={pct(diffPct)} delta={diffPct} subtitle={`${labelB} vs ${labelA}`} color="purple" />
        <Kpi icon="doc" title="Facturas A vs B" value={`${numberCompact(facturasA)} / ${numberCompact(facturasB)}`} delta={facturasPct} subtitle={pct(facturasPct)} color="blue" />
        <Kpi icon="bar" title="Principal variacion" value={mainVariation?.nombre || '-'} subtitle={`${moneyCompact(mainVariation?.delta)} (${pct(mainVariation?.porcentaje)})`} color="orange" />
      </section>

      <div className="qdf-compare-layout">
        <aside className="qdf-analysis-rail">
          <AnalysisRoute filters={filters} activeLevel={activeLevel.key} onSelect={onDrilldown} />
          <CompareModes />
        </aside>

        <div className="qdf-compare-content">
          <div className="qdf-compare-context">
            <strong>{contextTitle}</strong>
            <span>{labelA} vs {labelB} | Objeto: {filters.objeto || 'Todos'} | Proveedor: {filters.proveedor || 'Todos'}</span>
          </div>

          <section className="qdf-compare-main-grid">
            <Panel title="Evolucion mensual del gasto" subtitle="Millones de pesos">
              <PeriodEvolutionPanel periodos={periodos} labelA={labelA} labelB={labelB} />
            </Panel>
            <Panel title="Impacto por proveedor" subtitle={`${labelA} vs ${labelB}`}>
              <ProviderImpactTable rows={proveedoresPeriodo} labelA={labelA} labelB={labelB} onSelect={(value) => onDrilldown('proveedor', value)} />
            </Panel>
          </section>

          <section className="qdf-compare-bottom-grid">
            <Panel title={mainTable.title} subtitle={`${labelA} vs ${labelB}`}>
              <PeriodCompareTable rows={mainTable.rows} firstColumn={mainTable.firstColumn} labelA={labelA} labelB={labelB} onSelect={(value) => onDrilldown(mainTable.filterKey, value)} />
            </Panel>
            <Panel title="Insights principales">
              <ComparePeriodInsights items={insights} />
            </Panel>
          </section>
        </div>
      </div>

      <footer className="qdf-footer-note">
        <span>i</span> El comparativo permite cruzar mes contra mes, trimestre contra trimestre o rangos libres.
      </footer>
    </section>
  );
}

function CompareFilterBar({ filters, options, onFilter, clearFilters }) {
  const yearOptions = options.anios?.length ? options.anios : [{ value: filters.anio, label: filters.anio }];
  const monthFromOptions = [{ value: '', label: 'Desde' }, ...PERIOD_MONTH_OPTIONS];
  const monthToOptions = [{ value: '', label: 'Hasta' }, ...PERIOD_MONTH_OPTIONS];

  return (
    <div className="qdf-compare-filter-card qdf-compare-filter-card-v2">
      <div className="qdf-period-box">
        <strong>Periodo A</strong>
        <SelectMini label="Anio" value={filters.periodo_a_anio || filters.anio} options={yearOptions} onChange={(v) => onFilter('periodo_a_anio', v)} />
        <SelectMini label="Desde" value={filters.periodo_a_mes_desde} options={monthFromOptions} onChange={(v) => onFilter('periodo_a_mes_desde', v)} />
        <SelectMini label="Hasta" value={filters.periodo_a_mes_hasta} options={monthToOptions} onChange={(v) => onFilter('periodo_a_mes_hasta', v)} />
      </div>
      <div className="qdf-period-box secondary">
        <strong>Periodo B</strong>
        <SelectMini label="Anio" value={filters.periodo_b_anio || String(Number(filters.anio) - 1)} options={yearOptions} onChange={(v) => onFilter('periodo_b_anio', v)} />
        <SelectMini label="Desde" value={filters.periodo_b_mes_desde} options={monthFromOptions} onChange={(v) => onFilter('periodo_b_mes_desde', v)} />
        <SelectMini label="Hasta" value={filters.periodo_b_mes_hasta} options={monthToOptions} onChange={(v) => onFilter('periodo_b_mes_hasta', v)} />
      </div>
      <SelectMini label="Secretaria" value={filters.secretaria} options={formatOptions(options.secretarias, 'secretaria')} onChange={(v) => onFilter('secretaria', v)} />
      <SelectMini label="Programa" value={filters.programa} options={options.programas || [{ value: '', label: 'Todos los programas' }]} onChange={(v) => onFilter('programa', v)} />
      <SelectMini label="Proyecto" value={filters.proyecto} options={options.proyectos || [{ value: '', label: 'Todos los proyectos' }]} onChange={(v) => onFilter('proyecto', v)} />
      <SelectMini label="Dependencia" value={filters.dependencia} options={formatOptions(options.dependencias, 'dependencia')} onChange={(v) => onFilter('dependencia', v)} />
      <SelectMini label="Objeto del gasto" value={filters.objeto} options={options.objetos || [{ value: '', label: 'Todos los objetos' }]} onChange={(v) => onFilter('objeto', v)} />
      <SelectMini label="Proveedor" value={filters.proveedor} options={options.proveedores} onChange={(v) => onFilter('proveedor', v)} />
      <button type="button" className="qdf-clear-filter" onClick={clearFilters}>Limpiar</button>
    </div>
  );
}

function CompareFilterBarDeferred({ filters, appliedFilters, options, onFilter, clearFilters, applyFilters, hasPendingFilters }) {
  const yearOptions = options.anios?.length ? options.anios : [{ value: filters.anio, label: filters.anio }];
  const monthsA = availableMonthOptions(options.meses_por_anio, filters.periodo_a_anio || filters.anio);
  const monthsB = availableMonthOptions(options.meses_por_anio, filters.periodo_b_anio || String(Number(filters.anio) - 1));
  const monthFromOptionsA = [{ value: '', label: 'Desde' }, ...monthsA];
  const monthToOptionsA = [{ value: '', label: 'Hasta' }, ...monthsA];
  const monthFromOptionsB = [{ value: '', label: 'Desde' }, ...monthsB];
  const monthToOptionsB = [{ value: '', label: 'Hasta' }, ...monthsB];
  const hasSecretaria = Boolean(filters.secretaria);
  const hasPrograma = Boolean(filters.programa);
  const dirty = hasPendingFilters || JSON.stringify(filters) !== JSON.stringify(appliedFilters || filters);

  return (
    <div className="qdf-compare-filter-card qdf-compare-filter-card-v2">
      <div className="qdf-period-box">
        <strong>Periodo A</strong>
        <SelectMini label="Anio" value={filters.periodo_a_anio || filters.anio} options={yearOptions} onChange={(v) => onFilter('periodo_a_anio', v)} />
        <SelectMini label="Desde" value={filters.periodo_a_mes_desde} options={monthFromOptionsA} onChange={(v) => onFilter('periodo_a_mes_desde', v)} />
        <SelectMini label="Hasta" value={filters.periodo_a_mes_hasta} options={monthToOptionsA} onChange={(v) => onFilter('periodo_a_mes_hasta', v)} />
      </div>
      <div className="qdf-period-box secondary">
        <strong>Periodo B</strong>
        <SelectMini label="Anio" value={filters.periodo_b_anio || String(Number(filters.anio) - 1)} options={yearOptions} onChange={(v) => onFilter('periodo_b_anio', v)} />
        <SelectMini label="Desde" value={filters.periodo_b_mes_desde} options={monthFromOptionsB} onChange={(v) => onFilter('periodo_b_mes_desde', v)} />
        <SelectMini label="Hasta" value={filters.periodo_b_mes_hasta} options={monthToOptionsB} onChange={(v) => onFilter('periodo_b_mes_hasta', v)} />
      </div>
      <SelectMini label="Secretaria" value={filters.secretaria} options={formatOptions(options.secretarias, 'secretaria')} onChange={(v) => onFilter('secretaria', v)} />
      <SelectMini label="Programa" value={filters.programa} options={hasSecretaria ? options.programas : [{ value: '', label: 'Seleccione jurisdiccion' }]} onChange={(v) => onFilter('programa', v)} disabled={!hasSecretaria} />
      <SelectMini label="Proyecto" value={filters.proyecto} options={hasPrograma ? options.proyectos : [{ value: '', label: 'Seleccione programa' }]} onChange={(v) => onFilter('proyecto', v)} disabled={!hasPrograma} />
      <SelectMini label={hasSecretaria ? `Dependencias de ${limitText(filters.secretaria, 20)}` : 'Dependencia'} value={filters.dependencia} options={formatOptions(options.dependencias, hasSecretaria ? `Dependencias de ${filters.secretaria}` : 'dependencia')} onChange={(v) => onFilter('dependencia', v)} />
      <SelectMini label="Objeto del gasto" value={filters.objeto} options={options.objetos || [{ value: '', label: 'Todos los objetos' }]} onChange={(v) => onFilter('objeto', v)} />
      <SelectMini label="Proveedor" value={filters.proveedor} options={options.proveedores} onChange={(v) => onFilter('proveedor', v)} />
      <div className="qdf-compare-actions">
        <button type="button" className="qdf-apply-filter" onClick={applyFilters} disabled={!dirty}>Aplicar consulta</button>
        <button type="button" className="qdf-clear-filter" onClick={clearFilters}>Limpiar</button>
      </div>
    </div>
  );
}

function availableMonthOptions(mesesPorAnio, year) {
  const months = mesesPorAnio?.[String(year)] || [];
  if (!months.length) return PERIOD_MONTH_OPTIONS;
  const allowed = new Set(months.map((month) => String(Number(month))));
  return PERIOD_MONTH_OPTIONS.filter((item) => allowed.has(String(Number(item.value))));
}

function getCompareActiveLevel(filters) {
  if (filters.dependencia) return { key: 'dependencia', label: 'Dependencia' };
  if (filters.proyecto) return { key: 'dependencia', label: 'Dependencia' };
  if (filters.programa) return { key: 'proyecto', label: 'Proyecto' };
  if (filters.secretaria) return { key: 'programa', label: 'Programa' };
  return { key: 'secretaria', label: 'Secretaria' };
}

function buildCompareContextTitle(filters) {
  const parts = [];
  if (filters.secretaria) parts.push(formatDimensionName(filters.secretaria, 'secretaria'));
  if (filters.programa) parts.push(`Programa ${filters.programa}`);
  if (filters.proyecto) parts.push(`Proyecto ${filters.proyecto}`);
  if (filters.dependencia) parts.push(formatDimensionName(filters.dependencia, 'dependencia'));
  return parts.length ? parts.join(' / ') : 'Total MVL';
}

function getCompareMainTable(activeLevel, data) {
  if (activeLevel.key === 'secretaria') {
    return { title: 'Comparativo por Secretaria', firstColumn: 'Secretaria', filterKey: 'secretaria', rows: data.secretarias };
  }
  if (activeLevel.key === 'programa') {
    return { title: 'Comparativo por Programa', firstColumn: 'Programa', filterKey: 'programa', rows: data.programas };
  }
  if (activeLevel.key === 'proyecto') {
    return { title: 'Comparativo por Proyecto', firstColumn: 'Proyecto', filterKey: 'proyecto', rows: data.proyectos };
  }
  if (activeLevel.key === 'dependencia') {
    return { title: 'Comparativo por Dependencia', firstColumn: 'Dependencia', filterKey: 'dependencia', rows: data.dependencias };
  }
  return { title: 'Comparativo por Secretaria', firstColumn: 'Secretaria', filterKey: 'secretaria', rows: data.secretarias };
}

function buildPrincipalVariation(...groups) {
  const rows = groups.flat().filter(Boolean);
  if (!rows.length) return null;
  return [...rows].sort((a, b) => Math.abs(Number(b.variacion_absoluta || 0)) - Math.abs(Number(a.variacion_absoluta || 0)))[0];
}

function buildCompareInsights({ diff, diffPct, mainVariation, proveedores, labelA, labelB }) {
  const topProveedor = proveedores?.[0];
  const totalIncrease = Math.max(Number(diff || 0), 0);
  const providerShare = totalIncrease > 0 && topProveedor ? (Number(topProveedor.variacion_absoluta || 0) / totalIncrease) * 100 : null;
  const variationDelta = Number(mainVariation ? (mainVariation.delta ?? mainVariation.variacion_absoluta ?? 0) : 0);
  const variationShare = totalIncrease > 0 && mainVariation ? (variationDelta / totalIncrease) * 100 : null;

  return [
    {
      icon: 'up',
      text: `El gasto ${diff >= 0 ? 'aumento' : 'bajo'} ${moneyCompact(Math.abs(diff))} (${pct(diffPct)}) respecto al periodo base.`,
    },
    mainVariation && {
      icon: 'bar',
      text: `${mainVariation.nombre} explica ${pct(variationShare)} de la variacion principal.`,
    },
    topProveedor && {
      icon: 'dot',
      text: `${topProveedor.nombre} concentra ${pct(providerShare)} del cambio entre ${labelA} y ${labelB}.`,
    },
  ].filter(Boolean);
}

function AnalysisRoute({ filters, activeLevel, onSelect }) {
  const steps = [
    { key: 'total', label: 'Total MVL', value: '', available: true },
    { key: 'secretaria', label: 'Secretaria', value: filters.secretaria, available: true },
    { key: 'programa', label: 'Programa', value: filters.programa, available: Boolean(filters.secretaria) },
    { key: 'proyecto', label: 'Proyecto', value: filters.proyecto, available: Boolean(filters.programa) },
    { key: 'dependencia', label: 'Dependencia', value: filters.dependencia, available: Boolean(filters.secretaria || filters.programa || filters.proyecto) },
  ];
  const resetFrom = (key) => {
    if (!onSelect) return;
    if (key === 'total') {
      ['secretaria', 'programa', 'proyecto', 'dependencia'].forEach((item) => onSelect(item, ''));
    }
    if (key === 'secretaria') {
      ['programa', 'proyecto', 'dependencia'].forEach((item) => onSelect(item, ''));
    }
    if (key === 'programa') {
      ['proyecto', 'dependencia'].forEach((item) => onSelect(item, ''));
    }
    if (key === 'proyecto') {
      onSelect('dependencia', '');
    }
  };

  return (
    <div className="qdf-rail-card">
      <h3>Ruta de analisis</h3>
      <div className="qdf-route">
        {steps.map((step, index) => (
          <button type="button" className={classNames('qdf-route-step', activeLevel === step.key && 'active', step.value && 'selected', !step.available && 'locked')} key={step.key} onClick={() => resetFrom(step.key)} disabled={!step.available}>
            <i>{index + 1}</i>
            <span>{step.label}{step.value && <small>{limitText(formatDimensionName(step.value, step.key), 22)}</small>}</span>
          </button>
        ))}
      </div>
    </div>
  );
}

function CompareModes() {
  const modes = ['Evolucion mensual', 'Tabla comparativa', 'Variacion %', 'Variacion $'];
  return (
    <div className="qdf-rail-card">
      <h3>Comparar por</h3>
      {modes.map((mode, index) => (
        <button type="button" className={classNames('qdf-mode-button', index === 0 && 'active')} key={mode}>{mode}</button>
      ))}
    </div>
  );
}

function PeriodEvolutionPanel({ periodos, labelA, labelB }) {
  const [mode, setMode] = useState('mensual');
  return (
    <div className="qdf-period-evolution-panel">
      <div className="qdf-chart-toggle">
        <button type="button" className={mode === 'mensual' ? 'active' : ''} onClick={() => setMode('mensual')}>Mensual</button>
        <button type="button" className={mode === 'acumulado' ? 'active' : ''} onClick={() => setMode('acumulado')}>Acumulado</button>
      </div>
      {mode === 'mensual'
        ? <PeriodBarChart periodos={periodos} labelA={labelA} labelB={labelB} />
        : <PeriodAccumulatedChart periodos={periodos} labelA={labelA} labelB={labelB} />}
      <small className="qdf-chart-caption">Comparando {labelA} vs {labelB}</small>
    </div>
  );
}

function Comparativo({ data, loading, error, filters, options, onFilter, clearFilters }) {
  if (error) return <StateBox title="No se pudo cargar el comparativo" text={error} />;

  const comparativo = data?.comparativo || {};
  const anios = (comparativo.anios || []).map((year) => Number(year)).filter(Boolean);
  const currentYear = Number(comparativo.anio_actual || filters.anio);
  const kpis = comparativo.kpis || {};
  const current = kpis[currentYear] || {};
  const previous = kpis[currentYear - 1] || {};
  const alcance = comparativo.alcance?.label || `YTD ${filters.anio}`;
  const secretarias = normalizeCompareRows(comparativo.secretarias || [], 'secretaria', anios);
  const proveedores = normalizeCompareRows(comparativo.proveedores || [], 'proveedor', anios);
  const dependencias = normalizeCompareRows(comparativo.dependencias || [], 'dependencia', anios);
  const topAumentos = (comparativo.aumentos || []).slice(0, 8).map((row) => ({
    ...row,
    nombre: formatDimensionName(row.nombre, 'dependencia'),
  }));
  const topSecretaria = secretarias[0];
  const topProveedor = proveedores[0];
  const totalActual = Number(current.total || 0);
  const totalAnterior = Number(previous.total || 0);
  const diff = totalActual - totalAnterior;
  const diffPct = totalAnterior > 0 ? (diff / totalAnterior) * 100 : null;

  return (
    <section className={classNames('qdf-page', loading && 'is-loading')}>
      <div className="qdf-title-row">
        <div>
          <h1>Analisis Comparativo</h1>
          <span>Evolucion y variaciones del gasto municipal</span>
        </div>
        <FilterStrip filters={filters} options={options} onFilter={onFilter} clearFilters={clearFilters} />
      </div>

      <section className="qdf-kpis">
        {anios.map((year) => (
          <Kpi
            key={`kpi-comp-${year}`}
            icon="$"
            title={`Gasto ${year}`}
            value={moneyCompact(kpis[year]?.total)}
            subtitle={year === currentYear ? alcance : alcance.replace(String(currentYear), String(year))}
            color={year === currentYear ? 'purple' : 'blue'}
          />
        ))}
        <Kpi icon="+" title={`Variacion ${currentYear} vs ${currentYear - 1}`} value={pct(diffPct)} delta={diffPct} subtitle={moneyCompact(diff)} color="green" />
        <Kpi icon="#" title="Facturas comparadas" value={numberCompact(current.facturas)} subtitle={alcance} color="blue" />
        <Kpi icon="o" title="Top proveedor" value={topProveedor?.nombre || '-'} subtitle={moneyCompact(topProveedor?.total_actual)} color="purple" />
      </section>

      <section className="qdf-grid qdf-grid-middle">
        <Panel title="Evolucion mensual comparada" subtitle="Millones de pesos">
          <MultiYearChart series={comparativo.evolucion || {}} years={anios} />
        </Panel>
        <Panel title="Distribucion comparativa" subtitle="Secretarias principales">
          <CompareHighlight
            total={totalActual}
            previousTotal={totalAnterior}
            topSecretaria={topSecretaria}
            topProveedor={topProveedor}
            diffPct={diffPct}
            year={currentYear}
          />
        </Panel>
      </section>

      <section className="qdf-grid qdf-grid-bottom">
        <Panel title="Comparativo por Secretaria" subtitle={alcance}>
          <CompareTable rows={secretarias} years={anios} firstColumn="Secretaria" />
        </Panel>
        <Panel title="Top proveedores" subtitle={alcance}>
          <CompareTable rows={proveedores} years={anios} firstColumn="Proveedor" />
        </Panel>
        <Panel title="Mayores aumentos" subtitle={`${currentYear} vs ${currentYear - 1}`}>
          <IncreaseRanking rows={topAumentos} />
        </Panel>
      </section>

      <section className="qdf-grid qdf-grid-bottom">
        <Panel title="Top dependencias" subtitle={alcance}>
          <CompactBarRanking rows={dependencias.map((row) => ({ ...row, total: row.total_actual }))} totalGeneral={totalActual} limit={10} numbered />
        </Panel>
        <Panel title="Lectura ejecutiva" subtitle="Que mirar primero">
          <CompareInsights total={totalActual} previousTotal={totalAnterior} topSecretaria={topSecretaria} topProveedor={topProveedor} diffPct={diffPct} />
        </Panel>
      </section>

      <footer className="qdf-footer-note">
        <span>i</span> El comparativo usa el mismo corte de periodo para todos los anios.
      </footer>
    </section>
  );
}

function normalizeCompareRows(rows, type, years) {
  return (rows || []).map((row) => {
    const totalActual = Number(row.total_actual || 0);
    const normalized = {
      ...row,
      nombre: formatDimensionName(row.nombre || row.id, type),
      total_actual: totalActual,
      total_anterior: Number(row.total_anterior || 0),
      variacion_absoluta: Number(row.variacion_absoluta || 0),
      variacion_porcentual: row.variacion_porcentual,
      totales: row.totales || {},
    };

    years.forEach((year) => {
      normalized.totales[year] = Number(normalized.totales[year] || 0);
    });

    return normalized;
  });
}

function normalizePeriodRows(rows, type) {
  return (rows || []).map((row) => ({
    ...row,
    nombre: formatDimensionName(row.nombre || row.id, type),
    total_a: Number(row.total_a || 0),
    total_b: Number(row.total_b || 0),
    facturas_a: Number(row.facturas_a || 0),
    facturas_b: Number(row.facturas_b || 0),
    variacion_absoluta: Number(row.variacion_absoluta || 0),
    variacion_porcentual: row.variacion_porcentual,
  }));
}

function normalizeAccountRows(rows) {
  return (rows || []).map((row) => ({
    ...row,
    dependencia: formatDimensionName(row.dependencia || 'Sin dependencia', 'dependencia'),
    proveedor: formatDimensionName(row.proveedor || 'Sin proveedor', 'proveedor'),
    nro_cuenta: row.nro_cuenta || '-',
    total_a: Number(row.total_a || 0),
    total_b: Number(row.total_b || 0),
    facturas_a: Number(row.facturas_a || 0),
    facturas_b: Number(row.facturas_b || 0),
    variacion_absoluta: Number(row.variacion_absoluta || 0),
    variacion_porcentual: row.variacion_porcentual,
  }));
}

function normalizeInvoiceRows(rows) {
  return (rows || []).map((row) => ({
    ...row,
    dependencia: formatDimensionName(row.dependencia || 'Sin dependencia', 'dependencia'),
    proveedor: formatDimensionName(row.proveedor || 'Sin proveedor', 'proveedor'),
    nro_cuenta: row.nro_cuenta || '-',
    nro_factura: row.nro_factura || '-',
    periodo_del_consumo: row.periodo_del_consumo || '-',
    periodo: row.periodo || '-',
    total: Number(row.total || 0),
  }));
}

function buildPeriodVariationRows(rows) {
  const clean = (rows || []).filter((row) => Number(row.variacion_absoluta || 0) !== 0);
  return {
    increases: [...clean].sort((a, b) => Number(b.variacion_absoluta || 0) - Number(a.variacion_absoluta || 0)).slice(0, 5),
    decreases: [...clean].sort((a, b) => Number(a.variacion_absoluta || 0) - Number(b.variacion_absoluta || 0)).slice(0, 5),
  };
}

function periodoRows(periodos, key) {
  return (periodos?.evolucion?.[key] || []).map((row) => ({
    mes: Number(row.mes),
    total: Number(row.total || 0),
  })).filter((row) => row.mes);
}

function PeriodBarChart({ periodos, labelA, labelB }) {
  const rowsA = periodoRows(periodos, 'a');
  const rowsB = periodoRows(periodos, 'b');
  const months = Array.from(new Set([...rowsA, ...rowsB].map((row) => row.mes))).sort((a, b) => a - b);
  const max = Math.max(...rowsA.map((row) => row.total), ...rowsB.map((row) => row.total), 1);
  const mapA = new Map(rowsA.map((row) => [row.mes, row.total]));
  const mapB = new Map(rowsB.map((row) => [row.mes, row.total]));

  if (!months.length) return <div className="qdf-empty-mini">Sin datos para los periodos seleccionados.</div>;

  const w = 780;
  const h = 310;
  const pad = { top: 36, right: 18, bottom: 42, left: 68 };
  const innerW = w - pad.left - pad.right;
  const innerH = h - pad.top - pad.bottom;
  const groupW = innerW / Math.max(months.length, 1);
  const barW = Math.min(28, (groupW - 16) / 2);
  const y = (value) => pad.top + innerH - (Number(value || 0) / max) * innerH;
  const pairs = [{ label: labelA, map: mapA, color: '#6f35d3' }, { label: labelB, map: mapB, color: '#28b979' }];

  return (
    <div className="qdf-chart-wrap">
      <div className="qdf-legend">
        {pairs.map((item) => <span key={item.label}><i style={{ background: item.color }} />{item.label}</span>)}
      </div>
      <svg viewBox={`0 0 ${w} ${h}`} className="qdf-bar-chart">
        {[0, 1, 2, 3, 4].map((item) => {
          const gy = pad.top + (innerH / 4) * item;
          const label = moneyCompact(max - (max / 4) * item).replace('$ ', '');
          return <g key={item}><line x1={pad.left} y1={gy} x2={w - pad.right} y2={gy} /><text x={10} y={gy + 4}>{label}</text></g>;
        })}
        {months.map((month, monthIndex) => {
          const baseX = pad.left + monthIndex * groupW + groupW / 2 - barW;
          return (
            <g key={`period-month-${month}`}>
              {pairs.map((item, pairIndex) => {
                const value = item.map.get(month) || 0;
                const height = pad.top + innerH - y(value);
                return (
                  <rect key={`${item.label}-${month}`} x={baseX + pairIndex * barW} y={y(value)} width={barW - 3} height={height} rx="5" fill={item.color}>
                    <title>{item.label} {MONTHS[month - 1]}: {moneyCompact(value)}</title>
                  </rect>
                );
              })}
              <text className="month" x={pad.left + monthIndex * groupW + groupW / 2} y={h - 12}>{MONTHS[month - 1]}</text>
            </g>
          );
        })}
      </svg>
    </div>
  );
}

function PeriodAccumulatedChart({ periodos, labelA, labelB }) {
  const rowsA = periodoRows(periodos, 'a');
  const rowsB = periodoRows(periodos, 'b');
  const months = Array.from(new Set([...rowsA, ...rowsB].map((row) => row.mes))).sort((a, b) => a - b);
  const build = (rows) => {
    const map = new Map(rows.map((row) => [row.mes, row.total]));
    let running = 0;
    return months.map((month) => {
      running += Number(map.get(month) || 0);
      return { month, value: running };
    });
  };
  const series = [{ label: labelA, rows: build(rowsA), color: '#6f35d3' }, { label: labelB, rows: build(rowsB), color: '#28b979' }];
  const max = Math.max(...series.flatMap((item) => item.rows.map((row) => row.value)), 1);

  if (!months.length) return <div className="qdf-empty-mini">Sin acumulado para los periodos seleccionados.</div>;

  const w = 780;
  const h = 310;
  const pad = { top: 36, right: 22, bottom: 42, left: 72 };
  const innerW = w - pad.left - pad.right;
  const innerH = h - pad.top - pad.bottom;
  const x = (index) => pad.left + (innerW / Math.max(months.length - 1, 1)) * index;
  const y = (value) => pad.top + innerH - (Number(value || 0) / max) * innerH;
  const points = (rows) => rows.map((row, index) => `${x(index)},${y(row.value)}`).join(' ');

  return (
    <div className="qdf-chart-wrap">
      <div className="qdf-legend">
        {series.map((item) => <span key={item.label}><i style={{ background: item.color }} />{item.label}</span>)}
      </div>
      <svg viewBox={`0 0 ${w} ${h}`} className="qdf-acc-chart">
        {[0, 1, 2, 3, 4].map((item) => {
          const gy = pad.top + (innerH / 4) * item;
          const label = moneyCompact(max - (max / 4) * item).replace('$ ', '');
          return <g key={item}><line x1={pad.left} y1={gy} x2={w - pad.right} y2={gy} /><text x={10} y={gy + 4}>{label}</text></g>;
        })}
        {series.map((item, index) => <polyline key={item.label} points={points(item.rows)} fill="none" stroke={item.color} strokeWidth={index === 0 ? 4 : 3} strokeDasharray={index === 0 ? '0' : '7 7'} strokeLinecap="round" strokeLinejoin="round" />)}
        {months.map((month, index) => <text className="month" x={x(index)} y={h - 12} key={month}>{MONTHS[month - 1]}</text>)}
      </svg>
    </div>
  );
}
function MultiYearChart({ series, years }) {
  const visibleYears = years.filter((year) => (series?.[year] || []).some((row) => Number(row.total || 0) > 0));
  const months = Array.from(new Set(visibleYears.flatMap((year) => (series?.[year] || []).map((row) => Number(row.mes)).filter(Boolean)))).sort((a, b) => a - b);
  const max = Math.max(...visibleYears.flatMap((year) => (series?.[year] || []).map((row) => Number(row.total || 0))), 1);
  const dataMap = new Map();
  visibleYears.forEach((year) => {
    dataMap.set(year, new Map((series?.[year] || []).map((row) => [Number(row.mes), Number(row.total || 0)])));
  });

  if (!visibleYears.length || !months.length) {
    return <div className="qdf-empty-mini">Sin datos comparativos para los filtros seleccionados.</div>;
  }

  const w = 780;
  const h = 310;
  const pad = { top: 36, right: 18, bottom: 42, left: 68 };
  const innerW = w - pad.left - pad.right;
  const innerH = h - pad.top - pad.bottom;
  const groupW = innerW / Math.max(months.length, 1);
  const barW = Math.min(18, (groupW - 14) / Math.max(visibleYears.length, 1));
  const y = (value) => pad.top + innerH - (Number(value || 0) / max) * innerH;

  return (
    <div className="qdf-chart-wrap">
      <div className="qdf-legend">
        {visibleYears.map((year, index) => <span key={year}><i style={{ background: COLORS[index % COLORS.length] }} />{year}</span>)}
      </div>
      <svg viewBox={`0 0 ${w} ${h}`} className="qdf-bar-chart">
        {[0, 1, 2, 3, 4].map((item) => {
          const gy = pad.top + (innerH / 4) * item;
          const label = moneyCompact(max - (max / 4) * item).replace('$ ', '');
          return <g key={item}><line x1={pad.left} y1={gy} x2={w - pad.right} y2={gy} /><text x={10} y={gy + 4}>{label}</text></g>;
        })}
        {months.map((month, monthIndex) => {
          const baseX = pad.left + monthIndex * groupW + groupW / 2 - (barW * visibleYears.length) / 2;
          return (
            <g key={`month-${month}`}>
              {visibleYears.map((year, yearIndex) => {
                const value = dataMap.get(year)?.get(month) || 0;
                const height = pad.top + innerH - y(value);
                return (
                  <rect
                    key={`${year}-${month}`}
                    x={baseX + yearIndex * barW}
                    y={y(value)}
                    width={barW - 2}
                    height={height}
                    rx="5"
                    fill={COLORS[yearIndex % COLORS.length]}
                  >
                    <title>{year} {MONTHS[month - 1]}: {moneyCompact(value)}</title>
                  </rect>
                );
              })}
              <text className="month" x={pad.left + monthIndex * groupW + groupW / 2} y={h - 12}>{MONTHS[month - 1]}</text>
            </g>
          );
        })}
      </svg>
    </div>
  );
}

function AccumulatedYearChart({ series, years }) {
  const visibleYears = years.filter((year) => (series?.[year] || []).some((row) => Number(row.total || 0) > 0));
  const months = Array.from(new Set(visibleYears.flatMap((year) => (series?.[year] || []).map((row) => Number(row.mes)).filter(Boolean)))).sort((a, b) => a - b);
  const cumulative = {};
  let max = 1;

  visibleYears.forEach((year) => {
    let running = 0;
    const map = new Map((series?.[year] || []).map((row) => [Number(row.mes), Number(row.total || 0)]));
    cumulative[year] = months.map((month) => {
      running += Number(map.get(month) || 0);
      max = Math.max(max, running);
      return { month, value: running };
    });
  });

  if (!visibleYears.length || !months.length) {
    return <div className="qdf-empty-mini">Sin acumulado para los filtros seleccionados.</div>;
  }

  const w = 780;
  const h = 310;
  const pad = { top: 36, right: 22, bottom: 42, left: 72 };
  const innerW = w - pad.left - pad.right;
  const innerH = h - pad.top - pad.bottom;
  const x = (index) => pad.left + (innerW / Math.max(months.length - 1, 1)) * index;
  const y = (value) => pad.top + innerH - (Number(value || 0) / max) * innerH;
  const points = (rows) => rows.map((row, index) => `${x(index)},${y(row.value)}`).join(' ');

  return (
    <div className="qdf-chart-wrap">
      <div className="qdf-legend">
        {visibleYears.map((year, index) => <span key={year}><i style={{ background: COLORS[index % COLORS.length] }} />{year}</span>)}
      </div>
      <svg viewBox={`0 0 ${w} ${h}`} className="qdf-acc-chart">
        {[0, 1, 2, 3, 4].map((item) => {
          const gy = pad.top + (innerH / 4) * item;
          const label = moneyCompact(max - (max / 4) * item).replace('$ ', '');
          return <g key={item}><line x1={pad.left} y1={gy} x2={w - pad.right} y2={gy} /><text x={10} y={gy + 4}>{label}</text></g>;
        })}
        {visibleYears.map((year, index) => (
          <polyline
            key={year}
            points={points(cumulative[year])}
            fill="none"
            stroke={COLORS[index % COLORS.length]}
            strokeWidth={year === Math.max(...visibleYears) ? 4 : 2.5}
            strokeDasharray={year === Math.max(...visibleYears) ? '0' : '7 7'}
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        ))}
        {months.map((month, index) => <text className="month" x={x(index)} y={h - 12} key={month}>{MONTHS[month - 1]}</text>)}
      </svg>
    </div>
  );
}

function buildVariationRows(rows) {
  const clean = (rows || []).filter((row) => Number(row.variacion_absoluta || 0) !== 0);
  return {
    increases: [...clean].sort((a, b) => Number(b.variacion_absoluta || 0) - Number(a.variacion_absoluta || 0)).slice(0, 5),
    decreases: [...clean].sort((a, b) => Number(a.variacion_absoluta || 0) - Number(b.variacion_absoluta || 0)).slice(0, 5),
  };
}

function VariationAnalysis({ increases, decreases }) {
  const [mode, setMode] = useState('increases');
  const rows = mode === 'increases' ? increases : decreases;
  const max = Math.max(...(rows || []).map((row) => Math.abs(Number(row.variacion_absoluta || 0))), 1);

  return (
    <div className="qdf-variation-analysis">
      <div className="qdf-variation-tabs">
        <button type="button" className={mode === 'increases' ? 'active' : ''} onClick={() => setMode('increases')}>Mayores aumentos</button>
        <button type="button" className={mode === 'decreases' ? 'active' : ''} onClick={() => setMode('decreases')}>Mayores disminuciones</button>
      </div>
      <div className="qdf-increase-list">
        {(rows || []).length ? rows.map((row, index) => {
          const amount = Number(row.variacion_absoluta || 0);
          const width = Math.max(5, Math.abs(amount) / max * 100);
          return (
            <div className={classNames('qdf-increase-row', amount < 0 && 'decrease')} key={`${row.nombre}-${index}`} title={`${row.nombre}: ${moneyCompact(amount)}`}>
              <strong>{limitText(row.nombre || '-', 26)}</strong>
              <div><i style={{ width: `${width}%` }} /></div>
              <em>{moneyCompact(amount)}</em>
              <span>{pct(row.variacion_porcentual)}</span>
            </div>
          );
        }) : <div className="qdf-empty-mini">Sin variaciones relevantes.</div>}
      </div>
    </div>
  );
}

function CompareHighlight({ total, previousTotal, topSecretaria, topProveedor, diffPct, year }) {
  const secretariaPct = total > 0 ? (Number(topSecretaria?.total_actual || 0) / total) * 100 : 0;
  const proveedorPct = total > 0 ? (Number(topProveedor?.total_actual || 0) / total) * 100 : 0;
  const diffText = Number(diffPct || 0) >= 0 ? 'crece' : 'baja';

  return (
    <div className="qdf-compare-highlight">
      <div className="qdf-compare-main">
        <small>Total {year}</small>
        <strong>{moneyCompact(total)}</strong>
        <span className={Number(diffPct || 0) >= 0 ? 'up' : 'down'}>{diffText} {pct(Math.abs(Number(diffPct || 0)))} vs. anio anterior</span>
      </div>
      <div className="qdf-compare-cards">
        <div>
          <small>Top secretaria</small>
          <strong title={topSecretaria?.nombre}>{limitText(topSecretaria?.nombre || '-', 28)}</strong>
          <span>{moneyCompact(topSecretaria?.total_actual)} - {pct(secretariaPct)}</span>
        </div>
        <div>
          <small>Top proveedor</small>
          <strong title={topProveedor?.nombre}>{limitText(topProveedor?.nombre || '-', 28)}</strong>
          <span>{moneyCompact(topProveedor?.total_actual)} - {pct(proveedorPct)}</span>
        </div>
        <div>
          <small>Base comparada</small>
          <strong>{moneyCompact(previousTotal)}</strong>
          <span>Anio anterior</span>
        </div>
      </div>
    </div>
  );
}

function ProviderImpactTable({ rows, labelA, labelB, onSelect }) {
  const clean = (rows || []).slice(0, 8);
  if (!clean.length) return <div className="qdf-empty-mini">Sin proveedores para los filtros seleccionados.</div>;

  return (
    <div className="qdf-compare-table-wrap">
      <table className="qdf-table qdf-provider-impact-table">
        <thead>
          <tr>
            <th>Proveedor</th>
            <th title={labelA}>Periodo A</th>
            <th title={labelB}>Periodo B</th>
            <th>Dif. $</th>
            <th>Dif. %</th>
          </tr>
        </thead>
        <tbody>
          {clean.map((row) => (
            <tr key={row.nombre} onClick={() => onSelect?.(row.id || row.nombre)} title={`Filtrar por ${row.nombre}`}>
              <td><strong>{limitText(row.nombre, 24)}</strong></td>
              <td>{moneyCompact(row.total_a)}</td>
              <td>{moneyCompact(row.total_b)}</td>
              <td className={Number(row.variacion_absoluta || 0) >= 0 ? 'up' : 'down'}>{moneyCompact(row.variacion_absoluta)}</td>
              <td className={Number(row.variacion_absoluta || 0) >= 0 ? 'up' : 'down'}>{pct(row.variacion_porcentual)}</td>
            </tr>
          ))}
        </tbody>
      </table>
      <button type="button" className="qdf-link-button">Ver todos los proveedores</button>
    </div>
  );
}

function ComparePeriodInsights({ items }) {
  if (!items?.length) return <div className="qdf-empty-mini">Sin insights para los filtros seleccionados.</div>;
  return (
    <div className="qdf-insight-list">
      {items.map((item, index) => (
        <div className="qdf-insight-item" key={`${item.icon}-${index}`}>
          <i>{index + 1}</i>
          <span>{item.text}</span>
        </div>
      ))}
    </div>
  );
}

function PeriodCompareTable({ rows, firstColumn, labelA, labelB, onSelect }) {
  const clean = (rows || []).slice(0, 10);
  if (!clean.length) return <div className="qdf-empty-mini">Sin datos para los filtros seleccionados.</div>;

  return (
    <div className="qdf-compare-table-wrap">
      <table className="qdf-table qdf-compare-table qdf-period-table">
        <thead>
          <tr>
            <th>{firstColumn}</th>
            <th title={labelA}>Periodo A</th>
            <th title={labelB}>Periodo B</th>
            <th>Var. $</th>
            <th>Var. %</th>
            <th>Facturas</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {clean.map((row) => (
            <tr key={row.nombre} onClick={() => onSelect?.(row.id || row.nombre)} className={onSelect ? 'is-clickable' : ''} title={onSelect ? `Analizar ${row.nombre}` : row.nombre}>
              <td><strong title={row.nombre}>{limitText(row.nombre, 24)}</strong></td>
              <td>{moneyCompact(row.total_a)}</td>
              <td>{moneyCompact(row.total_b)}</td>
              <td className={Number(row.variacion_absoluta || 0) >= 0 ? 'up' : 'down'}>{moneyCompact(row.variacion_absoluta)}</td>
              <td className={Number(row.variacion_absoluta || 0) >= 0 ? 'up' : 'down'}>{pct(row.variacion_porcentual)}</td>
              <td>{numberCompact(row.facturas_a)} / {numberCompact(row.facturas_b)}</td>
              <td><span className="qdf-eye-action" title="Analizar"><i className="fa fa-eye" /></span></td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function AccountsCompareTable({ rows, labelA, labelB, onSelect }) {
  const clean = (rows || []).slice(0, 30);
  if (!clean.length) return <div className="qdf-empty-mini">Sin cuentas para los filtros seleccionados.</div>;

  return (
    <div className="qdf-account-table-wrap">
      <table className="qdf-table qdf-compare-table qdf-account-table">
        <thead>
          <tr>
            <th>Dependencia</th>
            <th>Cuenta</th>
            <th>Proveedor</th>
            <th title={labelA}>Periodo A</th>
            <th title={labelB}>Periodo B</th>
            <th>Var. $</th>
            <th>Var. %</th>
            <th>Facturas</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {clean.map((row, index) => {
            const rowTitle = `${row.dependencia || '-'} | Cuenta: ${row.nro_cuenta || '-'} | Proveedor: ${row.proveedor || '-'} | ${labelA}: ${moneyCompact(row.total_a)} | ${labelB}: ${moneyCompact(row.total_b)} | Var.: ${moneyCompact(row.variacion_absoluta)} (${pct(row.variacion_porcentual)}) | Facturas: ${numberCompact(row.facturas_a)} / ${numberCompact(row.facturas_b)}`;
            return (
            <tr key={`${row.dependencia}-${row.nro_cuenta}-${row.proveedor}-${index}`} title={rowTitle} onClick={() => onSelect?.(row.nro_cuenta)} className={onSelect ? 'is-clickable' : ''}>
              <td><strong title={row.dependencia}>{limitText(row.dependencia, 34)}</strong></td>
              <td title={row.nro_cuenta}>{limitText(row.nro_cuenta, 18)}</td>
              <td title={row.proveedor}>{limitText(row.proveedor, 24)}</td>
              <td>{moneyCompact(row.total_a)}</td>
              <td>{moneyCompact(row.total_b)}</td>
              <td className={Number(row.variacion_absoluta || 0) >= 0 ? 'up' : 'down'}>{moneyCompact(row.variacion_absoluta)}</td>
              <td className={Number(row.variacion_absoluta || 0) >= 0 ? 'up' : 'down'}>{pct(row.variacion_porcentual)}</td>
              <td>{numberCompact(row.facturas_a)} / {numberCompact(row.facturas_b)}</td>
              <td><span className="qdf-eye-action" title="Ver facturas"><i className="fa fa-eye" /></span></td>
            </tr>
          )})}
        </tbody>
      </table>
    </div>
  );
}

function InvoicesCompareTable({ rows, labelA, labelB }) {
  const clean = (rows || []).slice(0, 80);
  if (!clean.length) return <div className="qdf-empty-mini">Sin facturas para la cuenta seleccionada.</div>;

  return (
    <div className="qdf-account-table-wrap">
      <table className="qdf-table qdf-compare-table qdf-invoice-table">
        <thead>
          <tr>
            <th>Periodo</th>
            <th>Factura</th>
            <th>Proveedor</th>
            <th>Cuenta</th>
            <th>Consumo</th>
            <th>Mes/Anio</th>
            <th>Vencimiento</th>
            <th>Consolidada</th>
            <th>Importe</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          {clean.map((row, index) => (
            <tr key={`${row.periodo}-${row.nro_factura}-${index}`} title={`${row.periodo === 'A' ? labelA : labelB} | ${row.nro_factura} | ${moneyCompact(row.total)}`}>
              <td><strong>{row.periodo === 'A' ? 'A' : 'B'}</strong></td>
              <td>{limitText(row.nro_factura, 22)}</td>
              <td>{limitText(row.proveedor, 24)}</td>
              <td>{limitText(row.nro_cuenta, 18)}</td>
              <td>{limitText(row.periodo_del_consumo, 18)}</td>
              <td>{monthShort(row.mes_fc)} {row.anio_fc}</td>
              <td>{row.fecha_vencimiento || '-'}</td>
              <td>{row.fecha_consolidado || '-'}</td>
              <td>{moneyCompact(row.total)}</td>
              <td><span className="qdf-eye-action" title="Ver factura"><i className="fa fa-eye" /></span></td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
function CompareTable({ rows, years, firstColumn }) {
  const clean = (rows || []).slice(0, 10);
  const currentYear = Math.max(...years);

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin datos para los filtros seleccionados.</div>;
  }

  return (
    <table className="qdf-table qdf-compare-table">
      <thead>
        <tr>
          <th>{firstColumn}</th>
          {years.map((year) => <th key={year}>{year}</th>)}
          <th>Variacion</th>
        </tr>
      </thead>
      <tbody>
        {clean.map((row) => (
          <tr key={`${firstColumn}-${row.nombre}`}>
            <td title={row.nombre}>{limitText(row.nombre, 28)}</td>
            {years.map((year) => <td key={`${row.nombre}-${year}`}>{moneyCompact(row.totales?.[year])}</td>)}
            <td className={Number(row.variacion_porcentual || 0) >= 0 ? 'positive' : 'negative'}>
              {pct(row.variacion_porcentual)}
              <small>{moneyCompact(row.variacion_absoluta)}</small>
            </td>
          </tr>
        ))}
        <tr className="total">
          <td>Total visible</td>
          {years.map((year) => (
            <td key={`total-${year}`}>{moneyCompact(clean.reduce((sum, row) => sum + Number(row.totales?.[year] || 0), 0))}</td>
          ))}
          <td>{currentYear}</td>
        </tr>
      </tbody>
    </table>
  );
}

function IncreaseRanking({ rows }) {
  const clean = (rows || []).filter((row) => Number(row.variacion_absoluta || 0) !== 0).slice(0, 8);
  const max = Math.max(...clean.map((row) => Math.abs(Number(row.variacion_absoluta || 0))), 1);

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin aumentos relevantes para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-increase-list">
      {clean.map((row, index) => {
        const amount = Number(row.variacion_absoluta || 0);
        const width = Math.max(5, Math.abs(amount) / max * 100);
        return (
          <div className="qdf-increase-row" key={`${row.nombre}-${index}`} title={`${row.nombre}: ${moneyCompact(amount)}`}>
            <strong>{limitText(row.nombre || '-', 26)}</strong>
            <div><i style={{ width: `${width}%` }} /></div>
            <em>{moneyCompact(amount)}</em>
            <span>{pct(row.variacion_porcentual)}</span>
          </div>
        );
      })}
    </div>
  );
}

function CompareInsights({ total, previousTotal, topSecretaria, topProveedor, diffPct }) {
  const insights = [
    `El gasto comparado ${Number(diffPct || 0) >= 0 ? 'sube' : 'baja'} ${pct(Math.abs(Number(diffPct || 0)))} contra el mismo alcance del anio anterior.`,
    `${topSecretaria?.nombre || 'La principal secretaria'} lidera el gasto del periodo con ${moneyCompact(topSecretaria?.total_actual)}.`,
    `${topProveedor?.nombre || 'El principal proveedor'} explica ${moneyCompact(topProveedor?.total_actual)} del periodo seleccionado.`,
    `La base anterior fue ${moneyCompact(previousTotal)} contra ${moneyCompact(total)} actual.`,
  ];

  return (
    <div className="qdf-insights">
      {insights.map((text, index) => (
        <div className="qdf-insight" key={text}>
          <span className={['blue', 'green', 'purple', 'orange'][index % 4]}>{index + 1}</span>
          <p>{text}</p>
        </div>
      ))}
    </div>
  );
}

function DataQualityNotice({ corte, year }) {
  if (!corte?.hay_meses_parciales || !corte?.meses_excluidos?.length) return null;

  const excludedMonths = corte.meses_excluidos
    .map((item) => monthName(item.mes))
    .filter(Boolean)
    .join(', ');
  const cutoffLabel = monthName(corte.mes_hasta || corte.mes_corte);

  return (
    <div className="qdf-data-notice">
      <div>
        <strong>Datos parciales detectados</strong>
        <span>
          {excludedMonths} {year} tiene carga incompleta y queda fuera del corte ejecutivo. El YTD se muestra hasta {cutoffLabel} {year}.
        </span>
      </div>
      <small>Esto evita comparar meses todavia no cerrados contra periodos completos.</small>
    </div>
  );
}

function FilterStrip({ filters, options, onFilter, clearFilters }) {
  return (
    <div className="qdf-filter-strip">
      <SelectMini label="Periodo" value={filters.mes} options={MONTH_OPTIONS.map((item) => ({ ...item, label: item.value ? item.label : 'YTD' }))} onChange={(v) => onFilter('mes', v)} />
      <SelectMini label="Anio" value={filters.anio} options={options.anios} onChange={(v) => onFilter('anio', v)} />
      <SelectMini label="Secretaria" value={filters.secretaria} options={formatOptions(options.secretarias, 'secretaria')} onChange={(v) => onFilter('secretaria', v)} />
      <SelectMini label="Proveedor" value={filters.proveedor} options={options.proveedores} onChange={(v) => onFilter('proveedor', v)} />
      <SelectMini label="Dependencia" value={filters.dependencia} options={formatOptions(options.dependencias, 'dependencia')} onChange={(v) => onFilter('dependencia', v)} />
      <button type="button" onClick={clearFilters}>Limpiar</button>
    </div>
  );
}

function SelectMini({ label, value, options, onChange, disabled = false }) {
  return (
    <label className={classNames('qdf-select-mini', disabled && 'is-disabled')}>
      <span>{label}</span>
      <select value={value} onChange={(event) => onChange(event.target.value)} title={(options || []).find((item) => String(item.value ?? '') === String(value ?? ''))?.label || label} disabled={disabled}>
        {(options || []).map((item) => (
          <option value={item.value ?? ''} key={`${label}-${item.value ?? ''}`}>{item.label}</option>
        ))}
      </select>
    </label>
  );
}

function financeBottomPanels({ filters, anio, scopeLabel, total, secretarias, dependencias, proveedores, objetos, evolucion }) {
  const hasSecretaria = Boolean(filters.secretaria);
  const hasProveedor = Boolean(filters.proveedor);
  const hasDependencia = Boolean(filters.dependencia);
  const subtitle = scopeLabel || `YTD ${anio}`;

  if (hasDependencia) {
    return [
      { key: 'proveedores-dependencia', title: 'Proveedores de la dependencia', subtitle, rows: proveedores, totalGeneral: total, numbered: false },
      { key: 'objetos-dependencia', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
      { key: 'evolucion-dependencia', title: 'Gasto mensual', subtitle: `Anio ${anio}`, rows: evolucion, totalGeneral: total, type: 'monthly' },
    ];
  }

  if (hasProveedor && hasSecretaria) {
    return [
      { key: 'dependencias-cruce', title: 'Dependencias del cruce', subtitle, rows: dependencias, totalGeneral: total, numbered: true },
      { key: 'objetos-cruce', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
      { key: 'evolucion-cruce', title: 'Gasto mensual', subtitle: `Anio ${anio}`, rows: evolucion, totalGeneral: total, type: 'monthly' },
    ];
  }

  if (hasProveedor) {
    return [
      { key: 'secretarias-proveedor', title: 'Secretarias impactadas', subtitle, rows: secretarias, totalGeneral: total, numbered: false },
      { key: 'dependencias-proveedor', title: 'Dependencias impactadas', subtitle, rows: dependencias, totalGeneral: total, numbered: true },
      { key: 'objetos-proveedor', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
    ];
  }

  if (hasSecretaria) {
    return [
      { key: 'dependencias-secretaria', title: 'Top dependencias de la secretaria', subtitle, rows: dependencias, totalGeneral: total, numbered: true },
      { key: 'objetos-secretaria', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
      { key: 'proveedores-secretaria', title: 'Proveedores de la secretaria', subtitle, rows: proveedores, totalGeneral: total, numbered: false },
    ];
  }

  return [
    { key: 'dependencias-general', title: 'Top dependencias por gasto', subtitle, rows: dependencias, totalGeneral: total, numbered: true },
    { key: 'objetos-general', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
    { key: 'proveedores-general', title: 'Gasto por proveedor', subtitle, rows: proveedores, totalGeneral: total, numbered: false },
  ];
}

function Kpi({ icon, title, value, delta, subtitle, color }) {
  const numericDelta = Number(delta || 0);
  const deltaClass = numericDelta > 0 ? 'up' : numericDelta < 0 ? 'down' : 'flat';
  return (
    <article className={`qdf-card qdf-kpi ${color}`}>
      <div className="qdf-kpi-icon">{icon}</div>
      <div>
        <h3>{title}</h3>
        <strong title={String(value)}>{value}</strong>
        <p>
          {delta !== undefined && delta !== null && <span className={`qdf-delta ${deltaClass}`}>{numericDelta >= 0 ? '+' : '-'} {pct(Math.abs(numericDelta))}</span>}
          <small>{subtitle}</small>
        </p>
      </div>
    </article>
  );
}

function Panel({ title, subtitle, action, children }) {
  return (
    <article className="qdf-card qdf-panel">
      <header>
        <h2>{title} {subtitle && <span>{subtitle}</span>}</h2>
      </header>
      {children}
      {action && <button className="qdf-link-button" type="button">{action} mas detalle</button>}
    </article>
  );
}

function LineChart({ actual, previous, year }) {
  const months = Array.from(new Set([...(actual || []), ...(previous || [])].map((row) => Number(row.mes)).filter(Boolean))).sort((a, b) => a - b);
  const rowsActual = normalizeMonthly(actual, months);
  const rowsPrevious = normalizeMonthly(previous, months);
  const hasPrevious = rowsPrevious.some((row) => row.hasData);
  const hasActual = rowsActual.some((row) => row.hasData);
  const max = Math.max(...rowsActual.map((r) => r.value || 0), ...rowsPrevious.map((r) => r.value || 0), 1);
  const w = 760;
  const h = 310;
  const pad = { top: 28, right: 24, bottom: 42, left: 62 };
  const innerW = w - pad.left - pad.right;
  const innerH = h - pad.top - pad.bottom;
  const x = (index) => pad.left + (innerW / Math.max(months.length - 1, 1)) * index;
  const y = (value) => pad.top + innerH - (Number(value || 0) / max) * innerH;
  const points = (rows) => rows.map((row, index) => (row.hasData ? `${x(index)},${y(row.value)}` : null)).filter(Boolean).join(' ');

  return (
    <div className="qdf-chart-wrap">
      <div className="qdf-legend">
        {hasPrevious && <span><i style={{ background: '#28b979' }} />{year - 1}</span>}
        {hasActual && <span><i style={{ background: '#6833cf' }} />{year}</span>}
      </div>
      <svg viewBox={`0 0 ${w} ${h}`} className="qdf-line-chart">
        {[0, 1, 2, 3, 4].map((item) => {
          const gy = pad.top + (innerH / 4) * item;
          const label = moneyCompact(max - (max / 4) * item).replace('$ ', '');
          return <g key={item}><line x1={pad.left} y1={gy} x2={w - pad.right} y2={gy} /><text x={8} y={gy + 4}>{label}</text></g>;
        })}
        {hasPrevious && <polyline className="previous" points={points(rowsPrevious)} />}
        {hasActual && <polyline className="actual-shadow" points={points(rowsActual)} />}
        {hasActual && <polyline className="actual" points={points(rowsActual)} />}
        {rowsPrevious.map((row, index) => row.hasData ? <circle className="previous-dot" cx={x(index)} cy={y(row.value)} r="4" key={`p-${row.label}`} /> : null)}
        {rowsActual.map((row, index) => row.hasData ? <circle className="actual-dot" cx={x(index)} cy={y(row.value)} r="5" key={`a-${row.label}`} /> : null)}
        {rowsActual.map((row, index) => <text className="month" x={x(index)} y={h - 12} key={row.label}>{row.label}</text>)}
      </svg>
    </div>
  );
}

function normalizeMonthly(rows, months = null) {
  const map = new Map((rows || []).map((row) => [Number(row.mes), Number(row.total || 0)]));
  const first = rows?.[0];
  const monthList = months?.length ? months : Array.from(map.keys()).sort((a, b) => a - b);
  return monthList.map((month) => ({
    label: MONTHS[month - 1] || String(month),
    value: map.get(month) || 0,
    hasData: map.has(month),
    year: first?.anio || first?.anio_actual || '',
  }));
}

function Donut({ rows, centerTitle, centerValue }) {
  const clean = (rows || []).slice(0, 6);
  const total = clean.reduce((sum, row) => sum + Number(row.total || 0), 0) || 1;
  let offset = 25;
  return (
    <div className="qdf-donut-layout">
      <svg viewBox="0 0 220 220" className="qdf-donut">
        <circle cx="110" cy="110" r="74" className="track" pathLength="100" />
        {clean.map((row, index) => {
          const share = (Number(row.total || 0) / total) * 100;
          const currentOffset = offset;
          offset -= share;
          return <circle key={row.nombre || index} cx="110" cy="110" r="74" className="slice" pathLength="100" stroke={COLORS[index % COLORS.length]} strokeDasharray={`${share} ${100 - share}`} strokeDashoffset={currentOffset} />;
        })}
        <text x="110" y="101" className="donut-title">{centerTitle}</text>
        <text x="110" y="126" className="donut-value">{centerValue}</text>
      </svg>
      <div className="qdf-donut-legend">
        {clean.map((row, index) => (
          <div key={row.nombre || index}>
            <i style={{ background: COLORS[index % COLORS.length] }} />
            <span title={row.nombre}>{limitText(row.nombre, 22)}</span>
            <strong>{pct((Number(row.total || 0) / total) * 100)}</strong>
            <em>{moneyCompact(row.total)}</em>
          </div>
        ))}
      </div>
    </div>
  );
}

function DonutWithBars({ rows, barRows: contextRows, centerTitle, centerValue, totalGeneral, selectedValue }) {
  const clean = (rows || []).filter((row) => Number(row.total || 0) > 0);
  const chartRows = mergeSmallRows(clean, 6, 'Otros');
  const barRows = (contextRows || clean).filter((row) => Number(row.total || 0) > 0);
  const total = Number(totalGeneral || clean.reduce((sum, row) => sum + Number(row.total || 0), 0)) || 1;
  const barTotal = barRows.reduce((sum, row) => sum + Number(row.total || 0), 0) || total;
  const maxShare = Math.max(...barRows.map((row) => (Number(row.total || 0) / barTotal) * 100), 1);
  const selectedLabel = selectedValue ? cleanSecretaria(selectedValue) : '';
  const selectedInTop = selectedLabel && chartRows.some((row) => row.nombre === selectedLabel);
  let offset = 25;

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin secretarias para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-donut-bars">
      <div className="qdf-donut-layout">
        <svg viewBox="0 0 220 220" className="qdf-donut">
          <circle cx="110" cy="110" r="74" className="track" pathLength="100" />
          {chartRows.map((row, index) => {
            const share = (Number(row.total || 0) / total) * 100;
            const currentOffset = offset;
            offset -= share;
            const isSelected = selectedLabel && (selectedInTop ? row.nombre !== selectedLabel : row.nombre !== 'Otros');
            return (
              <circle
                key={row.nombre || index}
                cx="110"
                cy="110"
                r="74"
                className={classNames('slice', isSelected && 'muted')}
                pathLength="100"
                stroke={COLORS[index % COLORS.length]}
                strokeDasharray={`${share} ${100 - share}`}
                strokeDashoffset={currentOffset}
              />
            );
          })}
          <text x="110" y="101" className="donut-title">{centerTitle}</text>
          <text x="110" y="126" className="donut-value">{centerValue}</text>
        </svg>
        <div className="qdf-donut-legend">
          {chartRows.map((row, index) => {
            const isMuted = selectedLabel && (selectedInTop ? row.nombre !== selectedLabel : row.nombre !== 'Otros');
            return (
              <div key={row.nombre || index} className={classNames(isMuted && 'muted')}>
                <i style={{ background: COLORS[index % COLORS.length] }} />
                <span title={row.nombre}>{limitText(row.nombre, 26)}</span>
                <strong>{pct((Number(row.total || 0) / total) * 100)}</strong>
                <em>{moneyCompact(row.total)}</em>
              </div>
            );
          })}
        </div>
      </div>

      <div className="qdf-donut-bars-list">
        {barRows.map((row, index) => {
          const share = (Number(row.total || 0) / barTotal) * 100;
          const width = Math.max(4, (share / maxShare) * 100);
          const color = COLORS[index % COLORS.length];
          const isMuted = selectedLabel && row.nombre !== selectedLabel;
          return (
            <div className={classNames('qdf-donut-bar-row', isMuted && 'muted')} key={`${row.nombre}-${index}`} title={`${row.nombre}: ${moneyCompact(row.total)} (${pct(share)})`}>
              <strong>{limitText(row.nombre || '-', 24)}</strong>
              <div className="qdf-donut-mini-bar"><i style={{ width: `${width}%`, background: color }} /></div>
              <span>{pct(share)}</span>
            </div>
          );
        })}
      </div>
    </div>
  );
}

function mergeSmallRows(rows, limit = 6, label = 'Otras') {
  const clean = (rows || []).filter((row) => Number(row.total || 0) > 0);
  if (clean.length <= limit) return clean;
  const visible = clean.slice(0, limit - 1);
  const rest = clean.slice(limit - 1).reduce((acc, row) => {
    acc.total += Number(row.total || 0);
    acc.facturas += Number(row.facturas || 0);
    return acc;
  }, { nombre: label, total: 0, facturas: 0 });
  return [...visible, rest];
}

function normalizeTopRows(rows, limit = 10, label = 'Resto') {
  const clean = (rows || []).filter((row) => Number(row.total || 0) > 0);
  if (clean.length <= limit) return clean;
  const visible = clean.slice(0, limit - 1);
  const rest = clean.slice(limit - 1).reduce((acc, row) => {
    acc.total += Number(row.total || 0);
    acc.facturas += Number(row.facturas || 0);
    acc.consumo += Number(row.consumo || 0);
    return acc;
  }, { nombre: label, total: 0, facturas: 0, consumo: 0 });
  return [...visible, rest];
}

function SecretariasDistribution({ rows, totalGeneral, year, periodLabel }) {
  const clean = mergeSmallRows(rows, 7, 'Otras');
  const total = Number(totalGeneral || clean.reduce((sum, row) => sum + Number(row.total || 0), 0)) || 1;
  const maxShare = Math.max(...clean.map((row) => (Number(row.total || 0) / total) * 100), 1);

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin secretarias para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-distribution-bars">
      <div className="qdf-dist-summary">
        <span className="qdf-soft-icon">#</span>
        <div>
          <small>Gasto total YTD {year}</small>
          <strong>{moneyCompact(total)}</strong>
        </div>
        <em>{periodLabel}</em>
      </div>

      <div className="qdf-bars-head">
        <span>Secretaria</span>
        <span>Importe (MM)</span>
        <span>% del total</span>
      </div>

      <div className="qdf-secretaria-list">
        {clean.map((row, index) => {
          const share = (Number(row.total || 0) / total) * 100;
          const width = Math.max(3, (share / maxShare) * 100);
          const color = COLORS[index % COLORS.length];
          return (
            <div className="qdf-secretaria-row" key={`${row.nombre}-${index}`} title={`${row.nombre}: ${moneyCompact(row.total)} (${pct(share)})`}>
              <span className="qdf-bubble" style={{ '--row-color': color }}>{secretariaIcon(row.nombre)}</span>
              <strong>{limitText(row.nombre || '-', 22)}</strong>
              <div className="qdf-share-bar">
                <i style={{ width: `${width}%`, background: color }} />
              </div>
              <em>{moneyCompact(row.total)}</em>
              <b style={{ color }}>{pct(share)}</b>
            </div>
          );
        })}
      </div>
      <p className="qdf-chart-note">i Valores expresados en millones de pesos.</p>
    </div>
  );
}

function secretariaIcon(name) {
  const text = String(name || '').toLowerCase();
  if (text.includes('salud')) return 'S';
  if (text.includes('seguridad')) return 'OK';
  if (text.includes('educ')) return 'E';
  if (text.includes('obra') || text.includes('plane')) return '#';
  if (text.includes('desarrollo')) return 'D';
  if (text.includes('ambiente')) return 'A';
  return '...';
}

function Concentration({ value }) {
  const share = Math.min(Math.max(Number(value || 0), 0), 100);
  return (
    <div className="qdf-concentration">
      <svg viewBox="0 0 180 180">
        <circle cx="90" cy="90" r="68" className="track" pathLength="100" />
        <circle cx="90" cy="90" r="68" className="progress" pathLength="100" strokeDasharray={`${share} ${100 - share}`} />
        <text x="90" y="101">{pct(share)}</text>
      </svg>
      <div>
        <h3>Top 5 dependencias</h3>
        <p>representan el {pct(share)} del gasto total filtrado</p>
      </div>
    </div>
  );
}

function TopDependenciasDonut({ rows, totalGeneral }) {
  const [activeIndex, setActiveIndex] = useState(0);
  const clean = (rows || []).slice(0, 10);
  const total = clean.reduce((sum, row) => sum + Number(row.total || 0), 0) || 1;
  const baseTotal = Number(totalGeneral || total) || total;
  const active = clean[activeIndex] || clean[0];
  let offset = 25;

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin dependencias para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-topdep">
      <svg viewBox="0 0 210 210" className="qdf-topdep-donut">
        <circle cx="105" cy="105" r="70" className="track" pathLength="100" />
        {clean.map((row, index) => {
          const share = (Number(row.total || 0) / total) * 100;
          const currentOffset = offset;
          offset -= share;
          return (
            <circle
              key={`${row.nombre}-${index}`}
              cx="105"
              cy="105"
              r="70"
              className={classNames('slice', activeIndex === index && 'active')}
              pathLength="100"
              stroke={COLORS[index % COLORS.length]}
              strokeDasharray={`${share} ${100 - share}`}
              strokeDashoffset={currentOffset}
              onMouseEnter={() => setActiveIndex(index)}
              onClick={() => setActiveIndex(index)}
            >
              <title>{row.nombre}: {moneyCompact(row.total)} ({pct((Number(row.total || 0) / baseTotal) * 100)})</title>
            </circle>
          );
        })}
        <text x="105" y="99" className="topdep-value">{pct((total / baseTotal) * 100)}</text>
        <text x="105" y="122" className="topdep-label">del total</text>
      </svg>
      <div className="qdf-topdep-detail">
        <h3 title={active?.nombre}>{limitText(active?.nombre || '-', 28)}</h3>
        <strong>{moneyCompact(active?.total)}</strong>
        <p>{pct((Number(active?.total || 0) / baseTotal) * 100)} del gasto total filtrado</p>
        <div className="qdf-topdep-list">
          {clean.map((row, index) => (
            <button
              type="button"
              key={`${row.nombre}-item-${index}`}
              className={classNames(activeIndex === index && 'active')}
              onMouseEnter={() => setActiveIndex(index)}
              onClick={() => setActiveIndex(index)}
              title={row.nombre}
            >
              <i style={{ background: COLORS[index % COLORS.length] }} />
              <span>{limitText(row.nombre || '-', 24)}</span>
              <em>{moneyCompact(row.total)}</em>
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}

function TopDependenciasRanking({ rows, totalGeneral }) {
  const clean = (rows || []).slice(0, 10).filter((row) => Number(row.total || 0) > 0);
  const max = Math.max(...clean.map((row) => Number(row.total || 0)), 1);
  const topTotal = clean.reduce((sum, row) => sum + Number(row.total || 0), 0);
  const total = Number(totalGeneral || topTotal) || 1;
  const concentration = (topTotal / total) * 100;

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin dependencias para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-top-ranking">
      <div className="qdf-rank-summary">
        <span className="qdf-soft-icon purple">%</span>
        <div>
          <small>Las 10 dependencias concentran</small>
          <strong>{pct(concentration)}</strong>
        </div>
        <em>del gasto total filtrado</em>
      </div>

      <div className="qdf-rank-head">
        <span>#</span>
        <span>Dependencia</span>
        <span>Importe (MM)</span>
        <span>% del total</span>
      </div>

      <div className="qdf-rank-list">
        {clean.map((row, index) => {
          const amount = Number(row.total || 0);
          const share = (amount / total) * 100;
          const width = Math.max(4, (amount / max) * 100);
          const color = COLORS[index % COLORS.length];
          return (
            <div className="qdf-rank-row" key={`${row.nombre}-${index}`} title={`${row.nombre}: ${moneyCompact(row.total)} (${pct(share)})`}>
              <b style={{ '--row-color': color }}>{index + 1}</b>
              <strong>{limitText(row.nombre || '-', 28)}</strong>
              <div className="qdf-rank-bar"><i style={{ width: `${width}%`, background: color }} /></div>
              <em>{moneyCompact(row.total)}</em>
              <span style={{ color }}>{pct(share)}</span>
            </div>
          );
        })}
      </div>
      <p className="qdf-chart-note">i Valores expresados en millones de pesos.</p>
    </div>
  );
}

function CompactBarRanking({ rows, totalGeneral, limit = 6, numbered = false }) {
  const clean = normalizeTopRows(rows, limit, 'Resto');
  const max = Math.max(...clean.map((row) => Number(row.total || 0)), 1);
  const total = Number(totalGeneral || clean.reduce((sum, row) => sum + Number(row.total || 0), 0)) || 1;

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin datos para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-compact-ranking">
      {clean.map((row, index) => {
        const amount = Number(row.total || 0);
        const share = (amount / total) * 100;
        const width = Math.max(5, (amount / max) * 100);
        return (
          <div className="qdf-compact-row" key={`${row.nombre}-${index}`} title={`${row.nombre}: ${moneyCompact(row.total)} (${pct(share)})`}>
            {numbered && <small>{index + 1}</small>}
            <strong>{limitText(row.nombre || '-', numbered ? 24 : 28)}</strong>
            <div className="qdf-compact-bar"><i style={{ width: `${width}%` }} /></div>
            <em>{moneyCompact(row.total)}</em>
            <span>{pct(share)}</span>
          </div>
        );
      })}
    </div>
  );
}

function MonthlySummary({ rows, totalGeneral }) {
  const clean = normalizeMonthly(rows).filter((row) => Number(row.value || 0) > 0);
  const max = Math.max(...clean.map((row) => Number(row.value || 0)), 1);
  const total = Number(totalGeneral || clean.reduce((sum, row) => sum + Number(row.value || 0), 0)) || 1;

  if (!clean.length) {
    return <div className="qdf-empty-mini">Sin movimientos mensuales para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-compact-ranking qdf-monthly-ranking">
      {clean.map((row, index) => {
        const amount = Number(row.value || 0);
        const share = (amount / total) * 100;
        const width = Math.max(5, (amount / max) * 100);
        return (
          <div className="qdf-compact-row" key={`${row.label}-${index}`} title={`${row.label}: ${moneyCompact(amount)} (${pct(share)})`}>
            <strong>{row.label}</strong>
            <div className="qdf-compact-bar"><i style={{ width: `${width}%` }} /></div>
            <em>{moneyCompact(amount)}</em>
            <span>{pct(share)}</span>
          </div>
        );
      })}
    </div>
  );
}

function ServiceDrivers({ data, selectedSecretaria, onSelectSecretaria }) {
  const rows = data?.comparativo || [];
  const detail = data?.detalle || [];
  const selected = selectedSecretaria
    ? detail.find((row) => row.secretaria === selectedSecretaria)
    : null;

  if (!rows.length) {
    return <div className="qdf-empty-mini">Sin composicion de servicios para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-service-drivers">
      {!selected ? (
        <>
          <p className="qdf-widget-copy">Participacion de los dos servicios principales y el resto por secretaria.</p>
          <div className="qdf-service-bars">
            {rows.slice(0, 7).map((row) => (
              <button type="button" key={row.secretaria} onClick={() => onSelectSecretaria(row.secretaria)} title="Ver composicion interna">
                <span>{limitText(cleanSecretaria(row.secretaria), 24)}</span>
                <div className="qdf-service-stack">
                  <i className="principal" style={{ width: `${Math.max(2, row.principal.porcentaje)}%` }} title={`${row.principal.servicio}: ${pct(row.principal.porcentaje)}`} />
                  <i className="segundo" style={{ width: `${Math.max(2, row.segundo.porcentaje)}%` }} title={`${row.segundo.servicio}: ${pct(row.segundo.porcentaje)}`} />
                  <i className="resto" style={{ width: `${Math.max(2, row.resto.porcentaje)}%` }} title={`Resto: ${pct(row.resto.porcentaje)}`} />
                </div>
                <strong>{row.principal.servicio}</strong>
              </button>
            ))}
          </div>
          <div className="qdf-service-legend">
            <span><i className="principal" />Servicio principal</span>
            <span><i className="segundo" />Segundo servicio</span>
            <span><i className="resto" />Resto</span>
          </div>
        </>
      ) : (
        <ServiceDonut selected={selected} onBack={() => onSelectSecretaria('')} />
      )}
    </div>
  );
}

function ServiceDonut({ selected, onBack }) {
  const servicios = (selected.servicios || []).slice(0, 6);
  const total = servicios.reduce((sum, row) => sum + Number(row.total || 0), 0) || 1;
  let offset = 25;

  return (
    <div className="qdf-service-detail">
      <button type="button" onClick={onBack} className="qdf-back-mini">Volver a todas las secretarias</button>
      <h3 title={selected.secretaria}>{limitText(cleanSecretaria(selected.secretaria), 36)}</h3>
      <div className="qdf-donut-layout compact">
        <svg viewBox="0 0 190 190" className="qdf-donut">
          <circle cx="95" cy="95" r="62" className="track" pathLength="100" />
          {servicios.map((row, index) => {
            const share = (Number(row.total || 0) / total) * 100;
            const currentOffset = offset;
            offset -= share;
            return <circle key={row.servicio} cx="95" cy="95" r="62" className="slice" pathLength="100" stroke={COLORS[index % COLORS.length]} strokeDasharray={`${share} ${100 - share}`} strokeDashoffset={currentOffset} />;
          })}
          <text x="95" y="90" className="donut-title">Total</text>
          <text x="95" y="113" className="donut-value">{moneyCompact(total)}</text>
        </svg>
        <div className="qdf-donut-legend">
          {servicios.map((row, index) => (
            <div key={row.servicio}>
              <i style={{ background: COLORS[index % COLORS.length] }} />
              <span>{row.servicio}</span>
              <strong>{pct((Number(row.total || 0) / total) * 100)}</strong>
              <em>{moneyCompact(row.total)}</em>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

function limitText(value, max) {
  const text = String(value || '');
  return text.length > max ? `${text.slice(0, max - 1)}...` : text;
}

function FinanceTable({ rows, firstColumn, numbered }) {
  const clean = normalizeTopRows(rows, 10, 'Resto');
  const total = clean.reduce((sum, row) => sum + Number(row.total || 0), 0) || 1;
  return (
    <table className="qdf-table">
      <thead>
        <tr>
          <th>{firstColumn}</th>
          <th>Gasto</th>
          <th>% del total</th>
          <th>Variacion</th>
          <th>Tendencia</th>
        </tr>
      </thead>
      <tbody>
        {clean.map((row, index) => (
          <tr key={`${firstColumn}-${row.nombre}-${index}`}>
            <td title={row.nombre || '-'}>{numbered && <small>{index + 1}</small>} {limitText(row.nombre || '-', 32)}</td>
            <td>{moneyCompact(row.total)}</td>
            <td>{pct((Number(row.total || 0) / total) * 100)}</td>
            <td className="positive">+ {pct(row.variacion_porcentual || 0)}</td>
            <td><Sparkline seed={index} /></td>
          </tr>
        ))}
        <tr className="total">
          <td>Total</td>
          <td>{moneyCompact(total)}</td>
          <td>100%</td>
          <td colSpan="2"></td>
        </tr>
      </tbody>
    </table>
  );
}

function Sparkline({ seed }) {
  const points = Array.from({ length: 8 }).map((_, index) => {
    const x = index * 13;
    const y = 22 - ((Math.sin(index + seed) + 1) * 6 + index * 1.2);
    return `${x},${Math.max(3, Math.min(24, y))}`;
  }).join(' ');
  return <svg viewBox="0 0 92 28" className="qdf-spark"><polyline points={points} /></svg>;
}

function Insights({ topSecretaria, topProveedor, topSecretariaPct, topProveedorPct, concentrationPct, yoy }) {
  const insights = [
    { icon: '+', color: 'blue', text: `El gasto total acumulado ${Number(yoy || 0) >= 0 ? 'crecio' : 'bajo'} ${pct(Math.abs(Number(yoy || 0)))} vs. el mismo periodo del anio anterior.` },
    { icon: 'o', color: 'green', text: `${topSecretaria?.nombre || 'La principal secretaria'} concentra ${pct(topSecretariaPct)} del gasto municipal.` },
    { icon: 'o', color: 'purple', text: `${topProveedor?.nombre || 'El principal proveedor'} representa ${pct(topProveedorPct)} del gasto acumulado.` },
    { icon: '#', color: 'orange', text: `5 dependencias concentran el ${pct(concentrationPct)} del gasto total filtrado.` },
  ];
  return (
    <div className="qdf-insights">
      {insights.map((item) => (
        <div className="qdf-insight" key={item.text}>
          <span className={item.color}>{item.icon}</span>
          <p>{item.text}</p>
        </div>
      ))}
    </div>
  );
}

function StateBox({ title, text }) {
  return (
    <div className="qdf-state">
      <h1>{title}</h1>
      <p>{text}</p>
    </div>
  );
}

function EficienciaEnergetica({ data, loading, error, filters, appliedFilters, options, years, onFilter, applyFilters, clearFilters, view, onViewChange }) {
  const safeData = data || {};
  const safeFilters = filters || {};
  const safeAppliedFilters = appliedFilters || safeFilters;
  const safeOptions = options || {};
  const kpis = safeData.kpis || {};
  const dirty = JSON.stringify(safeFilters) !== JSON.stringify(safeAppliedFilters);
  const availableMonths = (safeData.evolucion || []).map((row) => Number(row.mes)).filter(Boolean);
  const actualMonthFrom = availableMonths.length ? Math.min(...availableMonths) : Number(safeAppliedFilters.mes_desde || 1);
  const actualMonthTo = availableMonths.length ? Math.max(...availableMonths) : Number(safeAppliedFilters.mes_hasta || 12);
  const periodLabel = `${MONTHS[actualMonthFrom - 1]}-${MONTHS[actualMonthTo - 1]} ${safeAppliedFilters.anio || ''}`.trim();
  const operationalCutoff = Number(kpis.corte_operativo || 0);
  const operationalPeriodLabel = operationalCutoff
    ? `${monthName(operationalCutoff % 100)} ${Math.floor(operationalCutoff / 100)}`
    : 'Ultimo disponible';

  if (error) return <div className="qdf-error">No se pudo cargar el dashboard de eficiencia: {error}</div>;
  if (!data && loading) return null;

  return (
    <section className="qdf-page qdf-efficiency-page">
      <div className="qdf-title-row">
        <div>
          <h1>Eficiencia Energetica</h1>
          <span>Impacto financiero y gestion operativa de los suministros electricos</span>
        </div>
        <div className="qdf-efficiency-view-tabs">
          <button type="button" className={view === 'financiera' ? 'active' : ''} onClick={() => onViewChange('financiera')}>Vision financiera</button>
          <button type="button" className={view === 'operativa' ? 'active' : ''} onClick={() => onViewChange('operativa')}>Vision operativa</button>
          <button type="button" className={view === 'generacion' ? 'active' : ''} onClick={() => onViewChange('generacion')}>Generacion distribuida</button>
        </div>
      </div>

      <div className={classNames('qdf-card', 'qdf-efficiency-filters', view === 'operativa' && 'is-operational')}>
        {view === 'operativa' ? (
          <>
            <div className="qdf-efficiency-cutoff"><span>Corte operativo</span><strong>{operationalPeriodLabel}</strong><small>ultima condicion conocida</small></div>
            <SelectMini label="Tendencia" value={safeFilters.ventana} options={[{ value: '12', label: 'Ultimos 12 meses' }, { value: '24', label: 'Ultimos 2 anios' }, { value: '36', label: 'Ultimos 2 anios + actual' }]} onChange={(v) => onFilter('ventana', v)} />
            <SelectMini label="Tipo de problema" value={safeFilters.problema} options={[{ value: '', label: 'Todos los problemas' }, { value: 'potencia', label: 'Potencia excedida' }, { value: 'cosfi', label: 'CosFi critico' }, { value: 'tgfi', label: 'TGFI aplicado' }, { value: 'sobredimensionado', label: 'Contrato sobredimensionado' }]} onChange={(v) => onFilter('problema', v)} />
          </>
        ) : (
          <>
            <SelectMini label="Anio" value={safeFilters.anio} options={years || []} onChange={(v) => onFilter('anio', v)} />
            <SelectMini label="Desde" value={safeFilters.mes_desde} options={PERIOD_MONTH_OPTIONS} onChange={(v) => onFilter('mes_desde', v)} />
            <SelectMini label="Hasta" value={safeFilters.mes_hasta} options={PERIOD_MONTH_OPTIONS} onChange={(v) => onFilter('mes_hasta', v)} />
          </>
        )}
        <SelectMini label="Segmento" value={safeFilters.segmento} options={safeOptions.segmentos || [{ value: '', label: 'Todos los segmentos' }]} onChange={(v) => onFilter('segmento', v)} />
        <SelectMini label="Tarifa" value={safeFilters.tarifa} options={safeOptions.tarifas || [{ value: '', label: 'Todas las tarifas' }]} onChange={(v) => onFilter('tarifa', v)} />
        <SelectMini label="Dependencia" value={safeFilters.dependencia} options={formatOptions(safeOptions.dependencias, 'dependencia')} onChange={(v) => onFilter('dependencia', v)} />
        <SelectMini label="Cuenta" value={safeFilters.cuenta} options={safeOptions.cuentas || [{ value: '', label: 'Todas las cuentas' }]} onChange={(v) => onFilter('cuenta', v)} />
        <SelectMini label="Medidor" value={safeFilters.medidor} options={safeOptions.medidores || [{ value: '', label: 'Todos los medidores' }]} onChange={(v) => onFilter('medidor', v)} />
        <div className="qdf-efficiency-filter-actions">
          <button type="button" className="qdf-apply-filter" onClick={applyFilters} disabled={!dirty}>Aplicar consulta</button>
          <button type="button" className="qdf-clear-filter" onClick={clearFilters}>Limpiar</button>
        </div>
      </div>

      {view === 'financiera' && <EfficiencyFinancialView data={safeData} kpis={kpis} periodLabel={periodLabel} />}
      {view === 'operativa' && <EfficiencyOperationalView data={safeData} kpis={kpis} periodLabel={operationalPeriodLabel} problem={safeAppliedFilters.problema} />}
      {view === 'generacion' && <EfficiencyGenerationView data={safeData.generacion || {}} />}
    </section>
  );
}

function EfficiencyFinancialView({ data, kpis, periodLabel }) {
  const [costDependencies, setCostDependencies] = useState([]);
  const toggleCostDependency = (name) => {
    setCostDependencies((current) => current.includes(name)
      ? current.filter((item) => item !== name)
      : current.length < 3 ? [...current, name] : [...current.slice(1), name]);
  };
  const evolution = data.evolucion || [];
  const lastMonth = evolution.length ? evolution[evolution.length - 1] : null;
  const lastMonthLabel = lastMonth ? `${monthName(lastMonth.mes)} ${lastMonth.anio}` : periodLabel;
  const tgfiTotal = Number(kpis?.penalidad_tgfi || 0);
  const potenciaTotal = Number(kpis?.exceso_potencia_t3 || 0) + Number(kpis?.exceso_potencia_t2 || 0);
  const generationKwh = Number(data?.generacion?.kpis?.energia_inyectada || 0);
  const generationAccounts = Number(data?.generacion?.kpis?.cuentas_generadoras || 0);
  const totalElectric = Number(kpis?.importe_total || 0);
  const savingsShare = totalElectric > 0 && Number(kpis?.ahorro_potencial || 0) > 0
    ? Number(kpis.ahorro_potencial) / totalElectric * 100
    : null;

  return (
    <>
      <section className="qdf-efficiency-kpi-story" aria-label="Resumen financiero de eficiencia energetica">
        <EfficiencyKpiGroup title="Perdidas reales" hint="ya pagadas" tone="loss">
          <EfficiencyStoryKpi icon="$" title="Perdido en el periodo" period={periodLabel} value={moneyCompact(kpis?.impacto_identificado)} caption="TGFI + Potencia excedida" description="Dinero efectivamente pagado por penalizaciones y excesos de potencia durante el periodo seleccionado." />
          <EfficiencyStoryKpi icon="#" title="Perdido ultimo mes" period={lastMonthLabel} value={moneyCompact(lastMonth?.impacto_identificado)} caption="TGFI + Potencia excedida" description="Impacto economico detectado en la ultima factura procesada." />
          <EfficiencyStoryKpi icon="TG" title="TGFI acumulado" period={periodLabel} value={moneyCompact(tgfiTotal)} caption="Recargo por bajo factor" description="Recargo TGFI acumulado por bajo factor de potencia." accent="purple" />
          <EfficiencyStoryKpi icon="kW" title="Potencia excedida" period={periodLabel} value={moneyCompact(potenciaTotal)} caption="Cargo por excedentes" description="Costo acumulado por exceder la potencia contratada." accent="orange" />
        </EfficiencyKpiGroup>

        <EfficiencyKpiGroup title="Oportunidades de ahorro" hint="aun recuperables" tone="saving">
          <EfficiencyStoryKpi icon="+" title="Ahorro potencial" period={`${numberCompact(kpis?.ahorro_meses_proyectados)} meses restantes`} value={moneyCompact(kpis?.ahorro_potencial)} caption={`Promedio mensual: ${moneyCompact(kpis?.ahorro_promedio_mensual)}`} description="Proyeccion del sobrecosto corregible ya detectado para el resto del anio." action="Ver metodologia" />
          <EfficiencyStoryKpi icon="#" title="Medidores optimizables" period="Sobrecosto corregible" value={numberCompact(kpis?.contratos_con_oportunidad)} caption="Con oportunidad" description="Medidores con TGFI o potencia excedida que podrian evitar sobrecostos futuros." />
          <EfficiencyStoryKpi icon="!" title="Dependencias prioritarias" period="Mayor impacto economico" value={numberCompact(kpis?.dependencias_criticas)} caption="Prioridad de intervencion" description="Dependencias con mayor impacto economico y prioridad de intervencion." />
          <EfficiencyStoryKpi icon="kWh" title="Generacion distribuida" period="Energia inyectada" value={`${numberCompact(generationKwh)} kWh`} caption={`${numberCompact(generationAccounts)} cuentas generadoras`} description="Energia inyectada a la red por dependencias municipales." />
        </EfficiencyKpiGroup>
      </section>

      <section className="qdf-efficiency-mock-row-two">
        <EfficiencyMockPanel title="Donde se pierde dinero" titleHint="perdidas reales" subtitle={`Composicion de las perdidas reales en el periodo (${periodLabel})`}>
          <EfficiencyMockLossBreakdown kpis={kpis} />
        </EfficiencyMockPanel>
        <EfficiencyMockPanel title="Consumo vs costo unitario" subtitle="Permite distinguir si el aumento del gasto responde a consumo o tarifa.">
          <EfficiencyMockConsumptionCost rows={evolution} />
        </EfficiencyMockPanel>
        <EfficiencyMockPanel title="Composicion del costo electrico" titleHint="del gasto total" subtitle="Participacion de cada concepto en el gasto total del periodo.">
          <EfficiencyMockCostComposition data={data} selected={costDependencies} onToggle={toggleCostDependency} />
        </EfficiencyMockPanel>
      </section>

      <section className="qdf-efficiency-mock-row-three">
        <EfficiencyMockPanel title="Top dependencias por impacto economico" titleHint="perdidas reales" subtitle={`Ordenado por perdidas reales en el periodo (${periodLabel})`}>
          <EfficiencyMockDependencies rows={data.top_dependencias || []} selected={costDependencies} onSelect={toggleCostDependency} />
        </EfficiencyMockPanel>
        <EfficiencyMockPanel title="Evolucion de perdidas reales" subtitle={`Millones de $ por mes (${periodLabel})`}>
          <EfficiencyMockLossEvolution rows={evolution} />
        </EfficiencyMockPanel>
        <EfficiencyMockPanel title="De donde sale el ahorro potencial" subtitle="Desglose del ahorro potencial anual estimado.">
          <EfficiencyMockSavingsOrigin kpis={kpis} />
        </EfficiencyMockPanel>
      </section>
      <EfficiencyMockInfoRow kpis={kpis} savingsShare={savingsShare} />
    </>
  );
}

function EfficiencyKpiGroup({ title, hint, tone, children }) {
  return (
    <div className={`qdf-efficiency-kpi-group ${tone}`}>
      <header><strong>{title}</strong><span>({hint})</span></header>
      <div>{children}</div>
    </div>
  );
}

function EfficiencyStoryKpi({ icon, title, period, value, caption, description, accent, action }) {
  return (
    <article className={`qdf-efficiency-story-kpi ${accent || ''} ${String(value).length > 8 ? 'compact-value' : ''}`}>
      <header><strong>{title}</strong><small>{period}</small></header>
      <div className="qdf-efficiency-story-value"><i>{icon}</i><b>{value}</b></div>
      <span>{caption}</span>
      <p>{description}</p>
      {action && <button type="button">{action}</button>}
    </article>
  );
}

function EfficiencyMockPanel({ title, titleHint, subtitle, children }) {
  return (
    <article className="qdf-efficiency-mock-panel">
      <header><h2>{title} {titleHint && <small>({titleHint})</small>}</h2><p>{subtitle}</p></header>
      <div>{children}</div>
    </article>
  );
}

function efficiencyLossRows(kpis) {
  const potencia = Number(kpis?.exceso_potencia_t3 || 0) + Number(kpis?.exceso_potencia_t2 || 0);
  const tgfi = Number(kpis?.penalidad_tgfi || 0);
  const total = Math.max(0, Number(kpis?.impacto_identificado || 0));
  const other = Math.max(0, total - potencia - tgfi);
  return [
    { label: 'Potencia excedida', value: potencia, color: '#e51e2a' },
    { label: 'TGFI', value: tgfi, color: '#7545bd' },
    { label: 'Otros cargos identificados', value: other, color: '#56647a' },
  ].filter((row) => row.value > 0 || total === 0);
}

function EfficiencyMockLossBreakdown({ kpis }) {
  const rows = efficiencyLossRows(kpis);
  const total = rows.reduce((sum, row) => sum + Number(row.value || 0), 0);
  const max = Math.max(1, ...rows.map((row) => Number(row.value || 0)));
  const axisMax = Math.max(1, Math.ceil(max / 1000000 / 10) * 10 * 1000000);
  return (
    <div className="qdf-mock-loss-chart">
      <div className="qdf-mock-loss-grid">
        {rows.map((row) => {
          const share = total > 0 ? Number(row.value || 0) / total * 100 : 0;
          return <div key={row.label}><strong>{row.label}</strong><span><i style={{ width: `${Math.max(2, Number(row.value || 0) / axisMax * 100)}%`, background: row.color }} /></span><b>{moneyCompact(row.value)}</b><em>{pct(share)}</em></div>;
        })}
      </div>
      <footer><span>0</span><span>{moneyCompact(axisMax * .33).replace('$ ', '')}</span><span>{moneyCompact(axisMax * .66).replace('$ ', '')}</span><span>{moneyCompact(axisMax).replace('$ ', '')}</span><small>Millones de $</small></footer>
    </div>
  );
}

function EfficiencyMockConsumptionCost({ rows }) {
  const clean = (rows || []).map((row) => ({
    label: `${MONTHS[Number(row.mes) - 1] || ''} '${String(row.anio || '').slice(-2)}`,
    consumo: Number(row.consumo_kwh || 0),
    costo: Number(row.costo_unitario || 0),
  })).filter((row) => row.label.trim());
  if (!clean.length) return <div className="qdf-empty-mini">Sin consumos validos para el periodo.</div>;
  const maxConsumption = Math.max(1, ...clean.map((row) => row.consumo));
  const maxCost = Math.max(1, ...clean.map((row) => row.costo));
  const chartX = (index) => 55 + (clean.length <= 1 ? 0 : index * (255 / (clean.length - 1)));
  const consumptionY = (value) => 142 - value / maxConsumption * 105;
  const costY = (value) => 142 - value / maxCost * 105;
  const points = (values, mapper) => values.map((value, index) => `${chartX(index)},${mapper(value)}`).join(' ');
  return (
    <div className="qdf-mock-dual-line">
      <div className="qdf-mock-chart-legend"><span className="blue">Consumo (kWh)</span><span className="green">Costo unitario ($/kWh)</span></div>
      <svg viewBox="0 0 350 180" role="img" aria-label="Consumo y costo unitario">
        {[37, 63, 89, 115, 142].map((y) => <line key={y} x1="45" y1={y} x2="315" y2={y} className="grid" />)}
        <polyline points={points(clean.map((row) => row.consumo), consumptionY)} className="consumption-line" />
        <polyline points={points(clean.map((row) => row.costo), costY)} className="cost-line" />
        {clean.map((row, index) => <circle key={`c-${index}`} cx={chartX(index)} cy={consumptionY(row.consumo)} r="2.7" className="consumption-dot" />)}
        {clean.map((row, index) => <circle key={`u-${index}`} cx={chartX(index)} cy={costY(row.costo)} r="2.7" className="cost-dot" />)}
        {[0, .25, .5, .75, 1].map((ratio) => <text key={ratio} x="39" y={142 - ratio * 105 + 3} textAnchor="end">{numberCompact(maxConsumption * ratio)}</text>)}
        {[0, .25, .5, .75, 1].map((ratio) => <text key={ratio} x="321" y={142 - ratio * 105 + 3}>{numberCompact(maxCost * ratio)}</text>)}
        {clean.map((row, index) => <text key={row.label} x={chartX(index)} y="163" textAnchor="middle">{row.label}</text>)}
        <text x="10" y="93" transform="rotate(-90 10 93)" className="axis-title">kWh</text>
        <text x="344" y="93" transform="rotate(90 344 93)" className="axis-title">$/kWh</text>
      </svg>
    </div>
  );
}

function compositionValues(source) {
  const energia = Number(source?.energia_variable || 0);
  const potenciaContratada = Number(source?.potencia_contratada || 0) + Number(source?.potencia_adquirida || 0) + Number(source?.cargo_fijo || 0);
  const potenciaExcedida = Number(source?.potencia_excedida || 0);
  const tgfi = Number(source?.tgfi || 0);
  const impuestos = Number(source?.otros_impuestos || 0);
  return [energia, potenciaContratada, potenciaExcedida, tgfi, impuestos, 0];
}

function compositionPercentages(values) {
  const total = values.reduce((sum, value) => sum + Number(value || 0), 0);
  if (total <= 0) return [0, 0, 0, 0, 0, 0];
  return values.map((value) => Number(value || 0) / total * 100);
}

function EfficiencyMockCostComposition({ data, selected, onToggle }) {
  const colors = ['#0759c7', '#10a4ae', '#ef2d34', '#7344bd', '#f4a000', '#5c6068'];
  const labels = ['Energia', 'Potencia contratada', 'Potencia excedida', 'TGFI', 'Impuestos', 'Otros'];
  const dependencies = data?.composicion_dependencias || [];
  const selectedRows = (selected || []).map((name) => dependencies.find((row) => row.dependencia === name)).filter(Boolean);
  const municipality = { name: 'Municipio total', values: compositionPercentages(compositionValues(data?.composicion_costo || {})), benchmark: true };
  const rows = [municipality, ...selectedRows.map((row) => ({ name: formatDimensionName(row.dependencia, 'dependencia'), values: compositionPercentages(compositionValues(row)) }))];
  const available = dependencies.filter((row) => !(selected || []).includes(row.dependencia));
  return (
    <div className="qdf-mock-composition">
      <div className="qdf-mock-composition-control">
        <select value="" onChange={(event) => event.target.value && onToggle(event.target.value)} disabled={(selected || []).length >= 3 || !available.length}>
          <option value="">{(selected || []).length >= 3 ? 'Maximo 3 dependencias' : 'Agregar dependencia para comparar'}</option>
          {available.map((row) => <option key={row.dependencia} value={row.dependencia}>{formatDimensionName(row.dependencia, 'dependencia')}</option>)}
        </select>
        <div>{(selected || []).map((name) => <button key={name} type="button" onClick={() => onToggle(name)} title={`Quitar ${formatDimensionName(name, 'dependencia')}`}>{limitText(formatDimensionName(name, 'dependencia'), 19)} x</button>)}</div>
      </div>
      <div className="qdf-mock-composition-legend">{labels.map((label, index) => <span key={label}><i style={{ background: colors[index] }} />{label}</span>)}</div>
      <div className="qdf-mock-composition-bars">{rows.map((row) => <div key={row.name} className={row.benchmark ? 'benchmark' : ''}><strong>{row.name}</strong><span>{row.values.map((value, index) => <i key={`${row.name}-${index}`} style={{ width: `${Math.max(value > 0 ? 2 : 0, value)}%`, background: colors[index] }}>{value >= 3 ? pct(value) : ''}</i>)}</span></div>)}</div>
      <footer>{[0, 20, 40, 60, 80, 100].map((value) => <span key={value}>{value}%</span>)}</footer>
    </div>
  );
}

function EfficiencyMockDependencies({ rows, selected, onSelect }) {
  const clean = (rows || []).slice(0, 5);
  const max = Math.max(1, ...clean.map((row) => Number(row.impacto_identificado || 0)));
  return (
    <div className="qdf-mock-dependencies">
      <header><span>#</span><span>Dependencia</span><span>Perdida total</span></header>
      {clean.map((row, index) => {
        const name = row.dependencia || 'SIN DEPENDENCIA';
        return <button type="button" className={(selected || []).includes(name) ? 'selected' : ''} key={name} onClick={() => onSelect(name)} title={`Comparar composicion de ${formatDimensionName(name, 'dependencia')}`}><i>{index + 1}</i><strong>{formatDimensionName(name, 'dependencia')}</strong><span><i style={{ width: `${Math.max(3, Number(row.impacto_identificado || 0) / max * 100)}%`, background: COLORS[index % COLORS.length] }} /></span><b>{moneyCompact(row.impacto_identificado)}</b></button>;
      })}
      {!clean.length && <div className="qdf-empty-mini">Sin dependencias con impacto identificado.</div>}
      <footer><span>0</span><span>{moneyCompact(max * .25).replace('$ ', '')}</span><span>{moneyCompact(max * .5).replace('$ ', '')}</span><span>{moneyCompact(max * .75).replace('$ ', '')}</span><span>{moneyCompact(max).replace('$ ', '')}</span><small>Millones de $</small></footer>
    </div>
  );
}

function EfficiencyMockLossEvolution({ rows }) {
  const clean = (rows || []).map((row) => ({
    month: `${MONTHS[Number(row.mes) - 1] || ''} '${String(row.anio || '').slice(-2)}`,
    power: Number(row.exceso_potencia || 0),
    tgfi: Number(row.penalidad_tgfi || 0),
    other: Math.max(0, Number(row.impacto_identificado || 0) - Number(row.exceso_potencia || 0) - Number(row.penalidad_tgfi || 0)),
  })).filter((row) => row.month.trim());
  const max = Math.max(1, ...clean.map((row) => row.power + row.tgfi + row.other));
  if (!clean.length) return <div className="qdf-empty-mini">Sin perdidas tecnicas para el periodo.</div>;
  return (
    <div className="qdf-mock-loss-evolution">
      <div className="qdf-mock-loss-evolution-legend"><span className="power">Potencia excedida</span><span className="tgfi">TGFI</span><span className="other">Otros</span></div>
      <div className="qdf-mock-loss-evolution-chart">
        <aside><span>{moneyCompact(max).replace('$ ', '')}</span><span>{moneyCompact(max * .75).replace('$ ', '')}</span><span>{moneyCompact(max * .5).replace('$ ', '')}</span><span>{moneyCompact(max * .25).replace('$ ', '')}</span><span>0</span><small>Millones de $</small></aside>
        <section>{clean.map((row) => <div key={row.month}><span title={`${row.month}: ${moneyCompact(row.power + row.tgfi + row.other)}`}><i className="other" style={{ height: `${row.other / max * 100}%` }} /><i className="tgfi" style={{ height: `${row.tgfi / max * 100}%` }} /><i className="power" style={{ height: `${row.power / max * 100}%` }} /></span><strong>{row.month}</strong></div>)}</section>
      </div>
    </div>
  );
}

function EfficiencyMockSavingsOrigin({ kpis }) {
  const projected = Number(kpis?.ahorro_potencial || 0);
  const monthly = Number(kpis?.ahorro_promedio_mensual || 0);
  const paid = Number(kpis?.sobrecosto_corregible_periodo || 0);
  const total = projected;
  const rows = [
    { id: 'idle', label: 'Proyeccion resto del anio', value: projected },
    { id: 'tgfi', label: 'Promedio mensual base', value: monthly },
    { id: 'rate', label: 'Sobrecosto ya pagado', value: paid },
  ].filter((row) => row.value > 0 || total === 0);
  return (
    <div className="qdf-mock-savings-origin">
      <div className="qdf-mock-savings-donut"><div><strong>{moneyCompact(total)}</strong><span>Ahorro potencial<br />resto del anio</span></div></div>
      <div className="qdf-mock-savings-detail">
        <ul>
          {rows.map((row) => <li key={row.id}><i className={row.id} /><span>{row.label}</span><b>{row.id === 'idle' ? `${numberCompact(kpis?.ahorro_meses_proyectados)} meses` : row.id === 'tgfi' ? 'base' : 'periodo'}</b><em>{moneyCompact(row.value)}</em></li>)}
        </ul>
        <p>El ahorro potencial proyecta el promedio mensual del sobrecosto corregible por los meses restantes del anio.</p>
      </div>
    </div>
  );
}

function EfficiencyMockInfoRow({ kpis, savingsShare }) {
  return (
    <section className="qdf-efficiency-info-row">
      <article className="loss">
        <i>$</i>
        <div><h3>Como leer estos numeros</h3><p><strong>Perdidas reales:</strong> dinero ya pagado en el periodo.<br /><strong>Ahorro potencial:</strong> sobrecosto futuro evitable si se corrigen las causas detectadas.</p></div>
      </article>
      <article className="saving">
        <i>%</i>
        <div><h3>Equivalencia economica</h3><p>El ahorro potencial de <strong>{moneyCompact(kpis?.ahorro_potencial)}</strong>{savingsShare !== null && <> equivale a <strong>{pct(savingsShare)}</strong> del gasto electrico del periodo.</>}</p></div>
      </article>
      <article className="method">
        <i>#</i>
        <div><h3>Metodologia del ahorro</h3><p>Promedio mensual de TGFI y potencia excedida detectados, multiplicado por los meses restantes del anio. No recupera importes ya pagados.</p></div>
      </article>
      <article className="data">
        <i>i</i>
        <div><h3>Datos del analisis</h3><ul><li>Facturas analizadas: <strong>{numberCompact(kpis?.facturas)}</strong></li><li>Medidores analizados: <strong>{numberCompact(kpis?.medidores)}</strong></li><li>Medidores observados: <strong>{numberCompact(kpis?.medidores_observados)}</strong></li><li>Distribuidora: <strong>Edenor</strong></li></ul></div>
      </article>
    </section>
  );
}

function EfficiencyOperationalView({ data, kpis, periodLabel, problem }) {
  const rows = data.operativa || [];
  return (
    <>
      <EfficiencyOperationalMockKpis kpis={kpis} periodLabel={periodLabel} />
      <EfficiencyOperationalMockRowTwo data={data} kpis={kpis} problem={problem} />

      <EfficiencyOperationalMockActionPlan rows={rows} />
      <EfficiencyOperationalMockRankings data={data} />
      <EfficiencyOperationalMockFooter kpis={kpis} />
    </>
  );
}

function EfficiencyOperationalMockKpis({ kpis, periodLabel }) {
  const total = Number(kpis?.medidores || 0);
  const cards = [
    { icon: '#', title: 'Medidores analizados', value: numberCompact(total), subtitle: `corte ${periodLabel}`, tone: 'blue' },
    { icon: '!', title: 'Medidores observados', value: numberCompact(kpis?.medidores_observados), subtitle: `sobre ${numberCompact(total)} analizados`, tone: 'orange' },
    { icon: 'cos', title: 'CosFi critico', value: numberCompact(kpis?.medidores_cosfi_critico), subtitle: 'factor de potencia < 0,85', tone: 'purple' },
    { icon: 'TG', title: 'Medidores con TGFI', value: numberCompact(kpis?.medidores_con_tgfi), subtitle: 'con recargo por bajo factor', tone: 'amber' },
    { icon: 'kW', title: 'Potencia excedida', value: numberCompact(kpis?.medidores_potencia_excedida), subtitle: 'requieren revision contractual', tone: 'red' },
    { icon: '%', title: 'Contratos sobredimensionados', value: numberCompact(kpis?.contratos_sobredimensionados), subtitle: 'utilizacion < 60%', tone: 'green' },
  ];
  return (
    <section className="qdf-operational-mock-kpis">
      {cards.map((card) => <EfficiencyOperationalMockKpi key={card.title} {...card} />)}
    </section>
  );
}

function EfficiencyOperationalMockKpi({ icon, title, value, subtitle, tone }) {
  return (
    <article className={`qdf-operational-mock-kpi ${tone}`}>
      <i>{icon}</i>
      <div><h3>{title}</h3><strong>{value}</strong><p>{subtitle}</p></div>
      <button type="button">Ver detalle <span>-&gt;</span></button>
    </article>
  );
}

function EfficiencyOperationalMockRowTwo({ data, kpis, problem }) {
  return (
    <section className="qdf-operational-mock-row-two">
      <EfficiencyMockPanel title="Distribucion de problemas" titleHint="" subtitle="">
        <EfficiencyOperationalProblemDistribution rows={data?.distribucion_problemas || []} total={kpis?.medidores_observados} />
      </EfficiencyMockPanel>
      <EfficiencyMockPanel title="Impacto economico por tipo de problema" subtitle="Estimacion anualizada sobre los ultimos 12 meses.">
        <EfficiencyOperationalImpactBars rows={data?.impacto_problemas || []} />
      </EfficiencyMockPanel>
      <EfficiencyMockPanel title="Evolucion de incidencias detectadas" subtitle="Cantidad de medidores con problemas por mes.">
        <EfficiencyOperationalIncidentTrend rows={data?.evolucion_operativa || []} problem={problem} />
      </EfficiencyMockPanel>
    </section>
  );
}

function operationalProblemColor(problem) {
  if (problem === 'Potencia excedida') return '#e51e2a';
  if (problem === 'TGFI aplicado') return '#7344bd';
  if (problem === 'CosFi critico') return '#9b6bd5';
  if (problem === 'Contrato sobredimensionado') return '#22984c';
  return '#556273';
}

function EfficiencyOperationalProblemDistribution({ rows, total }) {
  const count = Number(total || (rows || []).reduce((sum, row) => sum + Number(row.medidores || 0), 0));
  let cursor = 0;
  const items = (rows || []).map((row) => {
    const share = count > 0 ? Number(row.medidores || 0) / count * 100 : 0;
    const start = cursor;
    cursor += share;
    return { ...row, share, start, end: cursor, color: operationalProblemColor(row.problema) };
  });
  const background = items.length
    ? `conic-gradient(${items.map((item) => `${item.color} ${item.start}% ${item.end}%`).join(', ')})`
    : '#edf2f7';
  return (
    <div className="qdf-operational-problem-distribution">
      <div className="qdf-operational-problem-donut" style={{ background }}><div><strong>{numberCompact(count)}</strong><span>Total medidores<br />con problemas</span></div></div>
      <ul>{items.map((item) => <li key={item.problema}><i style={{ background: item.color }} /><span>{item.problema}</span><b>{pct(item.share)} ({numberCompact(item.medidores)})</b></li>)}</ul>
      <button type="button">Ver detalle -&gt;</button>
    </div>
  );
}

function EfficiencyOperationalImpactBars({ rows }) {
  const max = Math.max(1, ...(rows || []).map((row) => Number(row.impacto || 0)));
  const total = (rows || []).reduce((sum, row) => sum + Number(row.impacto || 0), 0);
  return (
    <div className="qdf-operational-impact-bars">
      <div>{(rows || []).map((row) => <div key={row.problema}><strong>{row.problema}</strong><span><i style={{ width: `${Math.max(2, Number(row.impacto || 0) / max * 100)}%`, background: operationalProblemColor(row.problema) }} /></span><b>{moneyCompact(row.impacto)} ({pct(total > 0 ? Number(row.impacto || 0) / total * 100 : 0)})</b></div>)}</div>
      <footer><span>$ 0</span><span>{moneyCompact(max * .25)}</span><span>{moneyCompact(max * .5)}</span><span>{moneyCompact(max * .75)}</span><span>{moneyCompact(max)}</span><small>Impacto anual estimado</small></footer>
      <button type="button">Ver detalle -&gt;</button>
    </div>
  );
}

function EfficiencyOperationalIncidentTrend({ rows, problem }) {
  const metric = ({ potencia: 'potencia_excedida', cosfi: 'cosfi_critico', tgfi: 'tgfi_aplicado', sobredimensionado: 'sobredimensionados' })[problem] || 'medidores_observados';
  const grouped = new Map();
  (rows || []).forEach((row) => {
    const year = Number(row.anio);
    if (!grouped.has(year)) grouped.set(year, []);
    grouped.get(year).push({ month: Number(row.mes), value: Number(row[metric] || 0) });
  });
  const colors = ['#1761c8', '#22a66f', '#7344bd'];
  const series = Array.from(grouped.entries())
    .filter(([, points]) => points.filter((point) => point.value > 0).length >= 2)
    .sort((a, b) => a[0] - b[0])
    .slice(-3)
    .map(([year, points], index) => ({ year, points: points.sort((a, b) => a.month - b.month), color: colors[index] }));
  const maxValue = Math.max(1, ...series.flatMap((item) => item.points.map((point) => point.value)));
  const axisMax = Math.max(10, Math.ceil(maxValue / 10) * 10);
  const x = (month) => 48 + ((month - 1) / 11) * 286;
  const y = (value) => 138 - (value / axisMax) * 108;
  const ticks = [0, .25, .5, .75, 1].map((ratio) => Math.round(axisMax * ratio));
  return (
    <div className="qdf-operational-incident-trend">
      <div className="qdf-operational-trend-legend">{series.map((item) => <span key={item.year}><i style={{ background: item.color }} />{item.year}</span>)}</div>
      <svg viewBox="0 0 360 175" role="img" aria-label="Evolucion de medidores con incidencias detectadas">
        {ticks.map((value) => <g key={value}><line x1="45" y1={y(value)} x2="338" y2={y(value)} /><text x="38" y={y(value) + 3} textAnchor="end">{value}</text></g>)}
        {series.map((item) => <g key={item.year} className="qdf-operational-trend-series"><polyline style={{ stroke: item.color }} points={item.points.map((point) => `${x(point.month)},${y(point.value)}`).join(' ')} />{item.points.map((point) => <circle key={`${item.year}-${point.month}`} style={{ fill: item.color }} cx={x(point.month)} cy={y(point.value)} r="3" />)}</g>)}
        {MONTHS.map((month, index) => <text key={month} x={x(index + 1)} y="157" textAnchor="middle">{month}</text>)}
      </svg>
      <button type="button">Ver detalle -&gt;</button>
    </div>
  );
}

function EfficiencyOperationalMockActionPlan({ rows }) {
  const [problem, setProblem] = useState('');
  const [priority, setPriority] = useState('');
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const pageSize = 10;
  const query = search.trim().toLocaleLowerCase('es');
  const filtered = (rows || []).filter((row) => (!problem || row.problema_principal === problem)
    && (!priority || row.prioridad === priority)
    && (!query || [row.dependencia, row.nro_cuenta, row.nro_medidor, row.problema_principal, row.accion_sugerida].some((value) => String(value || '').toLocaleLowerCase('es').includes(query))));
  const pageCount = Math.max(1, Math.ceil(filtered.length / pageSize));
  const currentPage = Math.min(page, pageCount);
  const visibleRows = filtered.slice((currentPage - 1) * pageSize, currentPage * pageSize);
  const pageButtons = Array.from({ length: Math.min(5, pageCount) }, (_, index) => index + 1);
  return (
    <section className="qdf-operational-action-plan">
      <header>
        <div><h2>Plan de accion operativo <small>i</small></h2><p>Ordenado por prioridad y mayor impacto economico estimado.</p></div>
        <div className="qdf-operational-action-filters">
          <label>Filtrar por problema<select value={problem} onChange={(event) => { setProblem(event.target.value); setPage(1); }}><option value="">Todos</option>{[...new Set((rows || []).map((row) => row.problema_principal))].map((item) => <option key={item} value={item}>{item}</option>)}</select></label>
          <label>Filtrar por prioridad<select value={priority} onChange={(event) => { setPriority(event.target.value); setPage(1); }}><option value="">Todas</option><option value="Alta">Alta</option><option value="Media">Media</option><option value="Baja">Baja</option></select></label>
          <label className="search"><span>?</span><input type="search" value={search} onChange={(event) => { setSearch(event.target.value); setPage(1); }} placeholder="Buscar..." /></label>
          <button type="button" title="Configurar columnas">Cols</button>
        </div>
      </header>
      <div className="qdf-operational-action-table-wrap">
        <table>
          <thead><tr><th>Prioridad</th><th>Dependencia</th><th>Cuenta</th><th>Medidor</th><th>Tarifa</th><th>Problema detectado</th><th>Accion sugerida</th><th>Impacto anual estimado</th><th>Estado</th><th /></tr></thead>
          <tbody>{visibleRows.map((row) => <tr key={`${row.nro_cuenta}-${row.nro_medidor}`}><td><span className={`priority ${row.prioridad.toLowerCase()}`}><i />{row.prioridad}</span></td><td><strong>{row.dependencia}</strong></td><td>{row.nro_cuenta}</td><td>{row.nro_medidor}</td><td>{row.tipo_de_tarifa}</td><td><strong>{row.problema_principal}</strong> <small>({row.detalle_problema})</small></td><td>{row.accion_sugerida}</td><td><strong>{moneyCompact(row.impacto_anual_estimado)}</strong></td><td><span className="status detected">{row.estado}</span></td><td><button type="button" title="Mas acciones">...</button></td></tr>)}</tbody>
        </table>
        {!filtered.length && <div className="qdf-empty-mini">No hay acciones que coincidan con los filtros.</div>}
      </div>
      <footer><span>Mostrando {filtered.length ? ((currentPage - 1) * pageSize) + 1 : 0} a {Math.min(currentPage * pageSize, filtered.length)} de {filtered.length} resultados</span><nav><button type="button" disabled={currentPage <= 1} onClick={() => setPage((value) => Math.max(1, value - 1))}>&lt;</button>{pageButtons.map((value) => <button type="button" key={value} className={value === currentPage ? 'active' : ''} onClick={() => setPage(value)}>{value}</button>)}{pageCount > 5 && <span>... {pageCount}</span>}<button type="button" disabled={currentPage >= pageCount} onClick={() => setPage((value) => Math.min(pageCount, value + 1))}>&gt;</button></nav></footer>
    </section>
  );
}

function EfficiencyOperationalMockRankings({ data }) {
  const dependencies = (data?.top_dependencias || []).slice(0, 5);
  const meters = (data?.top_medidores || []).slice(0, 5);
  const accounts = (data?.top_cuentas || []).slice(0, 5);
  const maxDependency = Math.max(1, ...dependencies.map((row) => Number(row.impacto || 0)));
  const maxAccount = Math.max(1, ...accounts.map((row) => Number(row.impacto || 0)));
  return (
    <section className="qdf-operational-mock-rankings">
      <OperationalRankingCard title="Top 5 dependencias afectadas" action="Ver todas las dependencias">
        <div className="qdf-operational-dependency-ranking"><header><span>#</span><span>Dependencia</span><span>Medidores con problemas</span><span>Impacto anual estimado</span></header>{dependencies.map((row, index) => <div key={row.dependencia}><i>{index + 1}</i><strong title={row.dependencia}>{row.dependencia}</strong><span><i style={{ width: `${Number(row.impacto || 0) / maxDependency * 100}%`, background: COLORS[index % COLORS.length] }} /></span><b>{numberCompact(row.medidores)}</b><em>{moneyCompact(row.impacto)}</em></div>)}</div>
      </OperationalRankingCard>
      <OperationalRankingCard title="Top 5 medidores con problemas" action="Ver todos los medidores">
        <div className="qdf-operational-meter-ranking"><header><span>#</span><span>Medidor</span><span>Problema principal</span><span>Impacto anual estimado</span></header>{meters.map((row, index) => <div key={`${row.nro_cuenta}-${row.nro_medidor}`}><i>{index + 1}</i><strong title={`${row.dependencia} | ${row.nro_cuenta}`}>{row.nro_medidor}</strong><span title={row.problema_principal}><i style={{ background: operationalProblemColor(row.problema_principal) }} />{row.problema_principal}</span><em>{moneyCompact(row.impacto_anual_estimado)}</em></div>)}</div>
      </OperationalRankingCard>
      <OperationalRankingCard title="Top 5 cuentas con problemas" action="Ver todas las cuentas">
        <div className="qdf-operational-account-ranking"><header><span>#</span><span>Cuenta</span><span>Medidores con problemas</span><span>Impacto anual estimado</span></header>{accounts.map((row, index) => <div key={row.cuenta}><i>{index + 1}</i><strong>{row.cuenta}</strong><span><i style={{ width: `${Number(row.impacto || 0) / maxAccount * 100}%`, background: COLORS[index % COLORS.length] }} /></span><b>{numberCompact(row.medidores)}</b><em>{moneyCompact(row.impacto)}</em></div>)}</div>
      </OperationalRankingCard>
    </section>
  );
}

function OperationalRankingCard({ title, action, children }) {
  return <article className="qdf-operational-ranking-card"><h2>{title} <small>i</small></h2>{children}<button type="button">{action} -&gt;</button></article>;
}

function EfficiencyOperationalMockFooter({ kpis }) {
  const items = [
    { icon: 'kW', title: 'Potencia excedida', text: 'La potencia registrada supera la contratada. Genera cargos adicionales.', tone: 'red' },
    { icon: 'cos', title: 'CosFi critico', text: 'Factor de potencia inferior a 0,85. Genera recargo TGFI en la factura.', tone: 'purple' },
    { icon: '%', title: 'Contrato sobredimensionado', text: 'La potencia contratada es muy superior a la utilizada. Hay oportunidad de ahorro.', tone: 'green' },
    { icon: 'TG', title: 'TGFI aplicado', text: 'La factura incluye un recargo economico asociado al bajo factor de potencia.', tone: 'amber' },
  ];
  return (
    <section className="qdf-operational-mock-footer">
      <article className="interpretation"><h2>Como interpretar los problemas</h2><div>{items.map((item) => <div key={item.title} className={item.tone}><i>{item.icon}</i><span><strong>{item.title}</strong><p>{item.text}</p></span></div>)}</div></article>
      <article className="methodology"><i>i</i><div><h2>Metodologia</h2><p>Se analizaron {numberCompact(kpis?.medidores)} medidores y se detectaron {numberCompact(kpis?.medidores_observados)} con incidencias. Los impactos economicos estan anualizados sobre los ultimos 12 meses.</p></div></article>
    </section>
  );
}

function EfficiencyGenerationView({ data }) {
  const kpis = data.kpis || {};
  const principal = (data.ranking || [])[0] || {};
  const period = kpis.periodo_desde && kpis.periodo_hasta ? `${kpis.periodo_desde} a ${kpis.periodo_hasta}` : 'Sin periodo disponible';
  return (
    <>
      <div className="qdf-generation-notice">
        <strong>Historico de generacion distribuida</strong>
        <span>Los valores reflejan energia inyectada registrada. El valor economico es equivalente estimado, no credito tarifario confirmado.</span>
      </div>
      <section className="qdf-kpis qdf-efficiency-kpis qdf-generation-kpis">
        <Kpi icon="kWh" title="Energia inyectada" value={`${numberCompact(kpis.energia_inyectada)} kWh`} subtitle={period} color="green" />
        <Kpi icon="#" title="Cuentas generadoras" value={numberCompact(kpis.cuentas_generadoras)} subtitle={`${numberCompact(kpis.registros)} registros`} color="blue" />
        <Kpi icon="+" title="Principal generador" value={`${numberCompact(principal.energia_inyectada)} kWh`} subtitle={limitText(principal.dependencia || 'Sin datos', 34)} color="purple" />
      </section>
      <section className="qdf-efficiency-main-grid">
        <Panel title="Evolucion de energia inyectada" subtitle={period}>
          <EfficiencyGenerationEvolution rows={data.evolucion || []} />
        </Panel>
        <Panel title="Ranking de generadores" subtitle="Por cuenta y dependencia">
          <EfficiencyGenerationRanking rows={data.ranking || []} />
        </Panel>
      </section>
    </>
  );
}

function EfficiencyConsumptionCostChart({ rows }) {
  const clean = (rows || []).map((row) => ({
    mes: Number(row.mes),
    consumo: Number(row.consumo_kwh || 0),
    costo: Number(row.costo_unitario || 0),
  }));
  if (!clean.length) return <div className="qdf-empty-mini">Sin consumos validos para el periodo.</div>;
  const maxConsumption = Math.max(...clean.map((row) => row.consumo), 1);
  const maxCost = Math.max(...clean.map((row) => row.costo), 1);
  return (
    <div className="qdf-dual-metric-chart">
      <header><span><i className="consumption" />Consumo kWh</span><span><i className="cost" />Costo $/kWh</span></header>
      <div className="qdf-dual-bars">
        {clean.map((row) => (
          <div key={row.mes} title={`${MONTHS[row.mes - 1]} | ${numberCompact(row.consumo)} kWh | ${moneyCompact(row.costo)}/kWh`}>
            <section><i style={{ height: `${Math.max(3, row.consumo / maxConsumption * 100)}%` }} /><b style={{ height: `${Math.max(3, row.costo / maxCost * 100)}%` }} /></section>
            <strong>{MONTHS[row.mes - 1]}</strong>
          </div>
        ))}
      </div>
    </div>
  );
}

function EfficiencyCostComposition({ data }) {
  const rows = [
    ['Energia variable', data.energia_variable],
    ['Potencia contratada', data.potencia_contratada],
    ['Potencia adquirida', data.potencia_adquirida],
    ['Cargo fijo', data.cargo_fijo],
    ['Potencia excedida', data.potencia_excedida],
    ['TGFI', data.tgfi],
    ['Impuestos y otros', data.otros_impuestos],
  ].map(([label, value]) => ({ label, value: Number(value || 0) })).filter((row) => row.value > 0);
  const total = rows.reduce((sum, row) => sum + row.value, 0);
  if (!total) return <div className="qdf-empty-mini">Sin componentes de costo disponibles.</div>;
  return <div className="qdf-cost-composition">{rows.map((row, index) => <div key={row.label}><span><i style={{ background: COLORS[index % COLORS.length] }} />{row.label}</span><strong>{moneyCompact(row.value)}</strong><em>{pct(row.value / total * 100)}</em><div><i style={{ width: `${row.value / total * 100}%`, background: COLORS[index % COLORS.length] }} /></div></div>)}</div>;
}

function EfficiencyGenerationEvolution({ rows }) {
  const clean = rows || [];
  const max = Math.max(...clean.map((row) => Number(row.energia_inyectada || 0)), 1);
  if (!clean.length) return <div className="qdf-empty-mini">Sin energia inyectada registrada.</div>;
  return <div className="qdf-generation-evolution">{clean.map((row) => <div key={`${row.anio}-${row.mes}`} title={`${MONTHS[Number(row.mes) - 1]} ${row.anio}: ${numberCompact(row.energia_inyectada)} kWh`}><section><i style={{ height: `${Math.max(4, Number(row.energia_inyectada || 0) / max * 100)}%` }} /></section><strong>{MONTHS[Number(row.mes) - 1]} {String(row.anio).slice(-2)}</strong></div>)}</div>;
}

function EfficiencyGenerationRanking({ rows }) {
  const max = Math.max(...(rows || []).map((row) => Number(row.energia_inyectada || 0)), 1);
  if (!(rows || []).length) return <div className="qdf-empty-mini">Sin cuentas generadoras.</div>;
  return <div className="qdf-generation-ranking">{rows.map((row, index) => <div key={`${row.nro_cuenta}-${index}`} title={`${row.dependencia} | Cuenta ${row.nro_cuenta}`}><i>{index + 1}</i><span><strong>{limitText(row.dependencia, 34)}</strong><small>Cuenta {row.nro_cuenta}</small></span><b><i style={{ width: `${Number(row.energia_inyectada || 0) / max * 100}%` }} /></b><em>{numberCompact(row.energia_inyectada)} kWh</em></div>)}</div>;
}

function EfficiencyEvolutionChart({ rows }) {
  const clean = (rows || []).map((row) => ({
    mes: Number(row.mes),
    impacto: Number(row.impacto_identificado || 0),
    tgfi: Number(row.penalidad_tgfi || 0),
    exceso: Number(row.exceso_potencia || 0),
  }));
  if (!clean.length) return <div className="qdf-empty-mini">Sin datos tecnicos para el periodo seleccionado.</div>;

  const max = Math.max(...clean.map((row) => row.impacto), 1);
  return (
    <div className="qdf-efficiency-bars">
      {clean.map((row) => (
        <div className="qdf-efficiency-month" key={row.mes} title={`${MONTHS[row.mes - 1]}: ${moneyCompact(row.impacto)}`}>
          <strong>{MONTHS[row.mes - 1]}</strong>
          <div><i style={{ height: `${Math.max(4, row.impacto / max * 100)}%` }} /></div>
          <span>{moneyCompact(row.impacto)}</span>
          <small>TGFI {moneyCompact(row.tgfi)} - Potencia {moneyCompact(row.exceso)}</small>
        </div>
      ))}
    </div>
  );
}

function EfficiencyCauseBreakdown({ rows }) {
  const total = (rows || []).reduce((sum, row) => sum + Number(row.total || 0), 0);
  if (!total) return <div className="qdf-empty-mini">No se detectaron cargos tecnicos en el periodo.</div>;

  return (
    <div className="qdf-efficiency-causes">
      {(rows || []).map((row, index) => {
        const share = Number(row.total || 0) / total * 100;
        return (
          <div key={row.id}>
            <span><i style={{ background: COLORS[index % COLORS.length] }} />{row.nombre}</span>
            <strong>{moneyCompact(row.total)}</strong>
            <em>{pct(share)}</em>
            <div><i style={{ width: `${share}%`, background: COLORS[index % COLORS.length] }} /></div>
          </div>
        );
      })}
      <footer><span>Impacto total identificado</span><strong>{moneyCompact(total)}</strong></footer>
    </div>
  );
}

function EfficiencyRanking({ rows, nameKey }) {
  const clean = (rows || []).slice(0, 10);
  const max = Math.max(...clean.map((row) => Number(row.impacto_identificado || 0)), 1);
  if (!clean.length) return <div className="qdf-empty-mini">Sin dependencias observadas.</div>;

  return (
    <div className="qdf-efficiency-ranking">
      {clean.map((row, index) => (
        <div key={`${row[nameKey]}-${index}`} title={`${row[nameKey]}: ${moneyCompact(row.impacto_identificado)}`}>
          <i>{index + 1}</i>
          <strong>{limitText(formatDimensionName(row[nameKey], 'dependencia'), 30)}</strong>
          <span><i style={{ width: `${Math.max(3, Number(row.impacto_identificado || 0) / max * 100)}%` }} /></span>
          <em>{moneyCompact(row.impacto_identificado)}</em>
        </div>
      ))}
    </div>
  );
}

function EfficiencyMetersTable({ rows }) {
  if (!(rows || []).length) return <div className="qdf-empty-mini">Sin medidores con impacto identificado.</div>;
  return (
    <div className="qdf-compare-table-wrap">
      <table className="qdf-table qdf-efficiency-table">
        <thead><tr><th>Dependencia</th><th>Cuenta</th><th>Medidor</th><th>Tarifa</th><th>Impacto</th><th>Importe</th></tr></thead>
        <tbody>
          {rows.slice(0, 12).map((row, index) => (
            <tr key={`${row.nro_cuenta}-${row.nro_medidor}-${index}`} title={`${row.dependencia} | Cuenta ${row.nro_cuenta} | Medidor ${row.nro_medidor}`}>
              <td>{limitText(formatDimensionName(row.dependencia, 'dependencia'), 28)}</td>
              <td>{row.nro_cuenta}</td>
              <td>{row.nro_medidor}</td>
              <td>{row.tipo_de_tarifa}</td>
              <td><strong>{moneyCompact(row.impacto_identificado)}</strong></td>
              <td>{moneyCompact(row.importe)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function EfficiencyOperationalTable({ rows, periodLabel }) {
  const [priorityFilter, setPriorityFilter] = useState('');
  const [issueFilter, setIssueFilter] = useState('');
  const [search, setSearch] = useState('');
  if (!(rows || []).length) return <div className="qdf-empty-mini">No hay acciones tecnicas pendientes para los filtros seleccionados.</div>;
  const issueOptions = [...new Set(rows.map((row) => row.problema_principal).filter(Boolean))];
  const normalizedSearch = search.trim().toLocaleLowerCase('es');
  const filteredRows = rows.filter((row) => {
    if (priorityFilter && row.prioridad !== priorityFilter) return false;
    if (issueFilter && row.problema_principal !== issueFilter) return false;
    if (!normalizedSearch) return true;
    return [row.dependencia, row.nro_cuenta, row.nro_medidor, row.tipo_de_tarifa, row.problema_principal]
      .some((value) => String(value || '').toLocaleLowerCase('es').includes(normalizedSearch));
  });
  return (
    <>
      <div className="qdf-operational-toolbar">
        <div><strong>{filteredRows.length}</strong><span>acciones - {periodLabel}</span></div>
        <input type="search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Buscar dependencia, cuenta o medidor" />
        <select value={priorityFilter} onChange={(event) => setPriorityFilter(event.target.value)}>
          <option value="">Todas las prioridades</option><option value="Alta">Alta</option><option value="Media">Media</option><option value="Baja">Baja</option><option value="Revisar">Revisar</option>
        </select>
        <select value={issueFilter} onChange={(event) => setIssueFilter(event.target.value)}>
          <option value="">Todos los problemas</option>{issueOptions.map((issue) => <option key={issue} value={issue}>{issue}</option>)}
        </select>
        {(search || priorityFilter || issueFilter) && <button type="button" onClick={() => { setSearch(''); setPriorityFilter(''); setIssueFilter(''); }}>Limpiar</button>}
      </div>
      <div className="qdf-compare-table-wrap">
      <table className="qdf-table qdf-efficiency-operational-table">
        <thead>
          <tr><th>Prioridad</th><th>Dependencia</th><th>Cuenta / Medidor</th><th>Tarifa</th><th>Problema y accion</th><th>Utilizacion</th><th>Impacto</th><th>Ahorro potencial</th></tr>
        </thead>
        <tbody>
          {filteredRows.map((row, index) => {
            const impact = Number(row.impacto_identificado || 0);
            const priority = row.prioridad || 'Revisar';
            const utilization = Number(row.p_contratada || 0) > 0 && Number(row.p_registrada || 0) > 0 ? Number(row.p_registrada || 0) / Number(row.p_contratada) * 100 : null;
            return (
              <tr key={`${row.nro_cuenta}-${row.nro_medidor}-${index}`} title={`${row.dependencia} | ${row.problema_principal} | Impacto ${moneyCompact(impact)}`}>
                <td><span className={`qdf-priority ${priority.toLowerCase()}`}>{priority}</span></td>
                <td>{formatDimensionName(row.dependencia, 'dependencia')}</td>
                <td><strong>{row.nro_cuenta}</strong><small>Medidor {row.nro_medidor}</small></td>
                <td>{row.tipo_de_tarifa}</td>
                <td><strong>{row.problema_principal}</strong><small>{row.accion_sugerida}</small></td>
                <td>{utilization === null ? '-' : pct(utilization)}<small>{numberCompact(row.p_registrada)} / {numberCompact(row.p_contratada)} kW</small></td>
                <td><strong>{moneyCompact(impact)}</strong></td>
                <td><strong>{moneyCompact(row.ahorro_potencial)}</strong><small>{row.meses_analizados} meses</small></td>
              </tr>
            );
          })}
        </tbody>
      </table>
      {!filteredRows.length && <div className="qdf-empty-mini">No hay acciones que coincidan con estos filtros.</div>}
      </div>
    </>
  );
}

function ComingSoon({ activeTab }) {
  const label = TABS.find((tab) => tab.id === activeTab)?.label || activeTab;
  return (
    <StateBox
      title={label}
      text="Esta pantalla queda reservada para la siguiente etapa. El shell visual y la navegacion ya estan listos."
    />
  );
}

createRoot(document.getElementById('dashboard-financiero-root')).render(<App />);




