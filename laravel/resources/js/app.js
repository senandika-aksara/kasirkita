import './bootstrap';

import Alpine from 'alpinejs';
import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
    Legend,
    Tooltip,
);

Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#5B6978';

window.Chart = Chart;
window.Alpine = Alpine;

/** Rp1.234.567 — sama dengan App\Support\Money::format di sisi server. */
window.rupiah = (value) => 'Rp' + Number(value || 0).toLocaleString('id-ID');

Alpine.start();
