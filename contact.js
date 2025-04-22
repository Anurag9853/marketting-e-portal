document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('contactForm');
    const formMessage = document.getElementById('formMessage');

    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Clear previous messages
        formMessage.textContent = '';
        formMessage.className = 'mt-4 text-center';

        const formData = {
            name: document.getElementById('name').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            message: document.getElementById('message').value.trim()
        };

        // Basic validation
        if (!formData.name || !formData.phone || !formData.message) {
            formMessage.textContent = 'Please fill in all fields';
            formMessage.className = 'mt-4 text-center text-red-600';
            formMessage.classList.remove('hidden');
            return;
        }

        try {
            const response = await fetch('http://localhost/project2/backend/api/contact/submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                formMessage.textContent = 'Message sent successfully!';
                formMessage.className = 'mt-4 text-center text-green-600';
                contactForm.reset();
            } else {
                formMessage.textContent = data.message || 'Failed to send message. Please try again.';
                formMessage.className = 'mt-4 text-center text-red-600';
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            formMessage.textContent = 'An error occurred. Please try again later.';
            formMessage.className = 'mt-4 text-center text-red-600';
        }

        formMessage.classList.remove('hidden');
    });
}); 