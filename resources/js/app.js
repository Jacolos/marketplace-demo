import './bootstrap';
import Alpine from 'alpinejs';
import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Inicjalizacja Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Inicjalizacja Livewire
Livewire.start();

// Inicjalizacja Chart.js jeśli jest na stronie
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Inter', sans-serif";
}

// Pomocnicze funkcje
window.formatPrice = function(amount, currency = 'PLN') {
    return new Intl.NumberFormat('pl-PL', {
        style: 'currency',
        currency: currency
    }).format(amount);
};

window.confirmDelete = function(message = 'Czy na pewno chcesz usunąć ten element?') {
    return confirm(message);
};

// Notyfikacje
window.notify = function(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 p-4 rounded-lg shadow-lg z-50 fade-in ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    } text-white`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${
                type === 'success' ? 'check-circle' : 
                type === 'error' ? 'times-circle' : 
                type === 'warning' ? 'exclamation-triangle' : 'info-circle'
            } mr-2"></i>
            ${message}
        </div>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};