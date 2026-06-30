<script>
    document.addEventListener('DOMContentLoaded', () => {
        const forms = Array.from(document.querySelectorAll('form')).filter((form) => {
            const method = (form.getAttribute('method') || 'GET').toUpperCase();

            return method !== 'GET' && !form.hasAttribute('data-swal-skip');
        });

        const humanizeAction = (text) => (text || '')
            .replace(/\s+/g, ' ')
            .trim();

        const detectAction = (form, submitter) => {
            const hintedAction = form.getAttribute('data-confirm-action');

            if (hintedAction) {
                return humanizeAction(hintedAction);
            }

            const buttonText = humanizeAction(
                submitter?.getAttribute('data-confirm-action')
                || submitter?.textContent
                || submitter?.value
                || ''
            );

            if (buttonText) {
                return buttonText;
            }

            const spoofedMethod = form.querySelector('input[name="_method"]')?.value?.toUpperCase();
            const method = spoofedMethod || (form.getAttribute('method') || 'POST').toUpperCase();

            return ({
                POST: 'Submit this form',
                PATCH: 'Update this item',
                PUT: 'Save these changes',
                DELETE: 'Delete this item',
            })[method] || 'Continue';
        };

        const firstPrompt = (action) => ({
            title: 'Please confirm',
            text: `Do you want to continue with "${action}"?`,
            icon: 'question',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#15803d',
            showCancelButton: true,
            reverseButtons: true,
        });

        forms.forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (form.dataset.swalConfirmed === 'true') {
                    return;
                }

                event.preventDefault();

                const submitter = event.submitter || document.activeElement;
                const action = detectAction(form, submitter);
                const firstResult = await Swal.fire(firstPrompt(action));

                if (!firstResult.isConfirmed) {
                    return;
                }

                form.dataset.swalConfirmed = 'true';

                if (submitter && typeof submitter.disabled !== 'undefined') {
                    submitter.disabled = true;
                }

                form.submit();
            });
        });
    });
</script>
