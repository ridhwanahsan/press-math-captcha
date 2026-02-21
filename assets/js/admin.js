document.addEventListener('DOMContentLoaded', () => {
    const rateLimitCheckbox = document.querySelector('input[name="pmc_settings[enable_rate_limit]"]');
    const maxAttemptsRow = document.querySelector('input[name="pmc_settings[max_attempts]"]')?.closest('tr');
    const blockDurationRow = document.querySelector('input[name="pmc_settings[block_duration]"]')?.closest('tr');

    const toggleRateLimitFields = () => {
        const enabled = rateLimitCheckbox && rateLimitCheckbox.checked;
        if (maxAttemptsRow) {
            maxAttemptsRow.style.display = enabled ? '' : 'none';
        }
        if (blockDurationRow) {
            blockDurationRow.style.display = enabled ? '' : 'none';
        }
    };

    if (rateLimitCheckbox) {
        rateLimitCheckbox.addEventListener('change', toggleRateLimitFields);
        toggleRateLimitFields();
    }
});
