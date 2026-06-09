function initCustomSelects() {
    // Initialize all custom selects
    const customSelects = document.querySelectorAll('.custom-select-container');

    customSelects.forEach(container => {
        // Prevent double initialization
        if (container.dataset.initialized) return;
        container.dataset.initialized = 'true';

        const trigger = container.querySelector('.custom-select-trigger');
        const dropdown = container.querySelector('.custom-select-dropdown');
        const hiddenSelect = container.querySelector('select');
        const labelEl = container.querySelector('.custom-select-label');
        const icon = container.querySelector('.custom-select-icon');
        const isMultiple = container.dataset.multiple === 'true';
        const defaultPlaceholder = labelEl.textContent; // Store initial placeholder

        // Toggle dropdown on click
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.contains('opacity-100');
            
            // Close all others first
            document.querySelectorAll('.custom-select-dropdown').forEach(d => {
                d.classList.add('opacity-0', 'invisible', '-translate-y-2', 'pointer-events-none');
                d.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
            });
            document.querySelectorAll('.custom-select-icon').forEach(i => i.style.transform = 'rotate(0deg)');

            if (!isOpen) {
                dropdown.classList.remove('opacity-0', 'invisible', '-translate-y-2', 'pointer-events-none');
                dropdown.classList.add('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
                icon.style.transform = 'rotate(180deg)';
            }
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                dropdown.classList.add('opacity-0', 'invisible', '-translate-y-2', 'pointer-events-none');
                dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
                icon.style.transform = 'rotate(0deg)';
            }
        });

        // Prevent closing when clicking inside dropdown
        dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Handle option selection
        if (isMultiple) {
            const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    updateMultipleSelection();
                });
            });

            // If options are clicked (label), it triggers the checkbox change automatically
            function updateMultipleSelection() {
                const selected = Array.from(checkboxes).filter(cb => cb.checked);
                
                // Update label
                if (selected.length === 0) {
                    labelEl.textContent = defaultPlaceholder;
                } else if (selected.length === 1) {
                    labelEl.textContent = selected[0].dataset.label;
                } else if (selected.length <= 2) {
                     labelEl.textContent = selected.map(cb => cb.dataset.label).join(', ');
                } else {
                    labelEl.textContent = `${selected.length} Dipilih`;
                }

                // Update hidden select
                Array.from(hiddenSelect.options).forEach(opt => {
                    opt.selected = selected.some(cb => cb.value === opt.value);
                });

                // Trigger change event on hidden select for existing JS logic
                hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
            
            // Sync initial state (if any)
            updateMultipleSelection();

        } else {
            const radios = dropdown.querySelectorAll('input[type="radio"]');
            
            radios.forEach(radio => {
                radio.addEventListener('change', () => {
                    // Update label
                    labelEl.textContent = radio.dataset.label;
                    
                    // Update styling for active state
                    container.querySelectorAll('.custom-select-option span').forEach(span => {
                        span.classList.remove('font-bold', 'text-[#444]');
                        span.classList.add('text-gray-600');
                    });
                    const activeSpan = radio.closest('.custom-select-option').querySelector('span');
                    if (activeSpan) {
                        activeSpan.classList.remove('text-gray-600');
                        activeSpan.classList.add('font-bold', 'text-[#444]');
                    }

                    // Close dropdown
                    dropdown.classList.add('opacity-0', 'invisible', '-translate-y-2', 'pointer-events-none');
                    dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
                    icon.style.transform = 'rotate(0deg)';

                    // Update hidden select
                    hiddenSelect.value = radio.value;

                    // Trigger change event
                    hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
            
            // Initial sync if a value is pre-selected (not empty)
            if(hiddenSelect.value) {
                const selectedRadio = Array.from(radios).find(r => r.value === hiddenSelect.value);
                if(selectedRadio) {
                    selectedRadio.checked = true;
                    // Trigger label update but prevent firing another change event that causes loops
                    labelEl.textContent = selectedRadio.dataset.label;
                    container.querySelectorAll('.custom-select-option span').forEach(span => {
                        span.classList.remove('font-bold', 'text-[#444]');
                        span.classList.add('text-gray-600');
                    });
                    const activeSpan = selectedRadio.closest('.custom-select-option').querySelector('span');
                    if (activeSpan) {
                        activeSpan.classList.remove('text-gray-600');
                        activeSpan.classList.add('font-bold', 'text-[#444]');
                    }
                }
            }
        }
        
        // Listen to programmatic changes on the hidden select to update the UI
        hiddenSelect.addEventListener('change', (e) => {
             // If event is not trusted (meaning programmatic, not from user interaction), sync UI
             if(!e.isTrusted) {
                 if(isMultiple) {
                     const selectedValues = Array.from(hiddenSelect.selectedOptions).map(o => o.value);
                     const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');
                     checkboxes.forEach(cb => {
                         cb.checked = selectedValues.includes(cb.value);
                     });
                     
                     // Update label manually since we don't want an infinite loop
                     const selected = Array.from(checkboxes).filter(cb => cb.checked);
                     if (selected.length === 0) {
                        labelEl.textContent = defaultPlaceholder;
                     } else if (selected.length === 1) {
                        labelEl.textContent = selected[0].dataset.label;
                     } else if (selected.length <= 2) {
                        labelEl.textContent = selected.map(cb => cb.dataset.label).join(', ');
                     } else {
                        labelEl.textContent = `${selected.length} Dipilih`;
                     }
                 } else {
                     const radios = dropdown.querySelectorAll('input[type="radio"]');
                     const selectedRadio = Array.from(radios).find(r => r.value === hiddenSelect.value);
                     if(selectedRadio) {
                         selectedRadio.checked = true;
                         labelEl.textContent = selectedRadio.dataset.label;
                         container.querySelectorAll('.custom-select-option span').forEach(span => {
                             span.classList.remove('font-bold', 'text-[#444]');
                             span.classList.add('text-gray-600');
                         });
                         const activeSpan = selectedRadio.closest('.custom-select-option').querySelector('span');
                         if (activeSpan) {
                             activeSpan.classList.remove('text-gray-600');
                             activeSpan.classList.add('font-bold', 'text-[#444]');
                         }
                     }
                 }
             }
        });
    });
}

// Run immediately if DOM is already ready, or wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomSelects);
} else {
    initCustomSelects();
}

// Make it available globally if we need to re-initialize dynamic selects
window.initCustomSelects = initCustomSelects;
