document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Laravel Foundation Loaded Successfully!');

    // Handle code snippet copy buttons
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const targetId = button.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                navigator.clipboard.writeText(targetEl.innerText);
                const originalHTML = button.innerHTML;
                button.innerHTML = '✓ Copied';
                button.style.color = '#4ade80';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.color = '';
                }, 2000);
            }
        });
    });
});
