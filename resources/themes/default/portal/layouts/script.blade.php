<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleAction = document.querySelector('.toggle-action');
        const navAction = document.querySelector('.nav-action');

        toggleAction.addEventListener('click', function() {
            navAction.classList.toggle('active');
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const openModalButtons = document.querySelectorAll('#modal-open');
    const closeModalButtons = document.querySelectorAll('#modal-close');

    openModalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.getAttribute('modal-data');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        });
    });

    closeModalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });

    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
});
</script>