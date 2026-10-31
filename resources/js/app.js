import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/cormorant-garamond/500.css';
import '@fontsource/cormorant-garamond/600.css';
import {
    BarController, BarElement, CategoryScale, Chart, DoughnutController, ArcElement,
    Legend, LinearScale, Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, DoughnutController, ArcElement, Tooltip, Legend);

const PALETTE = ['#6e1f2f', '#b89b86', '#a3485a', '#c9a96e', '#8c7a6b', '#e7c0c7'];
const fcfa = (v) => `${new Intl.NumberFormat('fr-FR').format(v)} FCFA`;

/** Graphiques déclarés dans le HTML : <canvas data-chart='{"type":"bar","labels":[],"values":[]}'> */
function initCharts() {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const { type, labels, values, money } = JSON.parse(canvas.dataset.chart);

        new Chart(canvas, {
            type,
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: type === 'bar' ? '#6e1f2f' : PALETTE,
                    borderRadius: type === 'bar' ? 8 : 0,
                    borderWidth: 0,
                    maxBarThickness: 42,
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: type !== 'bar', position: 'bottom', labels: { boxWidth: 10, color: '#7a6a63' } },
                    tooltip: { callbacks: { label: (ctx) => (money ? fcfa(ctx.parsed.y ?? ctx.parsed) : ` ${ctx.parsed}`) } },
                },
                scales: type === 'bar' ? {
                    x: { grid: { display: false }, ticks: { color: '#7a6a63' } },
                    y: { grid: { color: '#f1e4d6' }, ticks: { color: '#7a6a63', callback: (v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) } },
                } : {},
            },
        });
    });
}

/** Navigation mobile + confirmations simples, sans framework JS. */
function initUi() {
    document.querySelectorAll('[data-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => document.getElementById(btn.dataset.toggle)?.classList.toggle('hidden'));
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    // Filtre de liste (ex. recherche de client dans un <select>).
    document.querySelectorAll('[data-filter-target]').forEach((input) => {
        const select = document.getElementById(input.dataset.filterTarget);
        input.addEventListener('input', () => {
            const terme = input.value.trim().toLowerCase();
            [...select.options].forEach((opt) => {
                opt.hidden = opt.value !== '' && !opt.textContent.toLowerCase().includes(terme);
            });
        });
    });

    // Récapitulatif durée / prix dans le formulaire de rendez-vous.
    const recap = document.getElementById('recap-rdv');
    if (recap) {
        const maj = () => {
            let duree = 0; let prix = 0;
            document.querySelectorAll('input[data-duree]:checked').forEach((c) => {
                duree += Number(c.dataset.duree); prix += Number(c.dataset.prix);
            });
            recap.querySelector('[data-recap-duree]').textContent = `${Math.floor(duree / 60)}h${String(duree % 60).padStart(2, '0')}`;
            recap.querySelector('[data-recap-prix]').textContent = fcfa(prix);
        };
        document.querySelectorAll('input[data-duree]').forEach((c) => c.addEventListener('change', maj));
        maj();
    }
}

document.addEventListener('DOMContentLoaded', () => { initUi(); initCharts(); });
