import './bootstrap';

document.addEventListener('livewire:init', () => {
    Livewire.on('sessionRegenerated', (csrfToken) => {
        const meta = document.head.querySelector('meta[name="csrf-token"]');

        if (!meta) {
            return;
        }

        meta.setAttribute('content', csrfToken);
    });
});
