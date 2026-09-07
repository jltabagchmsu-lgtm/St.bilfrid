/**
 * St. Bilfrid Development Corporation - Construction Management System
 * Interactive UI Engine & Command Palette
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Global Shortcuts (Ctrl + K / Cmd + K)
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            toggleCommandPalette();
        } else if (e.key === 'Escape') {
            closeCommandPalette();
            const projectDropdown = document.getElementById('projectSwitcherDropdown');
            if (projectDropdown) {
                projectDropdown.classList.remove('active');
            }
        }
    });

    // 2. Initialize Before/After Photo Split Sliders
    initSplitSliders();

    // 3. Handle code snippet & ID copy buttons
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                navigator.clipboard.writeText(targetEl.innerText || targetEl.value);
                const originalHTML = button.innerHTML;
                button.innerHTML = 'Copied!';
                button.style.color = '#4ade80';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.color = '';
                }, 2000);
            }
        });
    });
});

/* ==========================================================================
   COMMAND PALETTE (Ctrl + K) CONTROLLER
   ========================================================================== */

function openCommandPalette() {
    const modal = document.getElementById('commandPaletteModal');
    const input = document.getElementById('cmdSearchInput');
    if (modal && input) {
        modal.classList.add('active');
        input.value = '';
        filterCommandPalette('');
        setTimeout(() => input.focus(), 50);
    }
}

function closeCommandPalette() {
    const modal = document.getElementById('commandPaletteModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function toggleCommandPalette() {
    const modal = document.getElementById('commandPaletteModal');
    if (modal && modal.classList.contains('active')) {
        closeCommandPalette();
    } else {
        openCommandPalette();
    }
}

function handleCmdBackdropClick(e) {
    if (e.target.id === 'commandPaletteModal') {
        closeCommandPalette();
    }
}

function filterCommandPalette(query) {
    const q = (query || '').toLowerCase().trim();
    const groups = document.querySelectorAll('.cmd-category-group');

    groups.forEach(group => {
        let hasVisibleChild = false;
        const items = group.querySelectorAll('.cmd-item');
        
        items.forEach(item => {
            const text = (item.getAttribute('data-text') || '') + ' ' + (item.innerText || '');
            if (!q || text.toLowerCase().includes(q)) {
                item.style.display = 'flex';
                hasVisibleChild = true;
            } else {
                item.style.display = 'none';
            }
        });

        group.style.display = hasVisibleChild ? 'block' : 'none';
    });

    // Reset selection highlight
    const visibleItems = document.querySelectorAll('.cmd-item[style*="display: flex"], .cmd-item:not([style*="display: none"])');
    document.querySelectorAll('.cmd-item.selected').forEach(el => el.classList.remove('selected'));
    if (visibleItems.length > 0 && q) {
        visibleItems[0].classList.add('selected');
    }
}

function handleCmdKeyNav(e) {
    const visibleItems = Array.from(document.querySelectorAll('.cmd-palette-body .cmd-item')).filter(
        item => window.getComputedStyle(item).display !== 'none'
    );

    if (!visibleItems.length) return;

    let currentIndex = visibleItems.findIndex(item => item.classList.contains('selected'));

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (currentIndex === -1 || currentIndex >= visibleItems.length - 1) {
            currentIndex = 0;
        } else {
            currentIndex++;
        }
        updateCmdSelection(visibleItems, currentIndex);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (currentIndex <= 0) {
            currentIndex = visibleItems.length - 1;
        } else {
            currentIndex--;
        }
        updateCmdSelection(visibleItems, currentIndex);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (currentIndex !== -1 && visibleItems[currentIndex]) {
            visibleItems[currentIndex].click();
        } else if (visibleItems.length > 0) {
            visibleItems[0].click();
        }
    }
}

function updateCmdSelection(items, selectedIndex) {
    items.forEach((item, idx) => {
        if (idx === selectedIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
            item.classList.remove('selected');
        }
    });
}

/* ==========================================================================
   INTERACTIVE BEFORE / AFTER PHOTO SPLIT SLIDER
   ========================================================================== */

function initSplitSliders() {
    const containers = document.querySelectorAll('.split-slider-container');

    containers.forEach(container => {
        const afterImg = container.querySelector('.split-slider-after');
        const handle = container.querySelector('.split-slider-handle');
        if (!afterImg || !handle) return;

        let isDragging = false;

        const updateSliderPosition = (clientX) => {
            const rect = container.getBoundingClientRect();
            let offsetX = clientX - rect.left;
            if (offsetX < 0) offsetX = 0;
            if (offsetX > rect.width) offsetX = rect.width;

            const percentage = (offsetX / rect.width) * 100;
            handle.style.left = `${percentage}%`;
            afterImg.style.clipPath = `polygon(0 0, ${percentage}% 0, ${percentage}% 100%, 0 100%)`;
        };

        handle.addEventListener('mousedown', (e) => {
            isDragging = true;
            e.preventDefault();
        });

        window.addEventListener('mouseup', () => {
            isDragging = false;
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            updateSliderPosition(e.clientX);
        });

        // Touch support for tablets and mobile
        handle.addEventListener('touchstart', () => {
            isDragging = true;
        }, { passive: true });

        window.addEventListener('touchend', () => {
            isDragging = false;
        });

        window.addEventListener('touchmove', (e) => {
            if (!isDragging || !e.touches[0]) return;
            updateSliderPosition(e.touches[0].clientX);
        }, { passive: true });

        // Click on container to jump handle
        container.addEventListener('click', (e) => {
            if (e.target.closest('.split-slider-label')) return;
            updateSliderPosition(e.clientX);
        });
    });
}

/* ==========================================================================
   INTERACTIVE PROJECT STATUS FILTER TABS
   ========================================================================== */

function filterProjectsByStatus(status, triggerBtn) {
    const buttons = document.querySelectorAll('.filter-pill-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    if (triggerBtn) {
        triggerBtn.classList.add('active');
    }

    const cards = document.querySelectorAll('.project-interactive-card, .dashboard-project-row');
    let visibleCount = 0;

    cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status') || '';
        const healthStatus = card.getAttribute('data-health') || '';

        if (status === 'all') {
            card.style.display = '';
            visibleCount++;
        } else if (status === 'risk') {
            if (healthStatus === 'risk' || healthStatus === 'caution') {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        } else if (cardStatus === status) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const emptyMsg = document.getElementById('filterEmptyState');
    if (emptyMsg) {
        emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}
