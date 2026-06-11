import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './styles.css';

const MONTHS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
const MONTH_OPTIONS = [
  { value: '', label: 'Año completo' },
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

const TABS = [
  { id: 'finanzas', label: 'Finanzas' },
  { id: 'comparativo', label: 'Análisis comparativo' },
  { id: 'consumos', label: 'Consumos' },
  { id: 'eficiencia', label: 'Eficiencia energética' },
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
    .replace(/^SECRETAR[IÍ]A\s+(DE\s+|DEL\s+|DE LA\s+|)/i, '')
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
  const initialYears = (root?.dataset.years || initialYear).split(',').filter(Boolean);

  const [activeTab, setActiveTab] = useState('finanzas');
  const [filters, setFilters] = useState({
    anio: initialYear,
    mes: '',
    secretaria: '',
    proveedor: '',
    dependencia: '',
    unidad_medida: '',
  });

  const [options, setOptions] = useState({
    anios: initialYears.map((year) => ({ value: year, label: year })),
    secretarias: [],
    proveedores: [],
    dependencias: [],
    unidades_medida: [],
  });
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
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
      const periodoExtra = filters.mes ? {} : { mes: corteMes || null, mes_hasta: null };

      const [resumenPeriodo, evolucion, secretarias, secretariasContexto, proveedores, dependencias, objetos, crecimiento, pareto, forecast, servicios] = await Promise.all([
        api.get('resumen', periodoExtra),
        api.get('evolucion', ytdExtra),
        api.get('ranking', { ...ytdExtra, dimension: 'secretaria', limite: 30 }),
        api.get('ranking', { ...ytdExtra, dimension: 'secretaria', limite: 30, secretaria: null }),
        api.get('ranking', { ...ytdExtra, dimension: 'proveedor', limite: 30 }),
        api.get('ranking', { ...ytdExtra, dimension: 'dependencia', limite: 30 }),
        api.get('ranking', { ...ytdExtra, dimension: 'objeto', limite: 30 }),
        api.get('crecimiento', { ...ytdExtra, dimension: 'secretaria', limite: 8 }),
        api.get('pareto', { ...ytdExtra, dimension: 'dependencia', limite: 5 }),
        api.get('forecast', ytdExtra),
        api.get('servicios', ytdExtra),
      ]);

        if (cancelled) return;
        setOptions({
          anios: filtros.filtros?.anios?.length ? filtros.filtros.anios : options.anios,
          secretarias: filtros.filtros?.secretarias || [],
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
  }, [filters.anio, filters.mes, filters.secretaria, filters.proveedor, filters.dependencia, filters.unidad_medida]);

  const onFilter = (key, value) => {
    setFilters((current) => ({ ...current, [key]: value }));
  };

  const clearFilters = () => {
    setFilters((current) => ({
      ...current,
      mes: '',
      secretaria: '',
      proveedor: '',
      dependencia: '',
      unidad_medida: '',
    }));
  };

  return (
    <div className="qdf-shell">
      <Header
        activeTab={activeTab}
        setActiveTab={setActiveTab}
        filters={filters}
        options={options}
        onFilter={onFilter}
        assetsUrl={assetsUrl}
      />
      <main className="qdf-main">
        {activeTab === 'finanzas' ? (
          <>
            <Finanzas
              data={data}
              loading={loading}
              error={error}
              filters={filters}
              options={options}
              onFilter={onFilter}
              clearFilters={clearFilters}
            />
            {loading && <LoadingOverlay />}
          </>
        ) : (
          <ComingSoon activeTab={activeTab} />
        )}
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

function Header({ activeTab, setActiveTab, filters, options, onFilter, assetsUrl }) {
  return (
    <header className="qdf-header">
      <div className="qdf-brand">
        <img src="/assets/dashboard-financiero/quickdata-doc-intelligence.png" alt="QuickData Document Intelligence" className="qdf-logo-quickdata" />
        <span className="qdf-brand-line" />
        <img src={`${assetsUrl}/Logo-mvl2.png`} alt="Vivamos Vicente López" className="qdf-logo-mvl-text" />
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

      <div className="qdf-period">
        <span className="qdf-period-icon">▣</span>
        <label>
          <span>Período</span>
          <select value={filters.mes} onChange={(event) => onFilter('mes', event.target.value)}>
            {MONTH_OPTIONS.map((item) => (
              <option value={item.value} key={item.value}>
                {item.value ? item.label : 'YTD'}
              </option>
            ))}
          </select>
        </label>
        <label>
          <span>Año</span>
          <select value={filters.anio} onChange={(event) => onFilter('anio', event.target.value)}>
            {options.anios.map((item) => (
              <option value={item.value} key={item.value}>{item.label}</option>
            ))}
          </select>
        </label>
      </div>
    </header>
  );
}

function Finanzas({ data, loading, error, filters, options, onFilter, clearFilters }) {
  if (error) return <StateBox title="No se pudo cargar el dashboard" text={error} />;
  const actual = data?.resumenYtd?.actual || {};
  const periodo = data?.resumenPeriodo?.actual || {};
  const compYtd = data?.resumenYtd?.comparativas || {};
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
  const periodMonthLabel = filters.mes ? monthName(filters.mes) : monthName(corteMes);
  const periodLabel = `${periodMonthLabel} ${filters.anio}`;
  const periodCompareLabel = periodMonthLabel ? `vs. ${periodMonthLabel} ${Number(filters.anio) - 1}` : `vs. ${Number(filters.anio) - 1}`;
  const periodDelta = monthYoYVariation(data?.evolucion?.actual || [], data?.evolucion?.anterior || [], filters.mes || corteMes);
  const ytdSubtitle = `vs. ${ytdRangeLabel(corteMes, Number(filters.anio) - 1)}`;
  const promedioMensualYtd = corteMes ? Number(actual.total || 0) / Number(corteMes) : 0;
  const bottomPanels = financeBottomPanels({
    filters,
    anio: filters.anio,
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
          <span>Visión general del gasto municipal</span>
        </div>
        <FilterStrip filters={filters} options={options} onFilter={onFilter} clearFilters={clearFilters} />
      </div>

      <section className="qdf-kpis">
        <Kpi icon="$" title="Gasto total acumulado (YTD)" value={moneyCompact(actual.total)} delta={variationPercent(compYtd.anio_anterior)} subtitle={ytdSubtitle} color="blue" />
        <Kpi icon="▣" title={filters.mes ? 'Gasto mes seleccionado' : 'Gasto ultimo mes cerrado'} value={moneyCompact(periodo.total)} delta={periodDelta} subtitle={periodCompareLabel} color="blue" />
        <Kpi icon="↗" title="Variación interanual (YTD)" value={pct(variationPercent(compYtd.anio_anterior))} subtitle={`${ytdRangeLabel(corteMes, filters.anio)} vs. ${ytdRangeLabel(corteMes, Number(filters.anio) - 1)}`} color="green" />
        <Kpi icon="▥" title="Promedio mensual YTD" value={moneyCompact(promedioMensualYtd)} subtitle={ytdRangeLabel(corteMes, filters.anio)} color="purple" />
        <Kpi icon="▥" title="Top secretaría (YTD)" value={topSecretaria?.nombre || '-'} subtitle={`${moneyCompact(topSecretaria?.total)} · ${pct(topSecretariaPct)}`} color="blue" />
        <Kpi icon="◉" title="Top proveedor (YTD)" value={topProveedor?.nombre || '-'} subtitle={`${moneyCompact(topProveedor?.total)} · ${pct(topProveedorPct)}`} color="purple" />
      </section>

      <section className="qdf-grid qdf-grid-middle">
        <Panel title="Evolución mensual del gasto" subtitle="Millones de pesos">
          <LineChart actual={data?.evolucion?.actual || []} previous={data?.evolucion?.anterior || []} year={Number(filters.anio)} />
        </Panel>
        <Panel title={`Distribución del gasto ${filters.anio} YTD`} subtitle="Por Secretaría">
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
        <span>ⓘ</span> Los datos se actualizan desde facturas consolidadas de Proveedores y Electromecánica.
      </footer>
    </section>
  );
}

function FilterStrip({ filters, options, onFilter, clearFilters }) {
  return (
    <div className="qdf-filter-strip">
      <SelectMini label="Secretaría" value={filters.secretaria} options={formatOptions(options.secretarias, 'secretaria')} onChange={(v) => onFilter('secretaria', v)} />
      <SelectMini label="Proveedor" value={filters.proveedor} options={options.proveedores} onChange={(v) => onFilter('proveedor', v)} />
      <SelectMini label="Dependencia" value={filters.dependencia} options={formatOptions(options.dependencias, 'dependencia')} onChange={(v) => onFilter('dependencia', v)} />
      <button type="button" onClick={clearFilters}>Limpiar</button>
    </div>
  );
}

function SelectMini({ label, value, options, onChange }) {
  return (
    <label className="qdf-select-mini">
      <span>{label}</span>
      <select value={value} onChange={(event) => onChange(event.target.value)}>
        {(options || []).map((item) => (
          <option value={item.value ?? ''} key={`${label}-${item.value ?? ''}`}>{item.label}</option>
        ))}
      </select>
    </label>
  );
}

function financeBottomPanels({ filters, anio, total, secretarias, dependencias, proveedores, objetos, evolucion }) {
  const hasSecretaria = Boolean(filters.secretaria);
  const hasProveedor = Boolean(filters.proveedor);
  const hasDependencia = Boolean(filters.dependencia);
  const subtitle = `YTD ${anio}`;

  if (hasDependencia) {
    return [
      { key: 'proveedores-dependencia', title: 'Proveedores de la dependencia', subtitle, rows: proveedores, totalGeneral: total, numbered: false },
      { key: 'objetos-dependencia', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
      { key: 'evolucion-dependencia', title: 'Gasto mensual', subtitle: `Año ${anio}`, rows: evolucion, totalGeneral: total, type: 'monthly' },
    ];
  }

  if (hasProveedor && hasSecretaria) {
    return [
      { key: 'dependencias-cruce', title: 'Dependencias del cruce', subtitle, rows: dependencias, totalGeneral: total, numbered: true },
      { key: 'objetos-cruce', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
      { key: 'evolucion-cruce', title: 'Gasto mensual', subtitle: `Año ${anio}`, rows: evolucion, totalGeneral: total, type: 'monthly' },
    ];
  }

  if (hasProveedor) {
    return [
      { key: 'secretarias-proveedor', title: 'Secretarías impactadas', subtitle, rows: secretarias, totalGeneral: total, numbered: false },
      { key: 'dependencias-proveedor', title: 'Dependencias impactadas', subtitle, rows: dependencias, totalGeneral: total, numbered: true },
      { key: 'objetos-proveedor', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
    ];
  }

  if (hasSecretaria) {
    return [
      { key: 'dependencias-secretaria', title: 'Top dependencias de la secretaría', subtitle, rows: dependencias, totalGeneral: total, numbered: true },
      { key: 'objetos-secretaria', title: 'Objeto del gasto', subtitle, rows: objetos, totalGeneral: total, numbered: false },
      { key: 'proveedores-secretaria', title: 'Proveedores de la secretaría', subtitle, rows: proveedores, totalGeneral: total, numbered: false },
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
          {delta !== undefined && delta !== null && <span className={`qdf-delta ${deltaClass}`}>{numericDelta >= 0 ? '↑' : '↓'} {pct(Math.abs(numericDelta))}</span>}
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
      {action && <button className="qdf-link-button" type="button">{action} →</button>}
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
        <span className="qdf-soft-icon">▣</span>
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
      <p className="qdf-chart-note">ⓘ Valores expresados en millones de pesos.</p>
    </div>
  );
}

function secretariaIcon(name) {
  const text = String(name || '').toLowerCase();
  if (text.includes('salud')) return '♥';
  if (text.includes('seguridad')) return '✓';
  if (text.includes('educ')) return '▰';
  if (text.includes('obra') || text.includes('plane')) return '▥';
  if (text.includes('desarrollo')) return '●';
  if (text.includes('ambiente')) return '◒';
  return '•••';
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
      <p className="qdf-chart-note">ⓘ Valores expresados en millones de pesos.</p>
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
    return <div className="qdf-empty-mini">Sin composición de servicios para los filtros seleccionados.</div>;
  }

  return (
    <div className="qdf-service-drivers">
      {!selected ? (
        <>
          <p className="qdf-widget-copy">Participación de los dos servicios principales y el resto por secretaría.</p>
          <div className="qdf-service-bars">
            {rows.slice(0, 7).map((row) => (
              <button type="button" key={row.secretaria} onClick={() => onSelectSecretaria(row.secretaria)} title="Ver composición interna">
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
      <button type="button" onClick={onBack} className="qdf-back-mini">← Todas las secretarías</button>
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
  return text.length > max ? `${text.slice(0, max - 1)}…` : text;
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
          <th>Variación</th>
          <th>Tendencia</th>
        </tr>
      </thead>
      <tbody>
        {clean.map((row, index) => (
          <tr key={`${firstColumn}-${row.nombre}-${index}`}>
            <td title={row.nombre || '-'}>{numbered && <small>{index + 1}</small>} {limitText(row.nombre || '-', 32)}</td>
            <td>{moneyCompact(row.total)}</td>
            <td>{pct((Number(row.total || 0) / total) * 100)}</td>
            <td className="positive">↑ {pct(row.variacion_porcentual || 0)}</td>
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
    { icon: '↗', color: 'blue', text: `El gasto total acumulado ${Number(yoy || 0) >= 0 ? 'creció' : 'bajó'} ${pct(Math.abs(Number(yoy || 0)))} vs. el mismo periodo del año anterior.` },
    { icon: '◷', color: 'green', text: `${topSecretaria?.nombre || 'La principal secretaría'} concentra ${pct(topSecretariaPct)} del gasto municipal.` },
    { icon: '◉', color: 'purple', text: `${topProveedor?.nombre || 'El principal proveedor'} representa ${pct(topProveedorPct)} del gasto acumulado.` },
    { icon: '▥', color: 'orange', text: `5 dependencias concentran el ${pct(concentrationPct)} del gasto total filtrado.` },
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

function ComingSoon({ activeTab }) {
  const label = TABS.find((tab) => tab.id === activeTab)?.label || activeTab;
  return (
    <StateBox
      title={label}
      text="Esta pantalla queda reservada para la siguiente etapa. El shell visual y la navegación ya están listos."
    />
  );
}

createRoot(document.getElementById('dashboard-financiero-root')).render(<App />);
